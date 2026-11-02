<?php

namespace App\Services;

use App\Models\CarColor;
use App\Models\CarMake;
use App\Models\PlateType;
use App\Models\TrafficDirectorate;
use App\Models\Transaction;
use App\Models\TransactionType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccessImportService
{
    /**
     * Import sample records from the exported JSON.
     *
     * @param int $limit
     * @param bool $dryRun
     * @return array
     */
    public function importSample(int $limit = 50, bool $dryRun = false): array
    {
        $jsonPath = database_path('sample_access_50.json');
        if (!file_exists($jsonPath)) {
            throw new \Exception("فایلی داتای تاقیکاری نەدۆزرایەوە لە: {$jsonPath}");
        }

        $raw = file_get_contents($jsonPath);
        $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);
        $items = json_decode($raw, true);
        if (!is_array($items)) {
            throw new \Exception("هەڵە لە خوێندنەوەی فایلی JSON هەیە: " . json_last_error_msg());
        }

        $items = array_slice($items, 0, $limit);
        $results = [
            'total' => count($items),
            'imported' => 0,
            'skipped' => 0,
            'dry_run' => $dryRun,
            'records' => [],
        ];

        if (!$dryRun) {
            DB::beginTransaction();
        }

        try {
            foreach ($items as $item) {
                $mapped = $this->mapRecord($item);

                if ($dryRun) {
                    $results['records'][] = $mapped;
                    $results['imported']++;
                    continue;
                }

                // Check if already imported by barcode or ID_df
                $existing = Transaction::where('barcode', $mapped['barcode'])->first();
                if ($existing) {
                    $results['skipped']++;
                    continue;
                }

                $tx = Transaction::create($mapped);
                $results['records'][] = [
                    'id' => $tx->id,
                    'barcode' => $tx->barcode,
                    'visitor_name' => $tx->visitor_name,
                    'car_make_name' => $tx->carMake?->name_kurdish,
                    'booklet_number' => $tx->booklet_number,
                    'plate_number' => $tx->plate_number,
                    'chassis_number' => $tx->chassis_number,
                    'type' => $tx->transaction_type_id == 5 ? 'پووچەڵکردنەوە' : 'دەرهێنانی دەفتەر',
                    'NO_boxfile' => $tx->NO_boxfile,
                    'NO_boxfile_puchal' => $tx->NO_boxfile_puchal,
                ];
                $results['imported']++;
            }

            if (!$dryRun) {
                DB::commit();
            }
        } catch (\Throwable $e) {
            if (!$dryRun) {
                DB::rollBack();
            }
            throw $e;
        }

        return $results;
    }

    /**
     * Delete only the imported sample records.
     *
     * @return int
     */
    public function rollbackSample(): int
    {
        return Transaction::where('barcode', 'like', 'ACC-%')->forceDelete();
    }

    /**
     * Map a single Access record to Laravel Transaction attributes.
     */
    public function mapRecord(array $item): array
    {
        $typeDfId = (int)($item['type_df_id'] ?? 2);
        $isCancelled = !empty($item['puchal_df']) || $typeDfId === 1;

        // Transaction Type ID in Laravel:
        // 1: دەرهێنانی دەفتەر (New Booklet Issue)
        // 2: تازەکردنەوەی دەفتەر (Renewal)
        // 5: پووچەڵکردنەوەی دەفتەر (Cancellation)
        if ($isCancelled) {
            $transactionTypeId = 5;
        } elseif ($typeDfId === 3) {
            $transactionTypeId = 2;
        } else {
            $transactionTypeId = 1;
        }

        // Directorate
        $directorateId = $this->resolveDirectorate($item['governorate_name'] ?? null);

        // Plate Type
        $plateTypeId = $this->resolvePlateType($item['plate_type_name'] ?? null);

        // Car Make
        $carMakeId = $this->resolveCarMake($item['car_make_name'] ?? null);

        // Car Color
        $carColorId = $this->resolveCarColor($item['car_color_name'] ?? null);

        $barcode = 'ACC-' . str_pad((string)($item['ID_df'] ?? rand(1000, 9999)), 6, '0', STR_PAD_LEFT);
        $txDate = $item['Date_in_df'] ?? $item['datee'] ?? Carbon::now()->toDateString();
        $puchalDate = $isCancelled ? ($item['puchal_date_df'] ?? $txDate) : null;

        $notes = trim(($item['note_df'] ?? '') . ' [هاوردەکراو لە ئەکسس - تاقیکاری]');

        return [
            'traffic_directorate_id' => $directorateId,
            'director_id' => 1,
            'transaction_type_id' => $transactionTypeId,
            'plate_type_id' => $plateTypeId,
            'car_make_id' => $carMakeId,
            'car_color_id' => $carColorId,

            'visitor_name' => !empty(trim($item['Name_df'] ?? '')) ? trim($item['Name_df']) : 'دیاری نەکراو',
            'visitor_name_eng' => !empty(trim($item['name_df_En'] ?? '')) ? trim($item['name_df_En']) : null,
            'second_driver_name' => !empty(trim($item['seceondName_df'] ?? '')) ? trim($item['seceondName_df']) : null,
            'second_driver_name_eng' => !empty(trim($item['seceondName_df_en'] ?? '')) ? trim($item['seceondName_df_en']) : null,
            'phone_number' => !empty(trim($item['NO_Phone'] ?? '')) ? trim($item['NO_Phone']) : null,

            'plate_number' => !empty(trim($item['No_car'] ?? '')) ? trim($item['No_car']) : '00000',
            'model_year' => !empty($item['Date_car']) ? substr((string)$item['Date_car'], 0, 4) : null,
            'chassis_number' => !empty(trim($item['No_shasi'] ?? '')) ? trim($item['No_shasi']) : '-',
            'piston_count' => 4,
            'salana_number' => !empty(trim($item['No_salana'] ?? '')) ? trim($item['No_salana']) : null,
            'transaction_date' => $txDate,
            'notes' => $notes,

            'barcode' => $barcode,
            'num_years' => (int)($item['No_Year'] ?? 1) ?: 1,
            'receipt_37a_number' => !empty(trim($item['NO_37A'] ?? '')) ? trim($item['NO_37A']) : null,
            'start_date' => $item['Date_in_df'] ?? $txDate,
            'end_date' => $item['Date_out_df'] ?? null,

            'zedabar' => $item['bashy_ziyadkraw'] ?? null,
            'kamukurty' => $item['kamukury'] ?? null,
            'bary_gshty' => $item['bary_gshty'] ?? null,

            'NO_boxfile' => (!empty($item['NO_boxfile']) && (int)$item['NO_boxfile'] > 0) ? (int)$item['NO_boxfile'] : null,
            'NO_boxfile_puchal' => (!empty($item['NO_boxfile_puchal']) && (int)$item['NO_boxfile_puchal'] > 0) ? (int)$item['NO_boxfile_puchal'] : null,

            // Cancellation fields
            'no_nusraw_puchal' => $isCancelled ? ($item['NO_N_mrur'] ?? null) : null,
            'date_nusraw_puchal' => $puchalDate,
            'cancellation_code' => $isCancelled ? 'ACC-PUCHAL' : null,
            'cancellation_reason' => $isCancelled ? 'پووچەڵکراوە لە سیستەمی ئەکسس' : null,
            'cancelled_at' => $puchalDate,
            'cancelled_by' => $isCancelled ? 'سیستەمی ئەکسس' : null,

            // Workflow status - marked completed as historical records
            'user_input' => 'ئەکسس (کۆن)',
            'is_inspected' => true,
            'inspected_by' => 'ئەکسس',
            'inspected_at' => $txDate,

            'is_audited' => true,
            'audited_by' => 'ئەکسس',
            'audited_at' => $txDate,

            'is_paid' => true,
            'paid_by' => 'ئەکسس',
            'paid_at' => $txDate,

            'is_booklet_completed' => true,
            'booklet_number' => !empty($item['NO_df']) ? (string)$item['NO_df'] : null,
            'booklet_by' => 'ئەکسس',
            'booklet_completed_at' => $txDate,

            'is_pledge_completed' => true,
            'is_printed' => true,
        ];
    }

    protected function resolveDirectorate(?string $name): int
    {
        if (!$name) {
            return 1;
        }

        $gov = TrafficDirectorate::where('name_kurdish', 'like', '%' . trim($name) . '%')->first();
        return $gov ? $gov->id : 1;
    }

    protected function resolvePlateType(?string $name): int
    {
        if (!$name) {
            return 1;
        }

        $clean = trim($name);
        $pt = PlateType::where('name_kurdish', 'like', '%' . $clean . '%')->first();
        return $pt ? $pt->id : 1;
    }

    protected function resolveCarMake(?string $rawName): int
    {
        if (!$rawName) {
            return 1;
        }

        $clean = trim($rawName);
        if (empty($clean)) {
            return 1;
        }

        // تۆمارکردن یان دۆزینەوەی ناوی تەواوی جۆری ئۆتۆمبێل وەک خۆی لە ئەکسسەوە (ئینگلیزی دواتر پڕدەکرێتەوە)
        $carMake = CarMake::firstOrCreate(
            ['name_kurdish' => $clean],
            ['name_english' => null]
        );

        return $carMake->id;
    }

    protected function resolveCarColor(?string $rawName): int
    {
        if (!$rawName) {
            return 1;
        }

        $clean = trim($rawName);
        $cc = CarColor::where('name_kurdish', 'like', '%' . $clean . '%')->first();
        if ($cc) {
            return $cc->id;
        }

        $colorMap = [
            'سپی' => 'سپی',
            'ابیض' => 'سپی',
            'ڕەش' => 'ڕەش',
            'اسود' => 'ڕەش',
            'نیلی' => 'نیلی / شین',
            'شین' => 'نیلی / شین',
            'ازرق' => 'نیلی / شین',
            'زەرد' => 'زێڕی / زەرد',
            'اصفر' => 'زێڕی / زەرد',
            'ڕەساسی' => 'ڕەساسی / زیوی',
            'سلڤه‌ر' => 'ڕەساسی / زیوی',
            'فضی' => 'ڕەساسی / زیوی',
            'رصاصی' => 'ڕەساسی / زیوی',
            'سوور' => 'سوور',
            'احمر' => 'سوور',
            'سەوز' => 'سەوز',
            'اخضر' => 'سەوز',
        ];

        foreach ($colorMap as $k => $v) {
            if (mb_stripos($clean, $k) !== false) {
                $matched = CarColor::where('name_kurdish', 'like', '%' . $v . '%')->first();
                if ($matched) {
                    return $matched->id;
                }
            }
        }

        return 1;
    }
}

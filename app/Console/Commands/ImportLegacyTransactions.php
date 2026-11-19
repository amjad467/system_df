<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Models\CarMake;
use App\Models\CarColor;
use App\Models\PlateType;
use App\Models\TrafficDirectorate;
use App\Models\Director;
use PDO;

class ImportLegacyTransactions extends Command
{
    protected $signature = 'legacy:import {--clear : Clear only the transactions table before importing} {--limit= : Limit the number of records}';
    protected $description = 'Import all 17,424 historical records from the legacy SQLite system into tourist-car-system';

    public function handle()
    {
        $dbPath = 'C:/Users/PC/.gemini/antigravity/scratch/otombil_system/database.sqlite';
        if (!file_exists($dbPath)) {
            $this->error("فایلی داتابەیسی کۆن نەدۆزرایەوە لە: {$dbPath}");
            return 1;
        }

        $this->info("دەستپێکردنی پڕۆسەی هاوردەکردنی داتاکانی سیستەمی پێشوو...");

        // 1. Clear transactions table if requested
        if ($this->option('clear')) {
            $this->warn("سڕینەوەی مامەڵەکانی پێشوو لە خشتەی transactions...");
            DB::statement('DELETE FROM transactions');
            // Reset autoincrement sequence if sqlite_sequence exists
            try {
                DB::statement("DELETE FROM sqlite_sequence WHERE name = 'transactions'");
            } catch (\Throwable $e) {}
            $this->info("خشتەی transactions بە سەرکەوتوویی پاککرایەوە.");
        }

        // 2. Connect to legacy SQLite database
        $legacyPdo = new PDO("sqlite:" . $dbPath);
        $legacyPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 3. Preload lookups in memory
        $this->info("ئامادەکردنی زانیارییە بنەڕەتییەکان لە میمۆری...");

        $activeDirector = Director::where('is_active', true)->first() ?? Director::first();
        $defaultDirectorId = $activeDirector ? $activeDirector->id : 1;

        // Directorates map
        $directoratesMap = [];
        foreach (TrafficDirectorate::all() as $d) {
            $directoratesMap[trim($d->name_kurdish)] = $d->id;
        }

        // Plate Types map
        $plateTypesMap = [];
        foreach (PlateType::all() as $pt) {
            $plateTypesMap[trim($pt->name_kurdish)] = $pt->id;
        }

        // Car Makes cache
        $carMakesCache = [];
        foreach (CarMake::all() as $cm) {
            $carMakesCache[trim($cm->name_kurdish)] = $cm->id;
        }

        // Car Colors cache
        $carColorsCache = [];
        foreach (CarColor::all() as $cc) {
            $carColorsCache[trim($cc->name_kurdish)] = $cc->id;
        }

        // Resolve helper functions
        $resolveDirectorate = function($rawName) use (&$directoratesMap) {
            if (!$rawName) return 1;
            $name = trim($rawName);
            if (mb_stripos($name, 'سلێمان') !== false) return 1;
            if (mb_stripos($name, 'هەولێر') !== false || mb_stripos($name, 'ههولێر') !== false) return 2;
            if (mb_stripos($name, 'دهۆک') !== false || mb_stripos($name, 'دهوك') !== false) return 3;
            if (mb_stripos($name, 'هەڵەبجە') !== false || mb_stripos($name, 'ههڵهبجه') !== false) return 4;

            // Check if exists or create
            foreach ($directoratesMap as $k => $id) {
                if (mb_stripos($k, $name) !== false) return $id;
            }
            $newDir = TrafficDirectorate::firstOrCreate(
                ['name_kurdish' => $name],
                ['name_english' => $name]
            );
            $directoratesMap[$name] = $newDir->id;
            return $newDir->id;
        };

        $resolvePlateType = function($tabloId, $rawName = null) use (&$plateTypesMap) {
            if ($tabloId == 1) return 1; // تایبەت
            if ($tabloId == 2) return 2; // بار
            if ($tabloId == 3) return 3; // کرێ
            if ($tabloId == 4) return 1; // پیشەسازی -> تایبەت
            return 1;
        };

        $resolveCarMake = function($rawMake) use (&$carMakesCache) {
            if (!$rawMake) return 1;
            $clean = trim($rawMake);
            if (empty($clean)) return 1;
            if (isset($carMakesCache[$clean])) {
                return $carMakesCache[$clean];
            }
            $make = CarMake::firstOrCreate(['name_kurdish' => $clean]);
            $carMakesCache[$clean] = $make->id;
            return $make->id;
        };

        $resolveCarColor = function($rawColor) use (&$carColorsCache) {
            if (!$rawColor) return 1;
            $clean = trim($rawColor);
            if (empty($clean)) return 1;
            if (isset($carColorsCache[$clean])) {
                return $carColorsCache[$clean];
            }
            $color = CarColor::firstOrCreate(['name_kurdish' => $clean]);
            $carColorsCache[$clean] = $color->id;
            return $color->id;
        };

        // 4. Query all records from legacy database
        $limitClause = '';
        if ($limit = $this->option('limit')) {
            $limitClause = "LIMIT " . (int)$limit;
        }

        $sql = "
            SELECT 
                d.ID_df,
                d.NO_df,
                d.Name_df,
                d.name_df_En,
                d.seceondName_df,
                d.seceondName_df_en,
                d.NO_Phone,
                d.type_df_id,
                d.Date_in_df,
                d.No_Year,
                d.Date_out_df,
                d.puchal_df,
                d.puchal_date_df,
                d.NO_boxfile,
                d.NO_boxfile_puchal,
                d.note_df,
                d.ID_car_dif,
                d.ID_nusraw,
                c.ID_car,
                c.No_car,
                c.No_salana,
                c.parezga_id,
                c.type_tablo_id,
                c.No_shasi,
                c.No_bzwenar,
                c.Date_car,
                c.bashy_ziyadkraw,
                c.kamukury,
                c.bary_gshty,
                tc.type_car,
                tcol.type_color,
                pt.Name_parezga,
                n.NO_37A,
                n.NO_N_gumrg,
                n.date_N_gumrg,
                n.NO_N_mrur,
                n.date_in_nusraw,
                n.NO_N_balennama
            FROM tbl_df_in d
            LEFT JOIN tbl_car_diffine c ON d.ID_car_dif = c.ID_car
            LEFT JOIN tbl_nusraw n ON d.ID_nusraw = n.ID
            LEFT JOIN tbl_type_car tc ON c.type_car_id = tc.ID_type
            LEFT JOIN tbl_type_color tcol ON c.type_color_id = tcol.ID_color
            LEFT JOIN tbl_parezga_tablo pt ON c.parezga_id = pt.ID_tablo
            ORDER BY d.ID_df ASC
            {$limitClause}
        ";

        $this->info("خوێندنەوەی تۆمارەکان لە بنکەدراوەی کۆن...");
        $stmt = $legacyPdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $totalCount = count($rows);
        $this->info("کۆی گشتی دۆزراوە لە ئەکسس/سکوڵایت: " . number_format($totalCount) . " تۆمار");

        $bar = $this->output->createProgressBar($totalCount);
        $bar->start();

        $batch = [];
        $batchSize = 500;
        $now = now()->toDateTimeString();

        foreach ($rows as $r) {
            $idDf = (int)$r['ID_df'];
            $barcode = 'ACC-' . str_pad((string)$idDf, 6, '0', STR_PAD_LEFT);

            // Classification
            $isCancelled = (!empty($r['puchal_df']) && (int)$r['puchal_df'] === 1) || ((int)($r['type_df_id'] ?? 0) === 1);
            $note = trim($r['note_df'] ?? '');
            $isRenewal = (mb_stripos($note, 'تازە') !== false || mb_stripos($note, 'تازه') !== false || mb_stripos($note, 'تجديد') !== false || (int)($r['type_df_id'] ?? 0) === 3);

            if ($isCancelled) {
                $transactionTypeId = 5; // پووچەڵکردنەوە
            } elseif ($isRenewal) {
                $transactionTypeId = 2; // تازەکردنەوە
            } else {
                $transactionTypeId = 1; // دەرهێنانی دەفتەر
            }

            // Chassis
            $rawChassis = trim($r['No_shasi'] ?? '');
            if (empty($rawChassis)) {
                // Check if 17-char chassis in note
                if (preg_match('/[A-HJ-NPR-Z0-9]{17}/i', $note, $matches)) {
                    $chassisNumber = strtoupper($matches[0]);
                } else {
                    $chassisNumber = 'CHASSIS-LEG-' . ($r['ID_car'] ?? $idDf);
                }
            } else {
                $chassisNumber = strtoupper(preg_replace('/[^A-Za-z0-9\-]/', '', $rawChassis));
                if (empty($chassisNumber)) {
                    $chassisNumber = 'CHASSIS-LEG-' . ($r['ID_car'] ?? $idDf);
                }
            }

            // Dates
            $rawTxDate = !empty($r['Date_in_df']) ? trim($r['Date_in_df']) : date('Y-m-d');
            $txDate = substr($rawTxDate, 0, 10);
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $txDate) || str_starts_with($txDate, '00') || str_starts_with($txDate, '02')) {
                $txDate = date('Y-m-d');
            }

            $endDate = null;
            if (!empty($r['Date_out_df'])) {
                $ed = substr(trim($r['Date_out_df']), 0, 10);
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $ed) && !str_starts_with($ed, '00') && !str_starts_with($ed, '02')) {
                    $endDate = $ed;
                }
            }

            $puchalDate = null;
            if ($isCancelled) {
                if (!empty($r['puchal_date_df'])) {
                    $pd = substr(trim($r['puchal_date_df']), 0, 10);
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $pd) && !str_starts_with($pd, '00')) {
                        $puchalDate = $pd;
                    }
                }
                if (!$puchalDate) {
                    $puchalDate = $txDate;
                }
            }

            // Directorate & Plate
            $parezgaName = $r['parezga_id'] ?? $r['Name_parezga'] ?? null;
            $directorateId = $resolveDirectorate($parezgaName);
            $plateTypeId = $resolvePlateType($r['type_tablo_id'] ?? 1);
            $carMakeId = $resolveCarMake($r['type_car'] ?? null);
            $carColorId = $resolveCarColor($r['type_color'] ?? null);

            // Official Letters
            $noNMrur = trim((string)($r['NO_N_mrur'] ?? ''));
            $dateNMrur = !empty($r['date_in_nusraw']) ? substr(trim($r['date_in_nusraw']), 0, 10) : null;
            if ($dateNMrur && (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateNMrur) || str_starts_with($dateNMrur, '00'))) {
                $dateNMrur = null;
            }

            $noNGumrg = trim((string)($r['NO_N_gumrg'] ?? ''));
            $dateNGumrg = !empty($r['date_N_gumrg']) ? substr(trim($r['date_N_gumrg']), 0, 10) : null;
            if ($dateNGumrg && (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateNGumrg) || str_starts_with($dateNGumrg, '00'))) {
                $dateNGumrg = null;
            }

            $noNBalennama = trim((string)($r['NO_N_balennama'] ?? ''));

            // Notes
            $notesFinal = trim($note . ' [هاوردەکراو لە سیستەمی پێشوو]');

            // Model Year
            $modelYear = null;
            if (!empty($r['Date_car'])) {
                $dy = substr(trim($r['Date_car']), 0, 4);
                if (is_numeric($dy) && (int)$dy >= 1950 && (int)$dy <= 2035) {
                    $modelYear = $dy;
                }
            }

            $batch[] = [
                'traffic_directorate_id' => $directorateId,
                'director_id' => $defaultDirectorId,
                'transaction_type_id' => $transactionTypeId,
                'plate_type_id' => $plateTypeId,
                'car_make_id' => $carMakeId,
                'car_color_id' => $carColorId,

                'visitor_name' => !empty(trim($r['Name_df'] ?? '')) ? trim($r['Name_df']) : 'دیاری نەکراو',
                'visitor_name_eng' => !empty(trim($r['name_df_En'] ?? '')) ? trim($r['name_df_En']) : null,
                'second_driver_name' => !empty(trim($r['seceondName_df'] ?? '')) ? trim($r['seceondName_df']) : null,
                'second_driver_name_eng' => !empty(trim($r['seceondName_df_en'] ?? '')) ? trim($r['seceondName_df_en']) : null,
                'phone_number' => !empty(trim($r['NO_Phone'] ?? '')) ? trim($r['NO_Phone']) : null,

                'plate_number' => !empty(trim($r['No_car'] ?? '')) ? trim($r['No_car']) : '00000',
                'model_year' => $modelYear,
                'chassis_number' => $chassisNumber,
                'piston_count' => (int)($r['No_bzwenar'] ?? 4) ?: 4,
                'salana_number' => !empty(trim($r['No_salana'] ?? '')) ? trim($r['No_salana']) : null,
                'transaction_date' => $txDate,
                'notes' => $notesFinal,

                'barcode' => $barcode,
                'num_years' => (int)($r['No_Year'] ?? 1) ?: 1,
                'receipt_37a_number' => (!empty(trim($r['NO_37A'] ?? '')) && trim($r['NO_37A']) !== '0') ? trim($r['NO_37A']) : null,
                'start_date' => $txDate,
                'end_date' => $endDate,

                'zedabar' => $r['bashy_ziyadkraw'] ?? null,
                'kamukurty' => $r['kamukury'] ?? null,
                'bary_gshty' => $r['bary_gshty'] ?? null,

                'NO_boxfile' => (!empty($r['NO_boxfile']) && (int)$r['NO_boxfile'] > 0) ? (int)$r['NO_boxfile'] : null,
                'NO_boxfile_puchal' => (!empty($r['NO_boxfile_puchal']) && (int)$r['NO_boxfile_puchal'] > 0) ? (int)$r['NO_boxfile_puchal'] : null,

                // Official Letters
                'no_nusraw_gumrg' => (!empty($noNGumrg) && $noNGumrg !== '0') ? $noNGumrg : null,
                'date_nusraw_gumrg' => $dateNGumrg,
                'no_nusraw_mrur' => $noNMrur !== '' ? $noNMrur : '0',
                'date_nusraw_mrur' => $dateNMrur,
                'no_nusraw_balennama' => (!empty($noNBalennama) && $noNBalennama !== '0') ? $noNBalennama : null,

                // Cancellation Fields
                'is_cancelled' => $isCancelled ? 1 : 0,
                'no_nusraw_puchal' => $isCancelled ? ($noNMrur ?: 'ACC-PUCHAL') : null,
                'date_nusraw_puchal' => $isCancelled ? $puchalDate : null,
                'cancellation_code' => $isCancelled ? 'ACC-PUCHAL' : null,
                'cancellation_reason' => $isCancelled ? 'پووچەڵکراوە لە سیستەمی پێشوو' : null,
                'cancelled_at' => $isCancelled ? $puchalDate : null,
                'cancelled_by' => $isCancelled ? 'سیستەمی پێشوو' : null,

                // Completed Historical Workflow
                'user_input' => 'سیستەمی پێشوو',
                'is_inspected' => 1,
                'inspected_by' => 'سیستەمی پێشوو',
                'inspected_at' => $txDate,

                'is_audited' => 1,
                'audited_by' => 'سیستەمی پێشوو',
                'audited_at' => $txDate,

                'is_paid' => 1,
                'paid_by' => 'سیستەمی پێشوو',
                'paid_at' => $txDate,

                'is_booklet_completed' => 1,
                'booklet_number' => !empty($r['NO_df']) ? (string)$r['NO_df'] : null,
                'booklet_by' => 'سیستەمی پێشوو',
                'booklet_completed_at' => $txDate,

                'is_pledge_completed' => 1,
                'is_printed' => 1,
                'created_at' => $rawTxDate,
                'updated_at' => $now,
            ];

            if (count($batch) >= $batchSize) {
                DB::table('transactions')->insert($batch);
                $batch = [];
                $bar->advance($batchSize);
            }
        }

        if (count($batch) > 0) {
            DB::table('transactions')->insert($batch);
            $bar->advance(count($batch));
        }

        $bar->finish();
        $this->newLine();
        $this->info("هاوردەکردنی " . number_format($totalCount) . " تۆمار بە سەرکەوتوویی تەواوبوو!");

        return 0;
    }
}

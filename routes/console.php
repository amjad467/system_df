<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('access:import-sample {--limit=50 : ژمارەی داتاکان} {--dry-run : تەنها پشکنین بێ پاشەکەوتکردن}', function (\App\Services\AccessImportService $importer) {
    $limit = (int)$this->option('limit');
    $isDryRun = (bool)$this->option('dry-run');

    $this->info($isDryRun ? "=== تاقیکردنەوەی خەمڵاندن (DRY RUN) بۆ {$limit} تۆمار ===" : "=== دەستپێکردنی هاوردەکردنی {$limit} تۆمار ===");

    try {
        $result = $importer->importSample($limit, $isDryRun);
        $sampleDisplay = array_merge(
            array_slice($result['records'], 0, 5),
            array_slice($result['records'], 25, 5)
        );

        $this->table(
            ['Barcode', 'Visitor Name', 'Car Make', 'Plate No', 'Chassis (VIN)', 'Type', 'Box', 'Cancel Box'],
            array_map(function ($r) {
                return [
                    $r['barcode'] ?? '-',
                    $r['visitor_name'] ?? '-',
                    $r['car_make_name'] ?? ($r['car_make_id'] ?? '-'),
                    $r['plate_number'] ?? '-',
                    $r['chassis_number'] ?? '-',
                    ($r['transaction_type_id'] ?? null) == 5 ? 'پووچەڵ' : ($r['type'] ?? 'دەرهێنان'),
                    $r['NO_boxfile'] ?? '-',
                    $r['NO_boxfile_puchal'] ?? '-',
                ];
            }, $sampleDisplay)
        );

        $this->info("کۆی گشتی: {$result['total']} | هاوردەکراو: {$result['imported']} | تێپەڕێنراو: {$result['skipped']}");
        if ($isDryRun) {
            $this->comment("تێبینی: هیچ داتایەک نەنووسراوە، چونکە باری Dry-Run چالاک بوو.");
        } else {
            $this->info("سەرکەوتوو بوو! داتاکان لە ناو داتابەیسی لاراڤێڵ جێگیر کران.");
        }
    } catch (\Throwable $e) {
        $this->error("هەڵە ڕوویدا: " . $e->getMessage());
    }
})->purpose('Import sample records from MS Access export');

Artisan::command('access:rollback-sample', function (\App\Services\AccessImportService $importer) {
    $this->warn("سڕینەوەی هەموو داتاکانی هاوردەکراوی تاقیکاری لە ئەکسس...");
    $count = $importer->rollbackSample();
    $this->info("سەرکەوتوو بوو! {$count} تۆماری تاقیکاری سڕانەوە و داتابەیس پاککرایەوە.");
})->purpose('Delete sample records imported from Access');

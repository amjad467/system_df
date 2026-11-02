<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DataRestoreService
{
    /**
     * Restore all baseline transactions, car makes, barcode sequences and configurations.
     */
    public static function restoreAll(): array
    {
        $stats = [
            'car_makes' => 0,
            'transactions' => 0,
            'barcode_sequences' => 0,
            'blocked_receipt_numbers' => 0,
        ];

        try {
            // 1. Car Makes (All 42 makes)
            $makesFile = database_path('seeders/car_makes.json');
            if (file_exists($makesFile)) {
                $makes = json_decode(file_get_contents($makesFile), true);
                if (is_array($makes)) {
                    foreach ($makes as $m) {
                        DB::table('car_makes')->updateOrInsert(['id' => $m['id']], (array)$m);
                        $stats['car_makes']++;
                    }
                }
            }

            // 2. Barcode Sequences
            $seqFile = database_path('seeders/barcode_sequences.json');
            if (file_exists($seqFile)) {
                $seqs = json_decode(file_get_contents($seqFile), true);
                if (is_array($seqs)) {
                    foreach ($seqs as $s) {
                        DB::table('barcode_sequences')->updateOrInsert(['id' => $s['id']], (array)$s);
                        $stats['barcode_sequences']++;
                    }
                }
            }

            // 3. Blocked Receipts
            $blockedFile = database_path('seeders/blocked_receipt_numbers.json');
            if (file_exists($blockedFile)) {
                $blocked = json_decode(file_get_contents($blockedFile), true);
                if (is_array($blocked)) {
                    foreach ($blocked as $b) {
                        DB::table('blocked_receipt_numbers')->updateOrInsert(['id' => $b['id']], (array)$b);
                        $stats['blocked_receipt_numbers']++;
                    }
                }
            }

            // 4. Transactions (All 84 records)
            $txFile = database_path('seeders/transactions_backup.json');
            if (file_exists($txFile)) {
                $txList = json_decode(file_get_contents($txFile), true);
                if (is_array($txList)) {
                    foreach ($txList as $tx) {
                        $txArr = (array)$tx;
                        // Avoid issues with null or auto-increment id collision across different DBs
                        $barcode = $txArr['barcode'] ?? null;
                        if ($barcode) {
                            DB::table('transactions')->updateOrInsert(['barcode' => $barcode], $txArr);
                            $stats['transactions']++;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('DataRestoreService restore failed: ' . $e->getMessage());
        }

        return $stats;
    }
}

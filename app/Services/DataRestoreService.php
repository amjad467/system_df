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
            'errors' => [],
        ];

        // 1. Temporarily disable foreign key checks to prevent ordering deadlocks
        $driver = DB::connection()->getDriverName();
        try {
            if ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = OFF;');
            } elseif ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
            }
        } catch (\Throwable $e) {
            Log::warning('Could not disable FK checks: ' . $e->getMessage());
        }

        try {
            // Step 1: Car Makes (All 42 makes)
            $makesFile = database_path('seeders/car_makes.json');
            if (file_exists($makesFile)) {
                $makes = json_decode(file_get_contents($makesFile), true);
                if (is_array($makes)) {
                    foreach ($makes as $m) {
                        try {
                            $mArr = (array)$m;
                            DB::table('car_makes')->updateOrInsert(['id' => $mArr['id']], $mArr);
                            $stats['car_makes']++;
                        } catch (\Throwable $e) {
                            $stats['errors'][] = "CarMake {$m['id']}: " . $e->getMessage();
                        }
                    }
                }
            }

            // Step 2: Barcode Sequences
            $seqFile = database_path('seeders/barcode_sequences.json');
            if (file_exists($seqFile)) {
                $seqs = json_decode(file_get_contents($seqFile), true);
                if (is_array($seqs)) {
                    foreach ($seqs as $s) {
                        try {
                            $sArr = (array)$s;
                            DB::table('barcode_sequences')->updateOrInsert(['id' => $sArr['id']], $sArr);
                            $stats['barcode_sequences']++;
                        } catch (\Throwable $e) {
                            $stats['errors'][] = "BarcodeSeq {$s['id']}: " . $e->getMessage();
                        }
                    }
                }
            }

            // Step 3: Transactions (All 84 records)
            $txFile = database_path('seeders/transactions_backup.json');
            if (file_exists($txFile)) {
                $txList = json_decode(file_get_contents($txFile), true);
                if (is_array($txList)) {
                    $booleanCols = [
                        'is_inspected', 'is_audited', 'is_paid', 'is_booklet_completed',
                        'is_pledge_completed', 'is_printed', 'is_submitted', 'is_returned',
                    ];
                    $dateCols = [
                        'transaction_date', 'start_date', 'end_date', 'date_nusraw_puchal',
                        'user_edit_date', 'inspected_at', 'audited_at', 'paid_at',
                        'booklet_completed_at', 'cancelled_at', 'submitted_at', 'deleted_at',
                    ];

                    foreach ($txList as $tx) {
                        $txArr = (array)$tx;
                        $barcode = $txArr['barcode'] ?? null;
                        if (!$barcode) {
                            continue;
                        }

                        // Sanitize booleans for compatibility with SQLite & PostgreSQL
                        foreach ($booleanCols as $bCol) {
                            if (array_key_exists($bCol, $txArr)) {
                                $txArr[$bCol] = (bool)$txArr[$bCol];
                            }
                        }

                        // Sanitize empty string dates to null
                        foreach ($dateCols as $dCol) {
                            if (array_key_exists($dCol, $txArr) && trim((string)$txArr[$dCol]) === '') {
                                $txArr[$dCol] = null;
                            }
                        }

                        try {
                            // Check if barcode already exists
                            $existing = DB::table('transactions')->where('barcode', $barcode)->first();
                            if ($existing) {
                                // Update existing record without changing primary key
                                unset($txArr['id']);
                                DB::table('transactions')->where('barcode', $barcode)->update($txArr);
                            } else {
                                // Try inserting with original ID, fallback without ID if collision
                                try {
                                    DB::table('transactions')->insert($txArr);
                                } catch (\Throwable $idErr) {
                                    unset($txArr['id']);
                                    DB::table('transactions')->insert($txArr);
                                }
                            }
                            $stats['transactions']++;
                        } catch (\Throwable $e) {
                            $stats['errors'][] = "Tx {$barcode}: " . $e->getMessage();
                            Log::error("Failed to restore transaction {$barcode}: " . $e->getMessage());
                        }
                    }

                    // Fix linked dual pledge parent transaction if needed
                    try {
                        $parentTx = DB::table('transactions')->where('barcode', '2611010001')->first();
                        if ($parentTx) {
                            DB::table('transactions')
                                ->where('barcode', '2611010002')
                                ->update(['parent_transaction_id' => $parentTx->id]);
                        }
                    } catch (\Throwable $pErr) {
                        // Non-critical
                    }
                }
            }

            // Step 4: Blocked Receipts (Runs AFTER transactions exist, so FK references are valid)
            $blockedFile = database_path('seeders/blocked_receipt_numbers.json');
            if (file_exists($blockedFile)) {
                $blocked = json_decode(file_get_contents($blockedFile), true);
                if (is_array($blocked)) {
                    foreach ($blocked as $b) {
                        try {
                            $bArr = (array)$b;
                            // Verify transaction exists before referencing
                            if (!empty($bArr['transaction_id'])) {
                                $txExists = DB::table('transactions')->where('id', $bArr['transaction_id'])->exists();
                                if (!$txExists) {
                                    $bArr['transaction_id'] = null;
                                }
                            }
                            DB::table('blocked_receipt_numbers')->updateOrInsert(['receipt_number' => $bArr['receipt_number']], $bArr);
                            $stats['blocked_receipt_numbers']++;
                        } catch (\Throwable $e) {
                            try {
                                $bArr = (array)$b;
                                $bArr['transaction_id'] = null;
                                DB::table('blocked_receipt_numbers')->updateOrInsert(['receipt_number' => $bArr['receipt_number']], $bArr);
                                $stats['blocked_receipt_numbers']++;
                            } catch (\Throwable $e2) {
                                $stats['errors'][] = "BlockedReceipt {$b['receipt_number']}: " . $e2->getMessage();
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('DataRestoreService restore failed: ' . $e->getMessage());
            $stats['errors'][] = "General: " . $e->getMessage();
        } finally {
            // Re-enable foreign key checks
            try {
                if ($driver === 'sqlite') {
                    DB::statement('PRAGMA foreign_keys = ON;');
                } elseif ($driver === 'mysql') {
                    DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
                }
            } catch (\Throwable $e) {
                // Ignore
            }
        }

        return $stats;
    }
}

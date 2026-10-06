<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class BarcodeService
{
    /**
     * Generates a concurrency-safe, transaction-atomic unique barcode.
     * Format: YYMMDDXXXX (e.g., 2610040001)
     */
    public static function generateNextBarcode(): string
    {
        $dateCode = now()->format('ymd'); // 2 digits year, 2 digits month, 2 digits day

        return DB::transaction(function () use ($dateCode) {
            // Lock sequence record for update to prevent race condition across multiple workers
            $seqRecord = DB::table('barcode_sequences')
                ->where('date_code', $dateCode)
                ->lockForUpdate()
                ->first();

            $maxExistingBarcode = DB::table('transactions')
                ->where('barcode', 'like', $dateCode . '%')
                ->max('barcode');

            $maxSeqInTx = 0;
            if ($maxExistingBarcode && strlen($maxExistingBarcode) >= 10) {
                $maxSeqInTx = (int)substr($maxExistingBarcode, 6);
            }

            $currentLastSeq = $seqRecord ? (int)$seqRecord->last_sequence : 0;
            $startSeq = max($currentLastSeq, $maxSeqInTx);

            $sequence = $startSeq + 1;

            // Loop to guarantee candidate barcode never collides with any existing record
            while (true) {
                $candidate = $dateCode . str_pad($sequence, 4, '0', STR_PAD_LEFT);
                $exists = DB::table('transactions')->where('barcode', $candidate)->exists();
                if (!$exists) {
                    break;
                }
                $sequence++;
            }

            if (!$seqRecord) {
                DB::table('barcode_sequences')->insert([
                    'date_code' => $dateCode,
                    'last_sequence' => $sequence,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('barcode_sequences')
                    ->where('date_code', $dateCode)
                    ->update([
                        'last_sequence' => $sequence,
                        'updated_at' => now(),
                    ]);
            }

            return $dateCode . str_pad($sequence, 4, '0', STR_PAD_LEFT);
        });
    }
}

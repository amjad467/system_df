<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('transactions')) {
            return;
        }

        Schema::table('transactions', function (Blueprint $table) {
            // Stage flags and date filters (heavily queried by dashboard and pipeline)
            $this->safeAddIndex($table, 'transaction_date', 'idx_tx_date');
            $this->safeAddIndex($table, 'created_at', 'idx_tx_created_at');
            $this->safeAddIndex($table, 'is_inspected', 'idx_tx_inspected');
            $this->safeAddIndex($table, 'is_audited', 'idx_tx_audited');
            $this->safeAddIndex($table, 'is_paid', 'idx_tx_paid');
            $this->safeAddIndex($table, 'is_booklet_completed', 'idx_tx_booklet');
            $this->safeAddIndex($table, 'is_submitted', 'idx_tx_submitted');
            $this->safeAddIndex($table, 'is_returned', 'idx_tx_returned');
            $this->safeAddIndex($table, 'cancelled_at', 'idx_tx_cancelled');

            // Quick search & barcode match fields
            $this->safeAddIndex($table, 'plate_number', 'idx_tx_plate');
            $this->safeAddIndex($table, 'chassis_number', 'idx_tx_chassis');
            $this->safeAddIndex($table, 'receipt_37a_number', 'idx_tx_receipt');
            $this->safeAddIndex($table, 'booklet_number', 'idx_tx_booklet_no');
            $this->safeAddIndex($table, 'phone_number', 'idx_tx_phone');
            $this->safeAddIndex($table, 'user_input', 'idx_tx_user_input');

            if (Schema::hasColumn('transactions', 'created_by')) {
                $this->safeAddIndex($table, 'created_by', 'idx_tx_created_by');
            }
            if (Schema::hasColumn('transactions', 'parent_transaction_id')) {
                $this->safeAddIndex($table, 'parent_transaction_id', 'idx_tx_parent_id');
            }
        });
    }

    /**
     * Safely add an index if column exists and index does not exist yet.
     */
    protected function safeAddIndex(Blueprint $table, string $column, string $indexName): void
    {
        try {
            if (Schema::hasColumn('transactions', $column)) {
                $table->index($column, $indexName);
            }
        } catch (\Throwable $e) {
            // Ignore if index already exists
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('transactions')) {
            return;
        }

        Schema::table('transactions', function (Blueprint $table) {
            $indexes = [
                'idx_tx_date', 'idx_tx_created_at', 'idx_tx_inspected', 'idx_tx_audited',
                'idx_tx_paid', 'idx_tx_booklet', 'idx_tx_submitted', 'idx_tx_returned',
                'idx_tx_cancelled', 'idx_tx_plate', 'idx_tx_chassis', 'idx_tx_receipt',
                'idx_tx_booklet_no', 'idx_tx_phone', 'idx_tx_user_input', 'idx_tx_created_by', 'idx_tx_parent_id'
            ];

            foreach ($indexes as $index) {
                try {
                    $table->dropIndex($index);
                } catch (\Throwable $e) {
                    // Ignore if missing
                }
            }
        });
    }
};


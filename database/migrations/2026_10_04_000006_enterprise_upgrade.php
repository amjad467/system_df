<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Transactions Table Extensions (SoftDeletes, Parent Relation)
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'parent_transaction_id')) {
                $table->foreignId('parent_transaction_id')->nullable()->after('id')->constrained('transactions')->nullOnDelete();
            }
            if (!Schema::hasColumn('transactions', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // 2. Notifications Table
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
                $table->string('target_role')->nullable(); // e.g. 'auditor', 'data_entry'
                $table->string('title');
                $table->text('message');
                $table->string('link')->nullable();
                $table->boolean('is_read')->default(false);
                $table->timestamps();
            });
        }

        // 3. Barcode Sequences Table (for atomic lock concurrency safety)
        if (!Schema::hasTable('barcode_sequences')) {
            Schema::create('barcode_sequences', function (Blueprint $table) {
                $table->id();
                $table->string('date_code')->unique(); // e.g. '261004'
                $table->integer('last_sequence')->default(0);
                $table->timestamps();
            });
        }

        // 4. Permissions & Role Permissions Table
        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('label_kurdish');
                $table->string('category')->default('general');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('role_permissions')) {
            Schema::create('role_permissions', function (Blueprint $table) {
                $table->id();
                $table->string('role');
                $table->string('permission_name');
                $table->timestamps();

                $table->unique(['role', 'permission_name']);
            });
        }

        // 5. Activity Logs Extensions
        Schema::table('activity_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('activity_logs', 'old_values')) {
                $table->json('old_values')->nullable()->after('details');
            }
            if (!Schema::hasColumn('activity_logs', 'new_values')) {
                $table->json('new_values')->nullable()->after('old_values');
            }
            if (!Schema::hasColumn('activity_logs', 'user_agent')) {
                $table->string('user_agent')->nullable()->after('ip_address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['parent_transaction_id']);
            $table->dropColumn(['parent_transaction_id']);
            $table->dropSoftDeletes();
        });

        Schema::dropIfExists('notifications');
        Schema::dropIfExists('barcode_sequences');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropColumn(['old_values', 'new_values', 'user_agent']);
        });
    }
};

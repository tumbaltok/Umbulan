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
        Schema::table('pengajuan_cars', function (Blueprint $table) {
            $table->index(['user_id', 'status_akhir'], 'idx_pengajuan_cars_user_status');
            $table->index(['status_tahap_1', 'status_tahap_2', 'status_akhir'], 'idx_pengajuan_cars_tahap_status');
            $table->index(['approver_tahap_1_id', 'status_tahap_1'], 'idx_pengajuan_cars_approver1');
            $table->index(['approver_tahap_2_id', 'status_tahap_2'], 'idx_pengajuan_cars_approver2');
        });

        Schema::table('pengajuan_mprs', function (Blueprint $table) {
            $table->index(['user_id', 'status_akhir'], 'idx_pengajuan_mprs_user_status');
            $table->index(['status_tahap_1', 'status_tahap_2', 'status_akhir'], 'idx_pengajuan_mprs_tahap_status');
            $table->index(['approver_tahap_1_id', 'status_tahap_1'], 'idx_pengajuan_mprs_approver1');
            $table->index(['approver_tahap_2_id', 'status_tahap_2'], 'idx_pengajuan_mprs_approver2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuan_cars', function (Blueprint $table) {
            $table->dropIndex('idx_pengajuan_cars_user_status');
            $table->dropIndex('idx_pengajuan_cars_tahap_status');
            $table->dropIndex('idx_pengajuan_cars_approver1');
            $table->dropIndex('idx_pengajuan_cars_approver2');
        });

        Schema::table('pengajuan_mprs', function (Blueprint $table) {
            $table->dropIndex('idx_pengajuan_mprs_user_status');
            $table->dropIndex('idx_pengajuan_mprs_tahap_status');
            $table->dropIndex('idx_pengajuan_mprs_approver1');
            $table->dropIndex('idx_pengajuan_mprs_approver2');
        });
    }
};

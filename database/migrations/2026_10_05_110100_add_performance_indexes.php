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
        Schema::table('pengajuan_cutis', function (Blueprint $table) {
            $table->index(['user_id', 'status_akhir', 'tanggal_mulai'], 'idx_pengajuan_cutis_user_status_tgl');
            $table->index(['status_tahap_1', 'status_akhir'], 'idx_pengajuan_cutis_tahap1_status_akhir');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index(['level'], 'idx_users_level');
            $table->index(['station_id', 'level'], 'idx_users_station_level');
        });

        Schema::table('role_user', function (Blueprint $table) {
            $table->index(['role_id', 'is_primary'], 'idx_role_user_role_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuan_cutis', function (Blueprint $table) {
            $table->dropIndex('idx_pengajuan_cutis_user_status_tgl');
            $table->dropIndex('idx_pengajuan_cutis_tahap1_status_akhir');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_level');
            $table->dropIndex('idx_users_station_level');
        });

        Schema::table('role_user', function (Blueprint $table) {
            $table->dropIndex('idx_role_user_role_primary');
        });
    }
};

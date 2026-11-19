<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah kolom schedule_type menjadi string/varchar(50) dengan default 'normal' (mendukung normal dan roster)
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN schedule_type VARCHAR(50) NULL DEFAULT 'normal'");
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->string('schedule_type', 50)->nullable()->default('normal')->change();
            });
        }

        // Migrasikan user yang sebelumnya bertipe 'reguler_5_hari', 'reguler_6_hari', atau NULL menjadi 'normal'
        DB::table('users')
            ->whereIn('schedule_type', ['reguler_5_hari', 'reguler_6_hari'])
            ->orWhereNull('schedule_type')
            ->update([
                'schedule_type' => 'normal',
                'normal_check_in' => '07:00:00',
                'normal_check_out' => '16:00:00',
                'normal_work_days' => json_encode(['Mon', 'Tue', 'Wed', 'Thu', 'Fri']),
            ]);
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN schedule_type ENUM('normal', 'roster') NULL DEFAULT 'normal'");
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->string('schedule_type', 50)->nullable()->default('normal')->change();
            });
        }
    }
};

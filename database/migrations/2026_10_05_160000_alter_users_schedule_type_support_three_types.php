<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah kolom schedule_type menjadi string/varchar(50) agar mendukung reguler_5_hari, reguler_6_hari, roster
        DB::statement("ALTER TABLE users MODIFY COLUMN schedule_type VARCHAR(50) NULL DEFAULT 'reguler_5_hari'");

        // Migrasikan user yang sebelumnya bertipe 'normal' atau NULL menjadi 'reguler_5_hari'
        DB::table('users')
            ->where('schedule_type', 'normal')
            ->orWhereNull('schedule_type')
            ->update([
                'schedule_type' => 'reguler_5_hari',
                'normal_check_in' => '07:00:00',
                'normal_check_out' => '16:00:00',
                'normal_work_days' => json_encode(['Mon', 'Tue', 'Wed', 'Thu', 'Fri']),
            ]);
    }

    public function down(): void
    {
        // Kembalikan ke enum sebelumnya
        DB::table('users')
            ->whereIn('schedule_type', ['reguler_5_hari', 'reguler_6_hari'])
            ->update(['schedule_type' => 'normal']);

        DB::statement("ALTER TABLE users MODIFY COLUMN schedule_type ENUM('normal', 'roster') NULL DEFAULT 'normal'");
    }
};

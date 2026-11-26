<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kehadirans', function (Blueprint $table) {
            $table->boolean('is_off_day_work')->default(false)->after('is_late');
            $table->boolean('is_rest_after_overtime')->default(false)->after('is_off_day_work');
            $table->date('checkout_date')->nullable()->after('check_out');
            $table->string('checkout_type', 30)->nullable()->after('checkout_date');
        });
    }

    public function down(): void
    {
        Schema::table('kehadirans', function (Blueprint $table) {
            $table->dropColumn([
                'is_off_day_work',
                'is_rest_after_overtime',
                'checkout_date',
                'checkout_type',
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menghapus tabel dan kolom khusus modul Presensi, Biometrik Wajah, dan Geofencing.
     */
    public function up(): void
    {
        Schema::dropIfExists('kehadirans');

        if (Schema::hasColumn('users', 'face_descriptor')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('face_descriptor');
            });
        }

        Schema::table('stations', function (Blueprint $table) {
            foreach (['latitude', 'longitude', 'radius_meters'] as $col) {
                if (Schema::hasColumn('stations', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    /**
     * Mengembalikan struktur kolom/tabel bila di-rollback.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('users', 'face_descriptor')) {
            Schema::table('users', function (Blueprint $table) {
                $table->longText('face_descriptor')->nullable()->after('roster_start_date');
            });
        }

        Schema::table('stations', function (Blueprint $table) {
            if (!Schema::hasColumn('stations', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable()->after('type');
            }
            if (!Schema::hasColumn('stations', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            }
            if (!Schema::hasColumn('stations', 'radius_meters')) {
                $table->integer('radius_meters')->default(1000)->after('longitude');
            }
        });
    }
};


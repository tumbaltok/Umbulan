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
        if (!Schema::hasTable('kehadirans')) {
            Schema::create('kehadirans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->date('date');
                $table->string('shift_type')->default('Normal');
                $table->time('scheduled_in')->nullable();
                $table->time('scheduled_out')->nullable();
                $table->time('check_in')->nullable();
                $table->decimal('check_in_lat', 10, 8)->nullable();
                $table->decimal('check_in_long', 11, 8)->nullable();
                $table->decimal('check_in_distance', 8, 2)->nullable();
                $table->boolean('is_in_radius_check_in')->default(true);
                $table->boolean('is_late')->default(false);
                $table->boolean('is_off_day_work')->default(false);
                $table->boolean('is_rest_after_overtime')->default(false);
                $table->boolean('is_face_verified_in')->default(false);
                $table->text('reason_in')->nullable();
                $table->string('evidence_in')->nullable();
                $table->time('check_out')->nullable();
                $table->date('checkout_date')->nullable();
                $table->string('checkout_type', 30)->nullable();
                $table->decimal('check_out_lat', 10, 8)->nullable();
                $table->decimal('check_out_long', 11, 8)->nullable();
                $table->decimal('check_out_distance', 8, 2)->nullable();
                $table->boolean('is_in_radius_check_out')->default(true);
                $table->boolean('is_early_checkout')->default(false);
                $table->boolean('is_face_verified_out')->default(false);
                $table->text('reason_out')->nullable();
                $table->string('evidence_out')->nullable();
                $table->enum('status', ['Hadir', 'Terlambat', 'Izin', 'Alpha', 'Libur'])->default('Hadir');
                $table->text('reason_out_of_radius_in')->nullable();
                $table->text('reason_checkout')->nullable();
                $table->string('face_photo_in')->nullable();
                $table->string('face_photo_out')->nullable();
                $table->unique(['user_id', 'date'], 'kehadirans_user_id_date_unique');
                $table->timestamps();
            });
        }

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


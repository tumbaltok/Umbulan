<?php

namespace App\Models\Absen;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User\User;

class Kehadiran extends Model
{
    use HasFactory;

    // Menentukan nama tabel yang aktif
    protected $table = 'kehadirans';

    protected $fillable = [
        'user_id',
        'tanggal',
        'date',
        'shift_type',
        'scheduled_in',
        'scheduled_out',
        'jam_masuk',
        'check_in',
        'check_in_lat',
        'check_in_long',
        'is_in_radius_check_in',
        'reason_out_of_radius_in',
        'face_photo_in',
        'jam_pulang',
        'check_out',
        'check_out_lat',
        'check_out_long',
        'is_in_radius_check_out',
        'is_early_checkout',
        'reason_checkout',
        'face_photo_out',
        'status_kehadiran',
        'latitude_masuk',
        'longitude_masuk',
        'latitude_pulang',
        'longitude_pulang',
        'keterangan'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
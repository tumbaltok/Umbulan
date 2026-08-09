<?php

namespace App\Models\Absen;

use Illuminate\Database\Eloquent\Model;
use App\Models\User\User;

class Absensi extends Model
{
    protected $table = 'absensis';

    protected $fillable = [
        'user_id',
        'tanggal',
        'jam_masuk',
        'jam_pulang',
        'status_kehadiran',
        'latitude_masuk',
        'longitude_masuk',
        'latitude_pulang',
        'longitude_pulang',
        'keterangan'
    ];

    // Relasi balik: Absensi ini milik siapa?
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

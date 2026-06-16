<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Station extends Model
{
    // Nama tabel di database (opsional, tapi bagus untuk penegasan)
    protected $table = 'stations';

    // Kolom yang diizinkan untuk diisi secara massal (Mass Assignment)
    protected $fillable = [
        'kode_stasiun',
        'name',
    ];

    /**
     * RELASI 1: Many-to-Many ke User (Khusus untuk Supervisor)
     * Mengambil semua Supervisor yang menjaga stasiun ini.
     */
    public function supervisors(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,               // Model tujuan
            'station_supervisor',      // Nama tabel pivot tengah
            'station_id',              // Foreign key di tabel pivot untuk model ini
            'supervisor_id'            // Foreign key di tabel pivot untuk model tujuan
        )->withTimestamps();           // Otomatis mengisi created_at & updated_at di tabel pivot
    }

    /**
     * RELASI 2: One-to-Many ke User (Untuk Karyawan Biasa)
     * Mengambil semua user/karyawan yang ditempatkan di stasiun ini.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'station_id', 'id');
    }
}

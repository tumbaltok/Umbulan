<?php

namespace App\Models\Cuti;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaldoCuti extends Model
{
    use HasFactory;

    protected $table = 'saldo_cutis';

    // Kolom yang dapat diisi secara massal (mass assignment)
    protected $fillable = [
        'user_id',
        'jenis_cuti_id',
        'kuota_awal',
        'bulan',
        'sisa_saldo',
        'tahun',
    ];

    // Relasi balik ke master jenis cuti
    public function jenisCuti()
    {
        return $this->belongsTo(JenisCuti::class, 'jenis_cuti_id');
    }
}

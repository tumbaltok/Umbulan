<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaldoCuti extends Model
{
    use HasFactory;

    protected $table = 'saldo_cutis';

    // WAJIB dimasukkan agar fungsi SaldoCuti::create() di controller bisa berjalan
    protected $fillable = [
        'user_id',
        'jenis_cuti_id',
        'tahun',
        'sisa_saldo'
    ];

    // Relasi dipindahkan ke sini
    public function jenisCuti()
    {
        return $this->belongsTo(JenisCuti::class, 'jenis_cuti_id');
    }
}

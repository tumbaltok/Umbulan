<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCuti extends Model
{
    use HasFactory;

    protected $table = 'sub_cutis';

    protected $fillable = [
        'jenis_cuti_id',
        'nama_sub_cuti',
        'durasi_default',
        'keterangan_opsional',
        'apakah_wajib_dokumen'
    ];

    protected $casts = [
        'apakah_wajib_dokumen' => 'boolean',
    ];

    // Relasi balik: Satu sub-cuti dimiliki oleh satu Jenis Cuti utama
    public function jenisCuti()
    {
        return $this->belongsTo(JenisCuti::class, 'jenis_cuti_id');
    }

    public function pengajuanCuti()
    {
        return $this->belongsTo(PengajuanCuti::class, 'sub_cuti_id');
    }
}

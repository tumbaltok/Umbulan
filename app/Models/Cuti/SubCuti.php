<?php

namespace App\Models\Cuti;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubCuti extends Model
{
    use HasFactory;

    protected $table = 'sub_cutis';

    protected $fillable = [
        'jenis_cuti_id',
        'nama_sub_cuti',
        'durasi_default',
        'keterangan_opsional',
        'apakah_wajib_dokumen',
    ];

    protected $casts = [
        'apakah_wajib_dokumen' => 'boolean',
    ];

    // Relasi balik: Satu sub-cuti dimiliki oleh satu Jenis Cuti utama
    public function jenisCuti(): BelongsTo
    {
        return $this->belongsTo(JenisCuti::class, 'jenis_cuti_id');
    }

    // Relasi: Satu Sub Cuti memiliki banyak pengajuan cuti
    public function pengajuanCuti(): HasMany
    {
        return $this->hasMany(PengajuanCuti::class, 'sub_cuti_id');
    }
}

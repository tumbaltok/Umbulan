<?php

namespace App\Models\Cuti;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubCuti extends Model
{
    use HasFactory;

    protected $table = 'jenis_cutis';

    protected $fillable = [
        'parent_id',
        'name_cuti',
        'durasi_default',
        'keterangan_opsional',
        'apakah_wajib_dokumen',
    ];

    protected $casts = [
        'apakah_wajib_dokumen' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('is_sub', fn ($q) => $q->whereNotNull('parent_id'));
    }

    public function getNamaSubCutiAttribute(): ?string
    {
        return $this->attributes['name_cuti'] ?? null;
    }

    public function setNamaSubCutiAttribute($value): void
    {
        $this->attributes['name_cuti'] = $value;
    }

    public function getJenisCutiIdAttribute(): ?int
    {
        return $this->attributes['parent_id'] ?? null;
    }

    public function setJenisCutiIdAttribute($value): void
    {
        $this->attributes['parent_id'] = $value;
    }

    // Relasi balik: Satu sub-cuti dimiliki oleh satu Jenis Cuti utama
    public function jenisCuti(): BelongsTo
    {
        return $this->belongsTo(JenisCuti::class, 'parent_id');
    }

    // Relasi: Satu Sub Cuti memiliki banyak pengajuan cuti
    public function pengajuanCuti(): HasMany
    {
        return $this->hasMany(PengajuanCuti::class, 'sub_cuti_id');
    }
}

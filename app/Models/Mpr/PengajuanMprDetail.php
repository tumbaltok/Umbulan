<?php

namespace App\Models\Mpr;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanMprDetail extends Model
{
    use HasFactory;

    protected $table = 'procurement_details';

    protected $fillable = [
        'procurement_id',
        'pengajuan_mpr_id',
        'nama_barang',
        'keterangan_item',
        'jumlah',
        'satuan',
        'estimasi_harga',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->procurement_id) && !empty($model->pengajuan_mpr_id)) {
                $model->procurement_id = $model->pengajuan_mpr_id;
            }
        });
    }

    public function pengajuanMpr()
    {
        return $this->belongsTo(PengajuanMpr::class, 'procurement_id');
    }

    public function getPengajuanMprIdAttribute()
    {
        return $this->attributes['procurement_id'] ?? null;
    }

    public function setPengajuanMprIdAttribute($value): void
    {
        $this->attributes['procurement_id'] = $value;
    }
}

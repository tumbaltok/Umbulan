<?php

namespace App\Models\Car;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanCarDetail extends Model
{
    use HasFactory;

    protected $table = 'procurement_details';

    protected $fillable = [
        'procurement_id',
        'pengajuan_car_id',
        'nama_barang',
        'jumlah',
        'satuan',
        'estimasi_harga',
        'ongkir',
        'total_harga',
        'dokumen_nota_or_proposal',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->procurement_id) && !empty($model->pengajuan_car_id)) {
                $model->procurement_id = $model->pengajuan_car_id;
            }
        });
    }

    public function pengajuanCar(): BelongsTo
    {
        return $this->belongsTo(PengajuanCar::class, 'procurement_id');
    }

    public function getPengajuanCarIdAttribute()
    {
        return $this->attributes['procurement_id'] ?? null;
    }

    public function setPengajuanCarIdAttribute($value): void
    {
        $this->attributes['procurement_id'] = $value;
    }
}

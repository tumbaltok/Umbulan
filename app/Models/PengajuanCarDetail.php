<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanCarDetail extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_car_details';

    protected $fillable = [
        'pengajuan_car_id',
        'nama_barang',
        'jumlah',
        'estimasi_harga',
        'total_harga',
        'dokumen_nota_or_proposal',
    ];

    /**
     * Relasi balik ke Header CAR
     */
    public function pengajuanCar()
    {
        return $this->belongsTo(PengajuanCar::class, 'pengajuan_car_id');
    }
}

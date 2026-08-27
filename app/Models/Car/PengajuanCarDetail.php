<?php

namespace App\Models\Car;

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
        'satuan',
        'estimasi_harga',
        'ongkir',
        'total_harga',
        'dokumen_nota_or_proposal',
    ];

    protected $casts = [
        'jumlah'         => 'float',
        'estimasi_harga' => 'float',
        'ongkir'         => 'float',
        'total_harga'    => 'float',
    ];

    /**
     * Relasi balik ke Header CAR
     */
    public function pengajuanCar()
    {
        // PEMBETULAN: Hubungkan relasi murni menggunakan foreign key pengajuan_car_id
        return $this->belongsTo(PengajuanCar::class, 'pengajuan_car_id');
    }
}

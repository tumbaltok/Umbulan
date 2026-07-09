<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PengajuanCar extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'alasan_pembelian',
        'receiving_account',
        'status_supervisor',
        'status_manager',
        'status_akhir',
        'catatan_penolakan'
    ];

    /**
     * Relasi ke tabel detail barang (Satu pengajuan CAR memiliki banyak detail barang)
     */
    public function details()
    {
        // Menyambungkan ke model PengajuanCarDetail menggunakan foreign key 'pengajuan_car_id'
        return $this->hasMany(PengajuanCarDetail::class, 'pengajuan_car_id');
    }

    /**
     * Relasi ke pengguna (User yang mengajukan CAR)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke pengguna (User yang mengajukan CAR)
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}

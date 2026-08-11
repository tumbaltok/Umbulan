<?php

namespace App\Models\Car;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanCar extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'alasan_pembelian',
        'receiving_account',
        'status_supervisor',
        'supervisor_id',
        'status_manager',
        'manager_id',
        'status_akhir',
        'catatan_penolakan',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function details()
    {
        return $this->hasMany(PengajuanCarDetail::class, 'pengajuan_car_id');
    }
}

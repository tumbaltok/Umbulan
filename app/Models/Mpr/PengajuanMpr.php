<?php

namespace App\Models\Mpr;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanMpr extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approverTahap1()
    {
        return $this->belongsTo(User::class, 'approver_tahap_1_id');
    }

    public function approverTahap2()
    {
        return $this->belongsTo(User::class, 'approver_tahap_2_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'approver_tahap_1_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'approver_tahap_2_id');
    }

    public function items()
    {
        return $this->hasMany(PengajuanMprDetail::class, 'pengajuan_mpr_id');
    }
}

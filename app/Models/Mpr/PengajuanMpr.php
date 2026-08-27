<?php

namespace App\Models\Mpr;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanMpr extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nomor_mpr',
        'priority',
        'department',
        'delivery_point',
        'latest_mpr_date',
        'tanggal_pengajuan',
        'keperluan_urgensi',
        'dokumen_pendukung',
        'status_tahap_1',
        'approver_tahap_1_id',
        'status_tahap_2',
        'approver_tahap_2_id',
        'status_akhir',
        'catatan_penolakan',
        'last_notified_at',
    ];

    protected $casts = [
        'last_notified_at'  => 'datetime',
        'tanggal_pengajuan' => 'date',
        'latest_mpr_date'   => 'date',
    ];

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

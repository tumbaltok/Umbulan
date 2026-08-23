<?php

namespace App\Models\Car;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PengajuanCar extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'alasan_pembelian',
        'receiving_account',
        'total_approval_levels',
        'status_tahap_1',
        'approver_tahap_1_id',
        'status_tahap_2',
        'approver_tahap_2_id',
        'status_akhir',
        'catatan_penolakan',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approverTahap1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_tahap_1_id');
    }

    public function approverTahap2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_tahap_2_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(PengajuanCarDetail::class, 'pengajuan_car_id');
    }
}

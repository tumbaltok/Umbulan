<?php

namespace App\Models\Cuti;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanCuti extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'jenis_cuti_id',
        'sub_cuti_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'total_hari',
        'alasan_cuti',
        'dokumen_pendukung',
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

    public function jenisCuti(): BelongsTo
    {
        return $this->belongsTo(JenisCuti::class, 'jenis_cuti_id');
    }

    public function subCuti(): BelongsTo
    {
        return $this->belongsTo(SubCuti::class, 'sub_cuti_id');
    }
}

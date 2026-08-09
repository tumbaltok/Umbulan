<?php

namespace App\Models\Cuti;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User\User;

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
        'status_supervisor', 
        'supervisor_id', 
        'status_manager', 
        'manager_id',   
        'status_akhir', 
        'catatan_penolakan'
    ];

    public function user(): BelongsTo 
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function supervisor(): BelongsTo 
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function manager(): BelongsTo 
    {
        return $this->belongsTo(User::class, 'manager_id');
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
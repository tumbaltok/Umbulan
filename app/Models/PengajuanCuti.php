<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id', 
    'jenis_cuti_id', 
    'sub_cuti_id', 
    'tanggal_mulai', 
    'tanggal_selesai', 
    'total_hari', 
    'alasan_cuti', 
    'dokumen_pendukung', 
    'status_supervisor', 
    'supervisor_id', // Tambahkan kolom ID Supervisor
    'status_manager', 
    'manager_id',    // Tambahkan kolom ID Manager
    'status_akhir', 
    'catatan_penolakan'
])]
class PengajuanCuti extends Model
{
    // Relasi ke Pemohon Cuti
    public function user(): BelongsTo 
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke Supervisor yang menyetujui
    public function supervisor(): BelongsTo 
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    // Relasi ke Manager yang menyetujui
    public function manager(): BelongsTo 
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    // Relasi ke Jenis Cuti
    public function jenisCuti(): BelongsTo 
    {
        return $this->belongsTo(JenisCuti::class);
    }

    // Relasi ke Sub Cuti
    public function subCuti(): BelongsTo 
    {
        return $this->belongsTo(SubCuti::class, 'sub_cuti_id', 'id');
    }
}
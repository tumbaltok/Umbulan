<?php

namespace App\Models\Mpr;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanMprDetail extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Relasi balik ke header PengajuanMpr
    public function pengajuanMpr()
    {
        return $this->belongsTo(PengajuanMpr::class, 'pengajuan_mpr_id');
    }
}
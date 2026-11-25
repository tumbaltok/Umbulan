<?php

namespace App\Models\Cuti;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisCuti extends Model
{
    protected $table = 'jenis_cutis';

    protected $guarded = ['id'];

    protected $casts = [
        'keterangan' => 'array',
        'apakah_wajib_dokumen' => 'boolean',
        'butuh_surat_dokter' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(JenisCuti::class, 'parent_id');
    }

    public function subCutis(): HasMany
    {
        return $this->hasMany(SubCuti::class, 'parent_id');
    }
}

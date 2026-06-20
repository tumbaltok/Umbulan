<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisCuti extends Model
{
    protected $casts = [
        'keterangan' => 'array',
    ];

    public function subCutis() // Perhatikan huruf besar 'C' (CamelCase)
    {
        return $this->hasMany(SubCuti::class, 'jenis_cuti_id');
    }
}

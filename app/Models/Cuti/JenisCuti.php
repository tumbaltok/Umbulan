<?php

namespace App\Models\Cuti;

use Illuminate\Database\Eloquent\Model;

class JenisCuti extends Model
{
    protected $casts = [
        'keterangan' => 'array',
    ];

    public function subCutis() 
    {
        return $this->hasMany(SubCuti::class, 'jenis_cuti_id');
    }
}

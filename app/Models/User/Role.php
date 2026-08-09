<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ['role_name', 'divisi', 'level', 'description', 'job_title'];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
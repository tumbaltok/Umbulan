<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class Gender extends Model
{
    protected $table = 'genders';

    protected $fillable = ['name'];
}

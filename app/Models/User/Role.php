<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'role_name', 
        'level',
        'description',
        'job_title',
        'parent_role_id',
        'approval_rules',
    ];

    protected $casts = [
        'approval_rules' => 'array',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // Relasi ke Atasan Langsung (Parent Role)
    public function parentRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'parent_role_id');
    }

    // Relasi ke Bawahan Langsung (Child Roles)
    public function childRoles(): HasMany
    {
        return $this->hasMany(Role::class, 'parent_role_id');
    }
}

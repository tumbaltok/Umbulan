<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'tree_code',
        'approval_rules',
    ];

    protected $casts = [
        'approval_rules' => 'array',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user')->withPivot('is_primary')->withTimestamps();
    }

    public function parentRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'parent_role_id');
    }

    public function childRoles(): HasMany
    {
        return $this->hasMany(Role::class, 'parent_role_id');
    }

    // --- RELASI TAMBAHAN UNTUK APPROVER ROLE ---

    /**
     * Role yang berwenang menyetujui Tahap/Level 1
     */
    public function approverLevel1Role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'approval_rules->approver_level_1_role_id');
    }

    /**
     * Role yang berwenang menyetujui Tahap/Level 2
     */
    public function approverLevel2Role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'approval_rules->approver_level_2_role_id');
    }
}

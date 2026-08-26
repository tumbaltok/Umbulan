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
        'approval_rules',
    ];

    protected $casts = [
        'approval_rules' => 'array',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'role_id', 'id');
    }

    public function parentRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'parent_role_id');
    }

    public function childRoles(): HasMany
    {
        return $this->hasMany(Role::class, 'parent_role_id');
    }

    /**
     * Mengambil semua role ID turunan/bawahan secara rekursif berbasis parent_role_id.
     * Termasuk role ID awal yang dikirimkan.
     *
     * @param array|int|\Illuminate\Support\Collection $roleIds
     * @return array
     */
    public static function getAllChildRoleIds(array|int|\Illuminate\Support\Collection $roleIds): array
    {
        if ($roleIds instanceof \Illuminate\Support\Collection) {
            $roleIds = $roleIds->toArray();
        } elseif (!is_array($roleIds)) {
            $roleIds = [$roleIds];
        }

        $allIds = array_values(array_filter($roleIds));
        $currentParentIds = $allIds;

        while (!empty($currentParentIds)) {
            $childIds = static::whereIn('parent_role_id', $currentParentIds)->pluck('id')->toArray();
            if (empty($childIds)) {
                break;
            }
            $allIds = array_merge($allIds, $childIds);
            $currentParentIds = $childIds;
        }

        return array_values(array_unique($allIds));
    }

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

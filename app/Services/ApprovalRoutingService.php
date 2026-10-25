<?php

namespace App\Services;

use App\Models\User\Role;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ApprovalRoutingService
{
    /**
     * Menentukan rute persetujuan (approval route) yang tepat untuk seorang pemohon pada modul tertentu.
     *
     * Prioritas penentuan:
     * 1. Ambil primary role pemohon (atau fallback ke role_id / roles->first()).
     * 2. Cek apakah ada konfigurasi spesifik modul pada approval_rules role tersebut (cuti, car, mpr).
     * 3. Jika approver_1_role_id disetel pada modul -> gunakan aturan modul tersebut (1-step atau 2-step).
     * 4. Jika approver_1_role_id kosong/belum diatur pada modul -> fallback ke Atasan Langsung (parent_role_id) sebagai 1-step Approver 1.
     * 5. Jika parent_role_id juga kosong dan role adalah Top Level (Puncak/Direksi) -> otomatis Auto-Approved.
     * 6. Jika tidak memenuhi seluruhnya -> kembalikan error (unconfigured).
     *
     * @param User $submitter
     * @param string $module 'cuti' | 'car' | 'mpr'
     * @return array
     */
    public function resolveApprovalRoute(User $submitter, string $module): array
    {
        $primaryRole = $this->resolveSubmitterPrimaryRole($submitter);

        if (!$primaryRole) {
            return [
                'success'            => false,
                'error'              => "Role/jabatan Anda belum terdaftar dalam sistem.",
                'levels'             => 0,
                'approver_1_role_id' => null,
                'approver_2_role_id' => null,
                'approver_1_role'    => null,
                'approver_2_role'    => null,
                'is_top_level'       => false,
                'is_auto_approved'   => false,
                'status_tahap_1'     => 'rejected',
                'status_tahap_2'     => 'not_required',
                'status_akhir'       => 'rejected',
                'primary_role'       => null,
            ];
        }

        $rules = $primaryRole->approval_rules ?? [];
        if (!is_array($rules)) {
            $rules = [];
        }

        $moduleRules = $rules[$module] ?? [];
        if (!is_array($moduleRules)) {
            $moduleRules = [];
        }

        // 1. Cek aturan spesifik modul
        $hasExplicitModuleApprover1 = !empty($moduleRules['approver_1_role_id']);
        $levels = (int) ($moduleRules['levels'] ?? ($rules['approval_levels'] ?? 1));
        if ($levels !== 2) {
            $levels = 1;
        }

        $approver1RoleId = null;
        $approver2RoleId = null;

        if ($hasExplicitModuleApprover1) {
            $approver1RoleId = (int) $moduleRules['approver_1_role_id'];
            if ($levels === 2 && !empty($moduleRules['approver_2_role_id'])) {
                $approver2RoleId = (int) $moduleRules['approver_2_role_id'];
            } else {
                $levels = 1;
            }
        } elseif (!empty($primaryRole->parent_role_id)) {
            // 2. Fallback ke Atasan Langsung (parent_role_id) sebagai 1-step persetujuan
            $approver1RoleId = (int) $primaryRole->parent_role_id;
            $approver2RoleId = null;
            $levels = 1;
        }

        // 3. Cek apakah role merupakan Top Level (tanpa atasan dan tanpa approver)
        $isTopLevel = $primaryRole->isTopLevel() || $submitter->isTopLevel();

        if (empty($approver1RoleId) && $isTopLevel) {
            return [
                'success'            => true,
                'error'              => null,
                'levels'             => 1,
                'approver_1_role_id' => null,
                'approver_2_role_id' => null,
                'approver_1_role'    => null,
                'approver_2_role'    => null,
                'is_top_level'       => true,
                'is_auto_approved'   => true,
                'status_tahap_1'     => 'approved',
                'status_tahap_2'     => 'not_required',
                'status_akhir'       => 'approved',
                'primary_role'       => $primaryRole,
            ];
        }

        if (empty($approver1RoleId)) {
            $moduleName = strtoupper($module);
            return [
                'success'            => false,
                'error'              => "Alur persetujuan {$moduleName} untuk jabatan Anda ({$primaryRole->role_name}) belum dikonfigurasi dan tidak memiliki Atasan Langsung.",
                'levels'             => 0,
                'approver_1_role_id' => null,
                'approver_2_role_id' => null,
                'approver_1_role'    => null,
                'approver_2_role'    => null,
                'is_top_level'       => false,
                'is_auto_approved'   => false,
                'status_tahap_1'     => 'pending',
                'status_tahap_2'     => 'not_required',
                'status_akhir'       => 'pending',
                'primary_role'       => $primaryRole,
            ];
        }

        $approver1Role = Role::find($approver1RoleId);
        $approver2Role = $approver2RoleId ? Role::find($approver2RoleId) : null;

        $statusTahap1 = 'pending';
        $statusTahap2 = ($levels === 2 && !empty($approver2RoleId)) ? 'pending' : 'not_required';
        $statusAkhir  = 'pending';

        return [
            'success'            => true,
            'error'              => null,
            'levels'             => $levels,
            'approver_1_role_id' => $approver1RoleId,
            'approver_2_role_id' => $approver2RoleId,
            'approver_1_role'    => $approver1Role,
            'approver_2_role'    => $approver2Role,
            'is_top_level'       => false,
            'is_auto_approved'   => false,
            'status_tahap_1'     => $statusTahap1,
            'status_tahap_2'     => $statusTahap2,
            'status_akhir'       => $statusAkhir,
            'primary_role'       => $primaryRole,
        ];
    }

    /**
     * Mengambil daftar User yang berwenang sebagai approver pada tahapan tertentu.
     * Mencakup pengguna dengan role via pivot role_user maupun users.role_id langsung,
     * serta mengecualikan pemohon (self-approval protection).
     *
     * @param User $submitter
     * @param string $module
     * @param int $stage 1 atau 2
     * @return Collection
     */
    public function getApproversForStage(User $submitter, string $module, int $stage = 1): Collection
    {
        $route = $this->resolveApprovalRoute($submitter, $module);

        if (!$route['success'] || $route['is_auto_approved']) {
            return collect();
        }

        $targetRoleId = ($stage === 2) ? $route['approver_2_role_id'] : $route['approver_1_role_id'];

        if (!$targetRoleId) {
            return collect();
        }

        return User::where(function ($q) use ($targetRoleId) {
            $q->whereHas('roles', fn ($rq) => $rq->where('roles.id', $targetRoleId))
              ->orWhere('role_id', $targetRoleId);
        })
        ->where('id', '!=', $submitter->id)
        ->get();
    }

    /**
     * Memvalidasi apakah seorang atasan berhak memproses persetujuan pengajuan saat ini.
     *
     * @param User $atasan
     * @param mixed $pengajuan (PengajuanCuti | PengajuanCar | PengajuanMpr)
     * @param string $module
     * @return array ['authorized' => bool, 'reason' => string|null]
     */
    public function isUserAuthorizedToApprove(User $atasan, $pengajuan, string $module): array
    {
        // 1. Proteksi Self-Approval: Pemohon dilarang menyetujui pengajuannya sendiri
        if ((int) $pengajuan->user_id === (int) $atasan->id) {
            return [
                'authorized' => false,
                'reason'     => 'Aksi ditolak: Anda tidak dapat memproses persetujuan pengajuan Anda sendiri (Self-Approval Protection)!',
            ];
        }

        // 2. Proteksi Anti-Collusion: Approver Tahap 1 dilarang memproses Tahap 2
        if ($pengajuan->status_tahap_1 === 'approved' && $pengajuan->status_tahap_2 === 'pending') {
            if ((int) $pengajuan->approver_tahap_1_id === (int) $atasan->id) {
                return [
                    'authorized' => false,
                    'reason'     => 'Aksi ditolak: Anda telah memproses Tahap 1 dan tidak dapat memproses Tahap 2 pada pengajuan yang sama (Anti-Collusion Protection)!',
                ];
            }
        }

        // 3. Cek apakah status sudah bukan pending
        if ($pengajuan->status_akhir !== 'pending') {
            return [
                'authorized' => false,
                'reason'     => 'Pengajuan ini sudah pernah diproses sebelumnya.',
            ];
        }

        // 4. Hak Akses Administrator Sistem (Admin Level 1)
        if ($atasan->isLevel1() || $atasan->hasRole('ADMIN')) {
            return [
                'authorized' => true,
                'reason'     => null,
            ];
        }

        // 5. Verifikasi Wewenang Jabatan Berdasarkan Aturan Dinamis
        $submitter = $pengajuan->user;
        if (!$submitter) {
            return [
                'authorized' => false,
                'reason'     => 'Data pemohon pengajuan tidak ditemukan.',
            ];
        }

        $route = $this->resolveApprovalRoute($submitter, $module);
        if (!$route['success']) {
            return [
                'authorized' => false,
                'reason'     => $route['error'] ?? 'Aturan persetujuan untuk pengajuan ini belum dikonfigurasi.',
            ];
        }

        $isTahap1 = ($pengajuan->status_tahap_1 === 'pending');
        $isTahap2 = ($pengajuan->status_tahap_1 === 'approved' && $pengajuan->status_tahap_2 === 'pending');

        $requiredRoleId = null;
        if ($isTahap1) {
            $requiredRoleId = $route['approver_1_role_id'];
        } elseif ($isTahap2) {
            $requiredRoleId = $route['approver_2_role_id'];
        }

        if (empty($requiredRoleId)) {
            return [
                'authorized' => false,
                'reason'     => 'Aksi ditolak: Aturan wewenang persetujuan untuk tahap ini belum dikonfigurasi dalam sistem.',
            ];
        }

        $atasanRoleIds = $this->getUserRoleIds($atasan);

        if (!in_array((int) $requiredRoleId, $atasanRoleIds)) {
            return [
                'authorized' => false,
                'reason'     => 'Aksi ditolak: Anda tidak memiliki wewenang jabatan untuk memproses persetujuan tahap ini.',
            ];
        }

        return [
            'authorized' => true,
            'reason'     => null,
        ];
    }

    /**
     * Menerapkan query filter scope untuk antrean persetujuan atasan (Inbox & Sidebar Badges).
     * Memastikan hanya pengajuan yang tepat sasaran yang muncul untuk atasan bersangkutan.
     *
     * @param Builder $query
     * @param User $atasan
     * @param string $module
     * @return Builder
     */
    public function applyPendingApprovalScope(Builder $query, User $atasan, string $module): Builder
    {
        $atasanRoleIds = $this->getUserRoleIds($atasan);
        $isAdmin = $atasan->isLevel1() || $atasan->hasRole('ADMIN');

        // Administrator memantau seluruh antrean pending global
        if ($isAdmin) {
            return $query->where('status_akhir', 'pending')
                         ->where('user_id', '!=', $atasan->id);
        }

        return $query->where('user_id', '!=', $atasan->id)
            ->where(function ($q) use ($atasanRoleIds, $atasan, $module) {
                // TAHAP 1 PENDING
                $q->where(function ($sub) use ($atasanRoleIds, $module) {
                    $sub->where('status_tahap_1', 'pending')
                        ->where(function ($userFilter) use ($atasanRoleIds, $module) {
                            // Filter via user.roles (pivot)
                            $userFilter->whereHas('user.roles', function ($rq) use ($atasanRoleIds, $module) {
                                $rq->where(function ($jsonQ) use ($atasanRoleIds, $module) {
                                    foreach ($atasanRoleIds as $roleId) {
                                        $jsonQ->orWhere("approval_rules->{$module}->approver_1_role_id", (int) $roleId)
                                              ->orWhere("approval_rules->{$module}->approver_1_role_id", (string) $roleId)
                                              ->orWhere('approval_rules->approver_level_1_role_id', (int) $roleId)
                                              ->orWhere('approval_rules->approver_level_1_role_id', (string) $roleId)
                                              // Fallback Atasan Langsung (parent_role_id) jika aturan modul belum disetel
                                              ->orWhere(function ($fallbackQ) use ($roleId, $module) {
                                                  $fallbackQ->where('parent_role_id', $roleId)
                                                            ->where(function ($nullQ) use ($module) {
                                                                $nullQ->whereNull("approval_rules->{$module}->approver_1_role_id")
                                                                      ->orWhere("approval_rules->{$module}->approver_1_role_id", '');
                                                            });
                                              });
                                    }
                                });
                            })
                            // Filter via user.role (direct BelongsTo role_id)
                            ->orWhereHas('user.role', function ($rq) use ($atasanRoleIds, $module) {
                                $rq->where(function ($jsonQ) use ($atasanRoleIds, $module) {
                                    foreach ($atasanRoleIds as $roleId) {
                                        $jsonQ->orWhere("approval_rules->{$module}->approver_1_role_id", (int) $roleId)
                                              ->orWhere("approval_rules->{$module}->approver_1_role_id", (string) $roleId)
                                              ->orWhere('approval_rules->approver_level_1_role_id', (int) $roleId)
                                              ->orWhere('approval_rules->approver_level_1_role_id', (string) $roleId)
                                              // Fallback Atasan Langsung (parent_role_id) jika aturan modul belum disetel
                                              ->orWhere(function ($fallbackQ) use ($roleId, $module) {
                                                  $fallbackQ->where('parent_role_id', $roleId)
                                                            ->where(function ($nullQ) use ($module) {
                                                                $nullQ->whereNull("approval_rules->{$module}->approver_1_role_id")
                                                                      ->orWhere("approval_rules->{$module}->approver_1_role_id", '');
                                                            });
                                              });
                                    }
                                });
                            });
                        });
                })
                // TAHAP 2 PENDING (Tahap 1 approved, Anti-Collusion: bukan approver tahap 1)
                ->orWhere(function ($sub) use ($atasanRoleIds, $atasan, $module) {
                    $sub->where('status_tahap_1', 'approved')
                        ->where('status_tahap_2', 'pending')
                        ->where('status_tahap_2', '!=', 'not_required')
                        ->where('approver_tahap_1_id', '!=', $atasan->id)
                        ->where(function ($userFilter) use ($atasanRoleIds, $module) {
                            $userFilter->whereHas('user.roles', function ($rq) use ($atasanRoleIds, $module) {
                                $rq->where(function ($jsonQ) use ($atasanRoleIds, $module) {
                                    foreach ($atasanRoleIds as $roleId) {
                                        $jsonQ->orWhere("approval_rules->{$module}->approver_2_role_id", (int) $roleId)
                                              ->orWhere("approval_rules->{$module}->approver_2_role_id", (string) $roleId)
                                              ->orWhere('approval_rules->approver_level_2_role_id', (int) $roleId)
                                              ->orWhere('approval_rules->approver_level_2_role_id', (string) $roleId);
                                    }
                                });
                            })
                            ->orWhereHas('user.role', function ($rq) use ($atasanRoleIds, $module) {
                                $rq->where(function ($jsonQ) use ($atasanRoleIds, $module) {
                                    foreach ($atasanRoleIds as $roleId) {
                                        $jsonQ->orWhere("approval_rules->{$module}->approver_2_role_id", (int) $roleId)
                                              ->orWhere("approval_rules->{$module}->approver_2_role_id", (string) $roleId)
                                              ->orWhere('approval_rules->approver_level_2_role_id', (int) $roleId)
                                              ->orWhere('approval_rules->approver_level_2_role_id', (string) $roleId);
                                    }
                                });
                            });
                        });
                });
            });
    }

    /**
     * Mengambil primary role seorang user secara konsisten.
     */
    public function resolveSubmitterPrimaryRole(User $user): ?Role
    {
        // 1. Prioritaskan relasi pivot is_primary = true
        $primaryRole = $user->roles()->wherePivot('is_primary', true)->first();
        if ($primaryRole) {
            return $primaryRole;
        }

        // 2. Jika tidak ada pivot is_primary, cek users.role_id
        if (!empty($user->role_id)) {
            $role = Role::find($user->role_id);
            if ($role) {
                return $role;
            }
        }

        // 3. Fallback ke role pertama yang dimiliki
        return $user->roles->first() ?? $user->role;
    }

    /**
     * Mengambil seluruh ID peran yang dipegang oleh seorang user.
     */
    public function getUserRoleIds(User $user): array
    {
        $roleIds = $user->roles->pluck('id')->map(fn ($id) => (int) $id)->toArray();

        if (!empty($user->role_id) && !in_array((int) $user->role_id, $roleIds)) {
            $roleIds[] = (int) $user->role_id;
        }

        return array_values(array_unique($roleIds));
    }
}

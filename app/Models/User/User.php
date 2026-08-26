<?php

namespace App\Models\User;

use App\Models\Absen\Kehadiran;
use App\Models\Cuti\JenisCuti;
use App\Models\Cuti\PengajuanCuti;
use App\Models\Cuti\SaldoCuti;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nip',
        'name',
        'email',
        'password',
        'role_id',
        'gender_id',
        'station_id',
        'atasan_langsung_id',
        'atasan_dua_id',
        'phone_number',
        'profile_photo',
        'signature',
        'phone_verified_at',
        'schedule_type',
        'normal_work_days',
        'normal_check_in',
        'normal_check_out',
        'roster_start_date',
        'face_descriptor',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'normal_work_days' => 'array',
            'face_descriptor' => 'array',
            'roster_start_date' => 'date',
            'jobdesk' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::created(function ($user) {
            $semuaJenisCuti = JenisCuti::all();

            foreach ($semuaJenisCuti as $cuti) {
                // Proteksi Cuti Melahirkan (id = 3) -> Hanya untuk Gender Wanita (id = 2)
                if ($cuti->id == 3 && $user->gender_id != 2) {
                    continue;
                }

                $saldoAwal = $cuti->kuota_default ?? 0;

                // Jika Ijin Meninggalkan Pekerjaan (id = 1) dan Gender Wanita (id = 2), beri kuota 2 untuk Haid
                if ($cuti->id == 1 && $user->gender_id == 2) {
                    $saldoAwal = 2;
                }

                SaldoCuti::create([
                    'user_id' => $user->id,
                    'jenis_cuti_id' => $cuti->id,
                    'tahun' => now()->year,
                    'kuota_awal' => $saldoAwal,
                    'sisa_saldo' => $saldoAwal,
                ]);
            }

            // Sync role_id awal ke pivot role_user jika ada
            if (!empty($user->role_id)) {
                $user->roles()->syncWithoutDetaching([$user->role_id => ['is_primary' => true]]);
            }
        });
    }

    protected $attributes = [
        'schedule_type' => null,
    ];

    const CUTI_TAHUNAN_ID = 4;
    const CUTI_HAID_ID = 5;

    /**
     * Relasi Many-to-Many ke tabel roles via pivot role_user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    /**
     * Relasi BelongsTo ke role utama (backward compatibility).
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Mengambil Primary Role (role utama pengguna).
     */
    public function getPrimaryRoleAttribute(): ?Role
    {
        return $this->roles->where('pivot.is_primary', true)->first() 
            ?? $this->roles->first() 
            ?? $this->role;
    }

    /**
     * Memeriksa apakah user memiliki satu atau lebih role tertentu (berdasarkan nama atau ID).
     */
    public function hasRole(string|int|array|Role|\Illuminate\Support\Collection $roles): bool
    {
        if ($roles instanceof \Illuminate\Support\Collection) {
            $roles = $roles->toArray();
        } elseif ($roles instanceof Role) {
            $roles = [$roles->id];
        } elseif (!is_array($roles)) {
            $roles = [$roles];
        }

        $userRoles = $this->roles;
        $userRoleNames = $userRoles->pluck('role_name')->map(fn($n) => strtoupper(trim($n)))->toArray();
        $userRoleIds = $userRoles->pluck('id')->toArray();

        foreach ($roles as $r) {
            if ($r instanceof Role) {
                if (in_array($r->id, $userRoleIds)) return true;
            } elseif (is_numeric($r)) {
                if (in_array((int) $r, $userRoleIds)) return true;
            } elseif (is_string($r)) {
                $searchName = strtoupper(trim($r));
                if (in_array($searchName, $userRoleNames)) return true;
            }
        }

        return false;
    }

    /**
     * Memeriksa apakah user memiliki setidaknya satu role dari array yang diberikan.
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->hasRole($roles);
    }

    /**
     * Memeriksa apakah user memiliki seluruh role dalam array yang diberikan.
     */
    public function hasAllRoles(array $roles): bool
    {
        foreach ($roles as $r) {
            if (!$this->hasRole($r)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Mengembalikan daftar string nama seluruh role yang dimiliki user.
     */
    public function rolesList(): array
    {
        return $this->roles->pluck('role_name')->toArray();
    }

    /**
     * Menambahkan satu atau beberapa role ke user.
     */
    public function assignRole(string|int|array|Role|\Illuminate\Support\Collection $roles): void
    {
        $roleIds = $this->resolveRoleIds($roles);
        if (!empty($roleIds)) {
            $this->roles()->syncWithoutDetaching($roleIds);
            if (empty($this->role_id)) {
                $this->update(['role_id' => $roleIds[0]]);
            }
            $this->unsetRelation('roles');
        }
    }

    /**
     * Menghapus satu atau beberapa role dari user.
     */
    public function removeRole(string|int|array|Role|\Illuminate\Support\Collection $roles): void
    {
        $roleIds = $this->resolveRoleIds($roles);
        if (!empty($roleIds)) {
            $this->roles()->detach($roleIds);
            if (in_array($this->role_id, $roleIds)) {
                $newPrimary = $this->roles()->first();
                $this->update(['role_id' => $newPrimary?->id]);
            }
            $this->unsetRelation('roles');
        }
    }

    /**
     * Helper privat untuk menormalisasi input role (string/int/object) menjadi array ID.
     */
    protected function resolveRoleIds(mixed $roles): array
    {
        if ($roles instanceof \Illuminate\Support\Collection) {
            $roles = $roles->toArray();
        } elseif ($roles instanceof Role) {
            return [$roles->id];
        } elseif (!is_array($roles)) {
            $roles = [$roles];
        }

        $ids = [];
        foreach ($roles as $r) {
            if ($r instanceof Role) {
                $ids[] = $r->id;
            } elseif (is_numeric($r)) {
                $ids[] = (int) $r;
            } elseif (is_string($r)) {
                $found = Role::whereRaw('LOWER(role_name) = ?', [strtolower(trim($r))])->first();
                if ($found) {
                    $ids[] = $found->id;
                }
            }
        }

        return array_values(array_unique(array_filter($ids)));
    }

    /**
     * Memeriksa apakah user memegang setidaknya satu role di level puncak (Top Level).
     */
    public function isTopLevel(): bool
    {
        return $this->roles->contains(fn($r) => empty($r->parent_role_id));
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'station_id', 'id');
    }

    public function gender(): BelongsTo
    {
        return $this->belongsTo(Gender::class, 'gender_id', 'id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atasan_langsung_id', 'id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atasan_dua_id', 'id');
    }

    public function stations(): BelongsToMany
    {
        return $this->belongsToMany(Station::class, 'station_supervisor', 'supervisor_id', 'station_id');
    }

    public function cuti_aktif(): HasMany
    {
        return $this->hasMany(PengajuanCuti::class, 'user_id')
            ->where('status_akhir', 'approved')
            ->whereDate('tanggal_mulai', '<=', now())
            ->whereDate('tanggal_selesai', '>=', now());
    }

    public function saldo_cuti_tahunan(int $jenisCutiId): HasOne
    {
        return $this->hasOne(SaldoCuti::class, 'user_id')
            ->where('jenis_cuti_id', $jenisCutiId)
            ->whereNull('bulan')
            ->where('tahun', date('Y'));
    }

    public function saldo_cuti_haid(): HasOne
    {
        return $this->hasOne(SaldoCuti::class, 'user_id')
            ->where('jenis_cuti_id', self::CUTI_HAID_ID)
            ->where('bulan', date('n'))
            ->where('tahun', date('Y'));
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Kehadiran::class, 'user_id');
    }

    public function saldoCuti(): HasMany
    {
        return $this->hasMany(SaldoCuti::class, 'user_id');
    }

    public function pengajuanCuti(): HasMany
    {
        return $this->hasMany(PengajuanCuti::class, 'user_id');
    }
}

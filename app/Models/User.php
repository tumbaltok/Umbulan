<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'job_title',
        'phone_number',
        'profile_photo'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    const CUTI_TAHUNAN_ID = 4;
    const CUTI_HAID_ID = 5;

    const JOB_OPERATOR = 'Operator';
    const JOB_MAINTENANCE = 'Maintenance';
    const JOB_HSE = 'HSE';
    const JOB_DOKUMENTASI = 'Dokumentasi';

    public function cuti_aktif(): HasMany
    {
        return $this->hasMany(PengajuanCuti::class, 'user_id')
                    ->where('status_manager', 'approved')
                    ->whereDate('tanggal_mulai', '<=', now())
                    ->whereDate('tanggal_selesai', '>=', now());
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'station_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function gender(): BelongsTo
    {
        return $this->belongsTo(Gender::class, 'gender_id');
    }

    public function stations(): BelongsToMany
    {
        return $this->belongsToMany(Station::class, 'station_supervisor', 'supervisor_id', 'station_id');
    }

    /**
     * PERBAIKAN: Melengkapi relasi saldo cuti tahunan yang terpotong
     */
    public function saldo_cuti_tahunan($jenisCutiId): HasOne
    {
        return $this->hasOne(SaldoCuti::class, 'user_id')
                    ->where('jenis_cuti_id', $jenisCutiId)
                    ->whereNull('bulan')
                    ->where('tahun', date('Y'));
    }

    /**
     * TAMBAHAN: Relasi Khusus Saldo Cuti Haid Bulanan
     */
    public function saldo_cuti_haid(): HasOne
    {
        return $this->hasOne(SaldoCuti::class, 'user_id')
                    ->where('jenis_cuti_id', self::CUTI_HAID_ID)
                    ->where('bulan', date('n'))
                    ->where('tahun', date('Y'));
    }
}

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
use App\Models\JenisCuti;
use App\Models\SaldoCuti;

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
        ];
    }

    protected static function booted(): void
    {
        static::created(function ($user) {
            $jenisCuti = JenisCuti::where('name_cuti', 'LIKE', '%Cuti%')
                                ->where('kuota_default', 12)
                                ->first();
                                
            $jenisCutiId = $jenisCuti ? $jenisCuti->id : self::CUTI_TAHUNAN_ID;

            SaldoCuti::create([
                'user_id'       => $user->id,
                'jenis_cuti_id' => $jenisCutiId,
                'tahun'         => now()->year,
                'kuota_awal'    => 12, 
                'sisa_saldo'    => 12, // Disesuaikan dengan $fillable SaldoCuti
            ]);
        });
    }

    protected $attributes = [
        'schedule_type' => null, 
    ];

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
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

    public function gender(): BelongsTo
    {
        return $this->belongsTo(Gender::class, 'gender_id', 'id');
    }

    public function stations(): BelongsToMany
    {
        return $this->belongsToMany(Station::class, 'station_supervisor', 'supervisor_id', 'station_id');
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
}
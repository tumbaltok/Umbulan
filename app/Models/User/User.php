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
        'sektor',
        'job_title',
        'jobdesk',
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
            // Ambil semua master Jenis Cuti
            $semuaJenisCuti = JenisCuti::all();

            foreach ($semuaJenisCuti as $cuti) {
                // 1. Proteksi Cuti Melahirkan (id = 3) -> Hanya untuk Gender Wanita (id = 2)
                if ($cuti->id == 3 && $user->gender_id != 2) {
                    continue;
                }

                // 2. Tentukan kuota & saldo awal
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

    // --- RELASI MODEL ---

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'station_id', 'id');
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

    public function cuti_aktif(): HasMany
    {
        return $this->hasMany(PengajuanCuti::class, 'user_id')
            ->where('status_manager', 'approved')
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

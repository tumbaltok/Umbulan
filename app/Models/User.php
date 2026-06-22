<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany; // Tambahan: Wajib di-import untuk relasi cuti_aktif

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Atribut yang dapat diisi secara massal (Mass Assignable).
     *
     * @var array<int, string>
     */
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

    /**
     * Atribut yang harus disembunyikan saat serialisasi (seperti ke JSON/API).
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Atribut yang harus di-cast ke tipe data tertentu.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    const CUTI_TAHUNAN_ID = 4;

    const JOB_OPERATOR = 'Operator';
    const JOB_MAINTENANCE = 'Maintenance';
    const JOB_HSE = 'HSE';
    const JOB_DOKUMENTASI = 'Dokumentasi';

    // ==========================================
    // DEFINISI RELASI DATA (Aman dari N+1)
    // ==========================================

    /**
     * Relasi ke data pengajuan cuti yang berstatus AKTIF (Dicari oleh KaryawanController)
     */
    public function cuti_aktif(): HasMany
    {
        return $this->hasMany(PengajuanCuti::class, 'user_id')
                    ->where('status_manager', 'approved') // Ubah 'approved' sesuai string sukses di database Anda (misal: 'disetujui' atau 'success')
                    ->whereDate('tanggal_mulai', '<=', now())
                    ->whereDate('tanggal_selesai', '>=', now());
    }
    /**
     * Relasi ke Station (Karyawan memiliki satu Home Station)
     */
    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'station_id');
    }

    /**
     * Relasi ke Role (Karyawan memiliki satu Role)
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Relasi ke Gender
     */
    public function gender(): BelongsTo
    {
        return $this->belongsTo(Gender::class, 'gender_id');
    }

    /**
     * Relasi Khusus Atasan/Supervisor yang membawahi banyak Station
     */
    public function stations(): BelongsToMany
    {
        return $this->belongsToMany(Station::class, 'station_supervisor', 'supervisor_id', 'station_id');
    }

    /**
     * Relasi ke Jatah Saldo Cuti Tahunan Aktif saat ini
     */
    public function saldo_cuti(): HasOne
    {
        return $this->hasOne(SaldoCuti::class, 'user_id')
                    ->where('jenis_cuti_id', self::CUTI_TAHUNAN_ID)
                    ->where('tahun', date('Y'));
    }
}

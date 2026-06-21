<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\SaldoCuti;

#[Fillable(['nip', 'name', 'email', 'password', 'role_id', 'gender_id', 'station_id', 'job_title', 'phone_number', 'profile_photo'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
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

    // Relasi ke Station
    public function station() {
        return $this->belongsTo(Station::class);
    }

    // Relasi ke Role
    public function role() {
        return $this->belongsTo(Role::class, 'role_id');
    }

    // Relasi ke Gender
    public function gender() {
        return $this->belongsTo(Gender::class, 'gender_id');
    }

    public function stations(): BelongsToMany
    {
        // Hubungkan User ke Station melalui tabel pivot station_supervisor
        return $this->belongsToMany(Station::class, 'station_supervisor', 'supervisor_id', 'station_id');
    }

    public function saldo_cuti()
    {
        // Cuti Tahunan utama biasanya memiliki jenis_cuti_id = 4 (sesuai DatabaseSeeder Anda)
        // dan difilter berdasarkan tahun saat ini
        return $this->hasOne(SaldoCuti::class, 'user_id')
                    ->where('jenis_cuti_id', 4) // Sesuaikan ID Cuti Tahunan Anda
                    ->where('tahun', date('Y'));
    }
}

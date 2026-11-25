<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Station extends Model
{
    protected $table = 'stations';

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('daftar_stasiun_geofence'));
        static::deleted(fn () => Cache::forget('daftar_stasiun_geofence'));
    }

    protected $fillable = [
        'name',
        'type',
        'kode_stasiun',
        'latitude',
        'longitude',
        'radius_meters',
    ];

    public function supervisors(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'station_supervisor',
            'station_id',
            'supervisor_id'
        )->withTimestamps();
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'station_id', 'id');
    }

    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'station_user',
            'station_id',
            'user_id'
        )->withTimestamps();
    }
}

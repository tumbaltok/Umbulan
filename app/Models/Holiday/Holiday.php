<?php

namespace App\Models\Holiday;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    protected $table = 'holidays';

    protected $fillable = [
        'holiday_date',
        'holiday_name',
        'holiday_year',
        'is_cuti_bersama',
        'source',
        'description',
    ];

    protected $casts = [
        'holiday_date' => 'string',
        'holiday_year' => 'integer',
        'is_cuti_bersama' => 'boolean',
    ];

    /**
     * Mutator untuk memastikan format tanggal selalu Y-m-d.
     */
    public function setHolidayDateAttribute($value): void
    {
        $this->attributes['holiday_date'] = substr((string) $value, 0, 10);
    }

    /**
     * Scope untuk memfilter hari libur berdasarkan tahun kalender.
     */
    public function scopeForYear(Builder $query, int|string $year): Builder
    {
        return $query->where('holiday_year', (int) $year);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kehadiran extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'shift_type',
        'scheduled_in',
        'scheduled_out',
        'check_in',
        'check_in_lat',
        'check_in_long',
        'is_in_radius_check_in',
        'reason_out_of_radius_in',
        'face_photo_in',
        'check_out',
        'check_out_lat',
        'check_out_long',
        'is_in_radius_check_out',
        'is_early_checkout',
        'reason_checkout',
        'face_photo_out',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
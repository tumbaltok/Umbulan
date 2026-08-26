<?php

namespace App\Models\Absen;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Kehadiran extends Model
{
    use HasFactory;

    protected $table = 'kehadirans';

    protected $fillable = [
        'user_id',
        'date',
        'shift_type',
        'scheduled_in',
        'scheduled_out',

        // Clock In
        'check_in',
        'check_in_lat',
        'check_in_long',
        'check_in_distance',
        'is_in_radius_check_in',
        'is_late',
        'is_face_verified_in',
        'reason_in',
        'evidence_in',

        // Clock Out
        'check_out',
        'check_out_lat',
        'check_out_long',
        'check_out_distance',
        'is_in_radius_check_out',
        'is_early_checkout',
        'is_face_verified_out',
        'reason_out',
        'evidence_out',

        // Status & Legacy
        'status',
        'reason_out_of_radius_in',
        'reason_checkout',
        'face_photo_in',
        'face_photo_out',
    ];

    protected $casts = [
        'date' => 'date',
        'is_in_radius_check_in' => 'boolean',
        'is_late' => 'boolean',
        'is_face_verified_in' => 'boolean',
        'is_in_radius_check_out' => 'boolean',
        'is_early_checkout' => 'boolean',
        'is_face_verified_out' => 'boolean',
        'check_in_distance' => 'float',
        'check_out_distance' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Mengambil alasan check-in dengan fallback ke kolom legacy.
     */
    public function getEffectiveReasonInAttribute(): ?string
    {
        return $this->reason_in ?: $this->reason_out_of_radius_in;
    }

    /**
     * Mengambil alasan check-out dengan fallback ke kolom legacy.
     */
    public function getEffectiveReasonOutAttribute(): ?string
    {
        return $this->reason_out ?: $this->reason_checkout;
    }

    /**
     * Mengambil URL bukti alasan masuk jika ada.
     */
    public function getEvidenceInUrlAttribute(): ?string
    {
        return $this->evidence_in ? Storage::disk('public')->url($this->evidence_in) : null;
    }

    /**
     * Mengambil URL bukti alasan pulang jika ada.
     */
    public function getEvidenceOutUrlAttribute(): ?string
    {
        return $this->evidence_out ? Storage::disk('public')->url($this->evidence_out) : null;
    }
}

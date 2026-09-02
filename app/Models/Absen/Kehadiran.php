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

    // Relasi ke user / karyawan pemilik absensi
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Mengambil alasan check-in dengan fallback ke kolom legacy
    public function getEffectiveReasonInAttribute(): ?string
    {
        return $this->reason_in ?: $this->reason_out_of_radius_in;
    }

    // Mengambil alasan check-out dengan fallback ke kolom legacy
    public function getEffectiveReasonOutAttribute(): ?string
    {
        return $this->reason_out ?: $this->reason_checkout;
    }

    // Mengambil URL bukti alasan masuk jika ada (fallback ke face_photo_in)
    public function getEvidenceInUrlAttribute(): ?string
    {
        $path = $this->evidence_in ?: $this->face_photo_in;
        return $path ? Storage::disk('public')->url($path) : null;
    }

    // Mengambil URL bukti alasan pulang jika ada (fallback ke face_photo_out)
    public function getEvidenceOutUrlAttribute(): ?string
    {
        $path = $this->evidence_out ?: $this->face_photo_out;
        return $path ? Storage::disk('public')->url($path) : null;
    }

    // Menghitung total durasi kerja dalam menit (aman untuk shift malam lintas hari)
    public function getWorkDurationMinutesAttribute(): ?int
    {
        if (empty($this->check_in) || empty($this->check_out)) {
            return null;
        }

        try {
            $dateStr = $this->date ? $this->date->format('Y-m-d') : now()->format('Y-m-d');
            $in = \Carbon\Carbon::parse($dateStr . ' ' . $this->check_in);
            $out = \Carbon\Carbon::parse($dateStr . ' ' . $this->check_out);

            if ($out->lt($in)) {
                $out->addDay();
            }

            return (int) $in->diffInMinutes($out);
        } catch (\Throwable $e) {
            return null;
        }
    }

    // Format durasi kerja agar mudah dibaca pengguna
    public function getWorkDurationFormattedAttribute(): string
    {
        if (empty($this->check_in)) {
            return '-';
        }

        if (empty($this->check_out)) {
            return 'Sedang Bekerja';
        }

        $minutes = $this->work_duration_minutes;
        if ($minutes === null) {
            return '-';
        }

        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;

        if ($hours > 0 && $mins > 0) {
            return "{$hours}j {$mins}m";
        } elseif ($hours > 0) {
            return "{$hours} Jam";
        } else {
            return "{$mins} Menit";
        }
    }

    // Memeriksa apakah terdapat pelanggaran radius geofencing saat masuk atau pulang
    public function getIsOutsideRadiusAttribute(): bool
    {
        $outsideIn = isset($this->is_in_radius_check_in) && !$this->is_in_radius_check_in;
        $outsideOut = isset($this->is_in_radius_check_out) && !$this->is_in_radius_check_out;
        return $outsideIn || $outsideOut;
    }
}


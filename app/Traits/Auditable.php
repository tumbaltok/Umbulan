<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait Auditable
{
    /**
     * Boot trait Auditable untuk mendaftarkan event listener Eloquent
     */
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            self::recordAuditLog($model, 'created');
        });

        static::updated(function ($model) {
            self::recordAuditLog($model, 'updated');
        });

        static::deleted(function ($model) {
            self::recordAuditLog($model, 'deleted');
        });
    }

    /**
     * Catat entri perubahan data ke tabel audit_logs
     */
    protected static function recordAuditLog($model, string $event): void
    {
        try {
            $ignored = array_merge(
                ['created_at', 'updated_at'],
                $model->getHidden() ?? [],
                ['password', 'remember_token', 'face_descriptor']
            );

            $oldValues = null;
            $newValues = null;

            if ($event === 'created') {
                $newValues = array_diff_key($model->getAttributes(), array_flip($ignored));
            } elseif ($event === 'updated') {
                $dirty = $model->getDirty();
                $dirty = array_diff_key($dirty, array_flip($ignored));

                if (empty($dirty)) {
                    return;
                }

                $oldValues = [];
                $newValues = [];

                foreach ($dirty as $key => $value) {
                    $oldValues[$key] = $model->getOriginal($key);
                    $newValues[$key] = $value;
                }
            } elseif ($event === 'deleted') {
                $oldValues = array_diff_key($model->getOriginal(), array_flip($ignored));
            }

            AuditLog::create([
                'user_id'        => Auth::id(),
                'auditable_type' => get_class($model),
                'auditable_id'   => $model->getKey(),
                'event'          => $event,
                'old_values'     => $oldValues,
                'new_values'     => $newValues,
                'ip_address'     => request()->ip() ?? null,
                'user_agent'     => request()->userAgent() ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::error("[Auditable Trait] Gagal mencatat audit log: " . $e->getMessage(), [
                'model' => get_class($model),
                'id'    => $model->getKey(),
                'event' => $event,
            ]);
        }
    }
}

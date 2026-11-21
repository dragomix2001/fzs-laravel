<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            $model->logAudit('create', null, $model->getAttributes());
        });

        static::updated(function ($model) {
            $changes = $model->getChanges();
            $oldValues = array_intersect_key($model->getOriginal(), $changes);

            $model->logAudit('update', $oldValues, $changes);
        });

        static::deleted(function ($model) {
            $model->logAudit('delete', $model->getAttributes(), null);
        });
    }

    protected function logAudit(string $action, ?array $oldValues, ?array $newValues)
    {
        $userId = null;

        if (Auth::check()) {
            $userId = Auth::id();
        }

        $tableName = $this->getTable();

        AuditLog::create([
            'user_id' => $userId,
            'action' => $action,
            'table_name' => $tableName,
            'record_id' => $this->getKey(),
            'old_values' => AuditLog::sanitizeValues($oldValues),
            'new_values' => AuditLog::sanitizeValues($newValues),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}

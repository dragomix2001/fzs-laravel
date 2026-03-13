<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditService
{
    public function log(Request $request = null, string $action, string $tableName, $recordId, array $oldValues = [], array $newValues = [])
    {
        $userId = null;
        $ipAddress = null;
        $userAgent = null;

        if (auth()->check()) {
            $userId = auth()->user()->id;
        }

        if ($request) {
            $ipAddress = $request->ip();
            $userAgent = $request->userAgent();
        }

        AuditLog::create([
            'user_id' => $userId,
            'action' => $action,
            'table_name' => $tableName,
            'record_id' => $recordId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }

    public function getLogs(string $tableName = null, int $userId = null, int $limit = 100)
    {
        $query = AuditLog::with('user');

        if ($tableName) {
            $query->where('table_name', $tableName);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->orderBy('created_at', 'desc')->limit($limit)->get();
    }
}

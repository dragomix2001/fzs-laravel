<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'table_name' => ['nullable', 'string', 'max:255'],
            'user_id' => ['nullable', 'integer', 'min:1'],
            'action' => ['nullable', 'string', 'max:50'],
            'record_id' => ['nullable', 'integer'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ]);

        $query = AuditLog::with('user');

        if (! empty($filters['table_name'])) {
            $query->where('table_name', $filters['table_name']);
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (! empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (! empty($filters['record_id'])) {
            $query->where('record_id', $filters['record_id']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $logs = $query->orderByDesc('created_at')->orderByDesc('id')->paginate(50)->withQueryString();

        $tables = AuditLog::query()->whereNotNull('table_name')->distinct()->orderBy('table_name')->pluck('table_name');
        $actions = AuditLog::query()->distinct()->orderBy('action')->pluck('action');
        $users = User::query()->select(['id', 'name'])->orderBy('name')->get();

        return view('audit.index', compact('logs', 'tables', 'actions', 'users', 'filters'));
    }
}

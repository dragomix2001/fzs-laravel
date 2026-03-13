<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user');

        if ($request->table_name) {
            $query->where('table_name', $request->table_name);
        }

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(50);
        
        $tables = AuditLog::distinct()->pluck('table_name');

        return view('audit.index', compact('logs', 'tables'));
    }
}

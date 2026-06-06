<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->can('audit_log.view'), 403);

        $filters = $request->only(['action', 'date_from', 'date_to', 'user_email']);

        $query = AuditLog::with('user')
            ->orderByDesc('occurred_at')
            ->orderByDesc('id');

        if (! empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('occurred_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('occurred_at', '<=', $filters['date_to']);
        }

        if (! empty($filters['user_email'])) {
            $query->where('user_email', 'like', '%'.$filters['user_email'].'%');
        }

        $logs = $query->paginate(50);

        return view('admin.audit-log', compact('logs', 'filters'));
    }
}

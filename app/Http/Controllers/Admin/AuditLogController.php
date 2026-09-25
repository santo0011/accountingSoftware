<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('permission:audit.view')];
    }

    public function index(Request $request): View
    {
        $activities = Activity::with('causer')
            ->when($request->filled('log'), fn ($q) => $q->where('log_name', $request->log))
            ->when($request->filled('q'), fn ($q) => $q->where('description', 'like', '%'.$request->q.'%'))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->to))
            ->latest()->paginate(30)->withQueryString();

        $logs = Activity::query()->distinct()->orderBy('log_name')->pluck('log_name');

        return view('admin.audit.index', compact('activities', 'logs'));
    }
}

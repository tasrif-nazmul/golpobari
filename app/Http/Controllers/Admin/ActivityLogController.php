<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ActivityLogController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('can:manage-settings')];
    }

    public function index(Request $request)
    {
        $logs = ActivityLog::with('admin:id,name')
            ->when($request->query('action'), fn ($q, $v) => $q->where('action', 'like', $v.'%'))
            ->when($request->query('admin'), fn ($q, $v) => $q->where('admin_id', (int) $v))
            ->when($request->query('q'), fn ($q, $v) => $q->where('description', 'like', "%{$v}%"))
            ->latest('id')->paginate(40)->withQueryString();

        return view('admin.activity.index', [
            'logs' => $logs,
            'admins' => Admin::orderBy('name')->pluck('name', 'id'),
            'actions' => ActivityLog::select('action')->distinct()->orderBy('action')->pluck('action'),
        ]);
    }
}

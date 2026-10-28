<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = ActivityLog::query()->with('user')->latest();

        if ($action = $request->query('action')) {
            $query->where('action', $action);
        }

        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($query) use ($search) {
                $query->where('description', 'like', '%'.$search.'%')
                    ->orWhere('subject_type', 'like', '%'.$search.'%');
            });
        }

        return view('activity.index', [
            'logs' => $query->paginate(25)->withQueryString(),
            'actions' => ActivityLog::query()->distinct()->orderBy('action')->pluck('action'),
            'filters' => $request->only(['action', 'q']),
        ]);
    }
}

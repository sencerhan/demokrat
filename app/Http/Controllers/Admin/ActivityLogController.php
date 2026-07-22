<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request): Response
    {
        $logName = $request->string('log_name')->toString();
        $causerId = $request->integer('causer_id') ?: null;

        $activities = Activity::query()
            ->with('causer:id,name')
            ->when($logName !== '', fn ($q) => $q->where('log_name', $logName))
            ->when($causerId !== null, fn ($q) => $q->where('causer_id', $causerId))
            ->latest()
            ->paginate(30)
            ->through(fn (Activity $activity) => [
                'id' => $activity->id,
                'log_name' => $activity->log_name,
                'description' => $activity->description,
                'event' => $activity->event,
                'subject_type' => $activity->subject_type,
                'subject_id' => $activity->subject_id,
                'causer' => $activity->causer ? [
                    'id' => $activity->causer->getKey(),
                    'name' => $activity->causer->name ?? null,
                ] : null,
                'properties' => $activity->properties,
                'created_at' => $activity->created_at?->toISOString(),
            ]);

        return Inertia::render('Admin/ActivityLog/Index', [
            'activities' => $activities,
            'filters' => [
                'log_name' => $logName,
                'causer_id' => $causerId,
            ],
        ]);
    }
}

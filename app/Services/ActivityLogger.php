<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

final class ActivityLogger
{
    public static function log(
        string $action,
        Model|string|null $subject,
        string $description,
        ?array $oldValues = null,
        ?array $newValues = null,
    ): ActivityLog {
        $subjectType = $subject instanceof Model ? $subject->getMorphClass() : $subject;
        $subjectId = $subject instanceof Model ? $subject->getKey() : null;

        return ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'created_at' => now(),
        ]);
    }
}

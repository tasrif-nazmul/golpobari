<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    public static function log(string $action, ?Model $subject = null, ?string $description = null, array $properties = []): void
    {
        ActivityLog::create([
            'admin_id' => auth('admin')->id(),
            'action' => $action,
            'subject_type' => $subject ? class_basename($subject) : null,
            'subject_id' => $subject?->getKey(),
            'description' => $description ? mb_substr($description, 0, 250) : null,
            'properties' => $properties ?: null,
            'ip_address' => request()?->ip(),
        ]);
    }
}

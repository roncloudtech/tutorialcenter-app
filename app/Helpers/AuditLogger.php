<?php

namespace App\Helpers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditLogger
{
    protected static array $cache = [];

    public static function log(
        Model $actor,
        string $action,
        ?Model $subject = null,
        ?string $description = null,
        array $changes = []
    ): void {
        $key = $actor::class . ':' . $actor->id . ':' . $action . ':' . ($subject?->id ?? 'null');

        // Prevent duplicate logs in the same request
        if (isset(self::$cache[$key])) {
            return;
        }

        self::$cache[$key] = true;

        AuditLog::create([
            'actor_id' => $actor->id,
            'actor_type' => get_class($actor),
            'action' => $action,
            'subject_id' => $subject?->id,
            'subject_type' => $subject ? get_class($subject) : null,
            'description' => $description,
            'changes' => empty($changes) ? null : $changes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}


// class AuditLogger
// {
//     /**
//      * Log an audit activity
//      */
//     public static function log(
//         Model $actor,
//         string $action,
//         ?Model $subject = null,
//         ?string $description = null,
//         array $changes = []
//     ): void {
//         AuditLog::create([
//             'actor_id'   => $actor->id,
//             'actor_type' => get_class($actor),

//             'action'     => $action,

//             'subject_id'   => $subject?->id,
//             'subject_type' => $subject ? get_class($subject) : null,

//             'description' => $description,
//             'changes'     => empty($changes) ? null : $changes,

//             'ip_address' => request()->ip(),
//             'user_agent' => request()->userAgent(),
//         ]);
//     }
// }

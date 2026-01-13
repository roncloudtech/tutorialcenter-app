<?php

namespace App\Listeners;

use App\Events\UserActivityEvent;
use App\Helpers\AuditLogger;
use App\Helpers\NotificationManager;
use App\Models\Student;

class LogUserActivity
{
    public function handle(UserActivityEvent $event): void
    {
        // 1️⃣ Log the audit
        AuditLogger::log(
            actor: $event->actor,
            action: $event->action,
            subject: $event->subject,
            description: $event->description,
            changes: $event->changes
        );

        // 2️⃣ Handle notifications
        // Always dispatch notifications using NotificationManager
        NotificationManager::dispatch(
            $event->actor,
            $event->action,
            $event->subject,
            $event->description
        );
    }
}


// namespace App\Listeners;

// use App\Events\UserActivityEvent;
// use App\Helpers\AuditLogger;

// class LogUserActivity
// {
//     /**
//      * Handle the event.
//      */
//     public function handle(UserActivityEvent $event): void
//     {
//         // Optional: debug to verify no duplicates
//         logger("AuditLog listener fired for: " . $event->actor->email);

//         AuditLogger::log(
//             actor: $event->actor,
//             action: $event->action,
//             subject: $event->subject,
//             description: $event->description,
//             changes: $event->changes
//         );
//     }
// }


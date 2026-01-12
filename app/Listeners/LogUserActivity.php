<?php

namespace App\Listeners;

use App\Events\UserActivityEvent;
use App\Helpers\AuditLogger;

class LogUserActivity
{
    /**
     * Handle the event.
     */
    public function handle(UserActivityEvent $event): void
    {
        // Optional: debug to verify no duplicates
        logger("AuditLog listener fired for: " . $event->actor->email);

        AuditLogger::log(
            actor: $event->actor,
            action: $event->action,
            subject: $event->subject,
            description: $event->description,
            changes: $event->changes
        );
    }
}

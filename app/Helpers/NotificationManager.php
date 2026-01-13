<?php

namespace App\Helpers;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Model;

class NotificationManager
{
    /**
     * Create a notification for any notifiable model.
     */
    public static function notify(
        Model $recipient,
        string $type,
        string $message,
        ?Model $subject = null
    ): Notification {
        return Notification::create([
            'notifiable_type' => get_class($recipient),
            'notifiable_id'   => $recipient->id,
            'type'            => $type,
            'message'         => $message,
            'subject_type'    => $subject ? get_class($subject) : null,
            'subject_id'      => $subject?->id,
        ]);
    }
}

<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Model;
use App\Models\Notification;

class NotificationManager
{
    public static function notify(
        Model $recipient,
        string $type,
        string $message,
        ?Model $subject = null
    ): ?Notification {

        $signature = self::signature(
            recipient: $recipient,
            type: $type,
            subject: $subject
        );

        // 🔒 DUPLICATE PROTECTION
        $exists = Notification::where('notifiable_type', get_class($recipient))
            ->where('notifiable_id', $recipient->id)
            ->where('signature', $signature)
            ->exists();

        if ($exists) {
            return null; // silently ignore duplicates
        }

        return Notification::create([
            'notifiable_type' => get_class($recipient),
            'notifiable_id'   => $recipient->id,
            'type'            => $type,
            'message'         => $message,
            'subject_type'    => $subject ? get_class($subject) : null,
            'subject_id'      => $subject?->id,
            'signature'       => $signature,
        ]);
    }

    protected static function signature(
        Model $recipient,
        string $type,
        ?Model $subject = null
    ): string {
        return sha1(
            get_class($recipient) . '|' .
            $recipient->id . '|' .
            $type . '|' .
            ($subject ? get_class($subject) . ':' . $subject->id : 'none')
        );
    }
}


// use App\Models\Notification;
// use Illuminate\Database\Eloquent\Model;

// class NotificationManager
// {
//     /**
//      * Create a notification for any notifiable model.
//      */
//     public static function notify(
//         Model $recipient,
//         string $type,
//         string $message,
//         ?Model $subject = null
//     ): Notification {
//         return Notification::create([
//             'notifiable_type' => get_class($recipient),
//             'notifiable_id'   => $recipient->id,
//             'type'            => $type,
//             'message'         => $message,
//             'subject_type'    => $subject ? get_class($subject) : null,
//             'subject_id'      => $subject?->id,
//         ]);
//     }
// }

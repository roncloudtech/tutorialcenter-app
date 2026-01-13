<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Model;
use App\Models\Staff;
use App\Models\Notification;

class NotificationManager
{
    public static function dispatch(
        Model $actor,
        string $action,
        ?Model $subject = null,
        ?string $message = null
    ): void {

        $recipients = collect();

        // Always notify the actor
        $recipients->push($actor);

        // Student-specific logic
        if ($actor instanceof \App\Models\Student) {

            // Assigned staff (if exists)
            if (method_exists($actor, 'assignedStaff') && $actor->assignedStaff) {
                $recipients->push($actor->assignedStaff);
            }

            // Guardians (if any)
            if (method_exists($actor, 'guardians') && $actor->guardians) {
                foreach ($actor->guardians as $guardian) {
                    $recipients->push($guardian);
                }
            }

            // Fallback: admin staff if no assigned staff
            if (!$actor->assignedStaff) {
                Staff::where('staff_role', 'admin')->get()->each(function ($admin) use ($recipients) {
                    $recipients->push($admin);
                });
            }
        }

        // Remove duplicates
        $recipients = $recipients->unique(fn ($user) => get_class($user).':'.$user->id);

        // Notify each recipient
        foreach ($recipients as $recipient) {
            NotificationManager::notify(
                recipient: $recipient,
                type: $action,
                message: $message ?? "Action: $action",
                subject: $subject
            );
        }
    }

    public static function notify(
        Model $recipient,
        string $type,
        string $message,
        ?Model $subject = null
    ) {
        $signature = sha1(get_class($recipient) . $recipient->id . $type . ($subject?->id ?? 'none'));

        // Avoid duplicates
        if (Notification::where('signature', $signature)->exists()) {
            return null;
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
}


// namespace App\Helpers;

// use Illuminate\Database\Eloquent\Model;
// use App\Models\Notification;

// class NotificationManager
// {
//     public static function notify(
//         Model $recipient,
//         string $type,
//         string $message,
//         ?Model $subject = null
//     ): ?Notification {

//         $signature = self::signature(
//             recipient: $recipient,
//             type: $type,
//             subject: $subject
//         );

//         // 🔒 DUPLICATE PROTECTION
//         $exists = Notification::where('notifiable_type', get_class($recipient))
//             ->where('notifiable_id', $recipient->id)
//             ->where('signature', $signature)
//             ->exists();

//         if ($exists) {
//             return null; // silently ignore duplicates
//         }

//         return Notification::create([
//             'notifiable_type' => get_class($recipient),
//             'notifiable_id'   => $recipient->id,
//             'type'            => $type,
//             'message'         => $message,
//             'subject_type'    => $subject ? get_class($subject) : null,
//             'subject_id'      => $subject?->id,
//             'signature'       => $signature,
//         ]);
//     }

//     protected static function signature(
//         Model $recipient,
//         string $type,
//         ?Model $subject = null
//     ): string {
//         return sha1(
//             get_class($recipient) . '|' .
//             $recipient->id . '|' .
//             $type . '|' .
//             ($subject ? get_class($subject) . ':' . $subject->id : 'none')
//         );
//     }
// }


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

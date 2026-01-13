<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'notifiable_type',
        'notifiable_id',
        'subject_type',
        'subject_id',
        'type',
        'message',
        'is_read',
    ];

    public function notifiable()
    {
        return $this->morphTo();
    }

    public function subject()
    {
        return $this->morphTo();
    }
}

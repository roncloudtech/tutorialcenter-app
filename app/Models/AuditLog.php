<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditLog extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'action',
        'description',
        'ip_address',
        'user_agent',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    /**
     * POLYMORPHIC ACTOR
     * Who performed the action?
     */
    public function actor()
    {
        return $this->morphTo();
    }

    /**
     * POLYMORPHIC SUBJECT
     * What was affected?
     */
    public function subject()
    {
        return $this->morphTo();
    }
}

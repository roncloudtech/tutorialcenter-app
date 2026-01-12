<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Staff extends Authenticatable
{
    use HasApiTokens, Notifiable;
    use HasFactory, SoftDeletes;
    protected $table = 'staffs';

    protected $fillable = [
        'staff_id',
        'firstname',
        'lastname',
        'email',
        'phone',
        'password',
        'gender',
        'staff_role',
        'profile_picture',
        'date_of_birth',
        'email_verified_at',
        'phone_verified_at',
        'verification_code',
        'verified',
        'status',
        'home_address',
        'indected_by'
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'date_of_birth' => 'date'
    ];

    /**
     * Automatically hash password before saving
     */
    public function setPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['password'] = bcrypt($value);
        }
    }

    public function auditLogs() {
        return $this->morphMany(AuditLog::class, 'actor');
    }
}

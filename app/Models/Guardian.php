<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Guardian extends Authenticatable
{
    use HasApiTokens, Notifiable;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'phone',
        'password',
        'gender',
        'profile_picture',
        'date_of_birth',
        'email_verified_at',
        'phone_verified_at',
        'verification_code',
        'location',
        'home_address',
        'students_ids',
        'provider',
        'google_id',
    ];
    
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'students_ids' => 'array',
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

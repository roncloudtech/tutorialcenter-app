<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Student extends Authenticatable
{
    use SoftDeletes, HasApiTokens;

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
        'department',
        'guardians_ids',
        'provider',
        'google_id',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'guardians_ids' => 'array',
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

    protected $appends = ['profile_picture_url'];

    public function getProfilePictureUrlAttribute () {
        return $this->profile_picture ? asset('storage/' . $this->profile_picture) : null;
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function auditLogs() {
        return $this->morphMany(AuditLog::class, 'actor');
    }
}

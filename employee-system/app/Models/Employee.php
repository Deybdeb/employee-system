<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Employee extends Authenticatable
{
    use HasFactory, HasUuids, Notifiable;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'gender',
        'marital_status',
        'nationality_id',
        'other_id',
        'drivers_license_number',
        'license_expiry_date',
        'personal_email',
        'work_email',
        'mobile_phone',
        'home_phone',
        'work_phone',
        'password',
        'mocked_time',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'password' => 'hashed',
        'is_2fa_enabled' => 'boolean',
        'date_of_birth' => 'date:Y-m-d',
        'license_expiry_date' => 'date:Y-m-d',
        'mocked_time' => 'datetime',
    ];

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    /** gets user record based on email */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'email', 'personal_email');
    }
}

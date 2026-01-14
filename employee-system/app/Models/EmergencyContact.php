<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmergencyContact extends Model
{
    use HasFactory;

    protected $table = 'emergency_contacts';

    protected $fillable = [
        'employee_id',
        'name',
        'relationship',
        'home_phone',
        'mobile_phone',
        'work_phone',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}

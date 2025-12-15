<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'type',
        'street_1',
        'street_2',
        'city',
        'state_province',
        'postal_code',
        'country_id',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}

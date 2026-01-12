<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Timesheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'week_start_date',
        'week_end_date',
        'status',
        'rejection_reason',
        'approved_by',
        'submitted_at',
        'approved_at',
    ];

    protected $casts = [
        'week_start_date' => 'date',
        'week_end_date' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(TimesheetEntry::class);
    }

    public function getTotalHoursAttribute(): float
    {
        return $this->entries->sum('hours');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Not Submitted',
            'submitted' => 'Pending Approval',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => 'Unknown',
        };
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MoveOutRequest extends Model
{
    protected $fillable = [
        'student_id',
        'booking_id',
        'intended_move_out_date',
        'reason',
        'status',
        'staff_note',
        'acknowledged_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'intended_move_out_date' => 'date',
            'acknowledged_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['pending', 'acknowledged'], true);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Pending Review',
            'acknowledged' => 'Acknowledged',
            'completed' => 'Move-Out Complete',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'pending' => 'bg-warning-subtle text-warning-emphasis',
            'acknowledged' => 'bg-info-subtle text-info-emphasis',
            'completed' => 'bg-success-subtle text-success-emphasis',
            'cancelled' => 'bg-secondary-subtle text-secondary-emphasis',
            default => 'bg-light text-dark',
        };
    }
}

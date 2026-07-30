<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    protected $fillable = [
        'student_id',
        'room_id',
        'date_booked',
        'status',
        'rejection_reason',
        'cancellation_note',
    ];

    protected function casts(): array
    {
        return [
            'date_booked' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function moveOutRequests(): HasMany
    {
        return $this->hasMany(MoveOutRequest::class);
    }

    public function isCancellableByStudent(): bool
    {
        return in_array($this->status, ['pending', 'approved_by_warden', 'awaiting_payment'], true);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Pending Approval',
            'approved_by_warden' => 'Approved — Awaiting Payment',
            'awaiting_payment' => 'Awaiting Payment',
            'confirmed' => 'Confirmed',
            'cancelled' => 'Cancelled',
            'rejected' => 'Rejected',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'pending' => 'bg-warning-subtle text-warning-emphasis',
            'approved_by_warden', 'awaiting_payment' => 'bg-info-subtle text-info-emphasis',
            'confirmed' => 'bg-success-subtle text-success-emphasis',
            'cancelled' => 'bg-secondary-subtle text-secondary-emphasis',
            'rejected' => 'bg-danger-subtle text-danger-emphasis',
            default => 'bg-light text-dark',
        };
    }
}

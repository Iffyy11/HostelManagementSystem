<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceRequest extends Model
{
    protected $fillable = [
        'student_id',
        'room_id',
        'description',
        'category',
        'status',
        'assigned_caretaker_id',
        'resolution_notes',
        'photo_path',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
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

    public function assignedCaretaker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_caretaker_id');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'open' => 'Open',
            'in_progress' => 'In Progress',
            'resolved' => 'Resolved',
            default => ucfirst($this->status),
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'open' => 'bg-danger-subtle text-danger-emphasis',
            'in_progress' => 'bg-warning-subtle text-warning-emphasis',
            'resolved' => 'bg-success-subtle text-success-emphasis',
            default => 'bg-light text-dark',
        };
    }
}

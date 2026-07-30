<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $fillable = [
        'room_number',
        'block_name',
        'capacity',
        'current_occupancy',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'current_occupancy' => 'integer',
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function hasAvailableSpace(): bool
    {
        return $this->status === 'available'
            && $this->current_occupancy < $this->capacity;
    }

    public function availableBeds(): int
    {
        return max(0, $this->capacity - $this->current_occupancy);
    }

    public function displayName(): string
    {
        return "{$this->block_name} — Room {$this->room_number}";
    }
}

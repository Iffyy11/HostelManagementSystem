<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = [
        'user_id',
        'student_id_number',
        'programme',
        'phone',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function moveOutRequests(): HasMany
    {
        return $this->hasMany(MoveOutRequest::class);
    }

    public function confirmedBooking(): ?Booking
    {
        return $this->bookings()
            ->where('status', 'confirmed')
            ->latest()
            ->first();
    }
}

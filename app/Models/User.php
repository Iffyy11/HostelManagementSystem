<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function warden(): HasOne
    {
        return $this->hasOne(Warden::class);
    }

    public function assignedMaintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class, 'assigned_caretaker_id');
    }

    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }

    public function dashboardRoute(): string
    {
        if ($this->hasRole('admin')) {
            return route('admin.dashboard');
        }
        if ($this->hasRole('warden')) {
            return route('warden.dashboard');
        }
        if ($this->hasRole('caretaker')) {
            return route('caretaker.dashboard');
        }

        return route('student.dashboard');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warden extends Model
{
    protected $fillable = [
        'user_id',
        'block_assigned',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class, 'block_name', 'block_assigned');
    }
}

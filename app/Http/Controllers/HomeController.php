<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $stats = [
            'blocks' => 3,
            'rooms' => 21,
            'available_beds' => 28,
        ];

        if (Schema::hasTable('rooms')) {
            $stats = [
                'blocks' => Room::distinct('block_name')->count('block_name') ?: 3,
                'rooms' => Room::count() ?: 21,
                'available_beds' => Room::where('status', '!=', 'under_maintenance')
                    ->get()
                    ->sum(fn ($r) => max(0, $r->capacity - $r->current_occupancy)) ?: 28,
            ];
        }

        return view('welcome', compact('stats'));
    }
}

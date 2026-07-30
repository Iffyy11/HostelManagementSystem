<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Room;
use App\Models\Student;
use App\Models\User;
use App\Models\Warden;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class HostelSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'System Admin',
            'email' => 'admin@usiu.ac.ke',
            'password' => Hash::make('password'),
            'phone' => '254700000001',
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        $wardenUsers = [
            ['name' => 'Grace Wanjiku', 'email' => 'warden.a@usiu.ac.ke', 'block' => 'Block A', 'phone' => '254700000002'],
            ['name' => 'Peter Ochieng', 'email' => 'warden.b@usiu.ac.ke', 'block' => 'Block B', 'phone' => '254700000003'],
        ];

        foreach ($wardenUsers as $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'phone' => $data['phone'],
                'is_active' => true,
            ]);
            $user->assignRole('warden');
            Warden::create(['user_id' => $user->id, 'block_assigned' => $data['block']]);
        }

        $caretakers = [
            ['name' => 'James Kamau', 'email' => 'caretaker1@usiu.ac.ke', 'phone' => '254700000004'],
            ['name' => 'Mary Akinyi', 'email' => 'caretaker2@usiu.ac.ke', 'phone' => '254700000005'],
        ];

        foreach ($caretakers as $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'phone' => $data['phone'],
                'is_active' => true,
            ]);
            $user->assignRole('caretaker');
        }

        $blocks = ['Block A', 'Block B', 'Block C'];
        $rooms = [];

        foreach ($blocks as $block) {
            for ($i = 1; $i <= 7; $i++) {
                $capacity = $i % 3 === 0 ? 4 : 2;
                $occupancy = $i <= 2 ? 1 : 0;
                $status = $i === 7 ? 'under_maintenance' : ($occupancy >= $capacity ? 'full' : 'available');

                $rooms[] = Room::create([
                    'room_number' => str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                    'block_name' => $block,
                    'capacity' => $capacity,
                    'current_occupancy' => $occupancy,
                    'status' => $status,
                ]);
            }
        }

        $programmes = ['BSc Computer Science', 'BBA Finance', 'BA International Relations', 'BSc Applied Technology'];
        $students = [];

        for ($i = 1; $i <= 15; $i++) {
            $user = User::create([
                'name' => "Student {$i}",
                'email' => "student{$i}@students.usiu.ac.ke",
                'password' => Hash::make('password'),
                'phone' => '2547'.str_pad((string) (10000000 + $i), 8, '0', STR_PAD_LEFT),
                'is_active' => true,
            ]);
            $user->assignRole('student');

            $students[] = Student::create([
                'user_id' => $user->id,
                'student_id_number' => 'USIU'.(2024000 + $i),
                'programme' => $programmes[$i % count($programmes)],
                'phone' => $user->phone,
            ]);
        }

        $confirmedBooking = Booking::create([
            'student_id' => $students[0]->id,
            'room_id' => $rooms[0]->id,
            'date_booked' => now()->subDays(5),
            'status' => 'confirmed',
        ]);

        Payment::create([
            'booking_id' => $confirmedBooking->id,
            'amount' => config('hostel.booking_fee'),
            'mpesa_receipt_number' => 'QGH123ABC',
            'phone_number' => $students[0]->phone,
            'status' => 'confirmed',
            'transaction_date' => now()->subDays(5),
        ]);

        Booking::create([
            'student_id' => $students[1]->id,
            'room_id' => $rooms[2]->id,
            'date_booked' => now()->subDay(),
            'status' => 'pending',
        ]);

        MaintenanceRequest::create([
            'student_id' => $students[0]->id,
            'room_id' => $rooms[0]->id,
            'description' => 'Leaking tap in the bathroom.',
            'category' => 'plumbing',
            'status' => 'open',
        ]);
    }
}

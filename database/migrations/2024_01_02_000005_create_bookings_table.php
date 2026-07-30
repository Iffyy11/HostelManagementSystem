<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->date('date_booked');
            $table->enum('status', [
                'pending',
                'approved_by_warden',
                'awaiting_payment',
                'confirmed',
                'cancelled',
                'rejected',
            ])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->text('cancellation_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_number');
            $table->string('block_name');
            $table->unsignedTinyInteger('capacity');
            $table->unsignedTinyInteger('current_occupancy')->default(0);
            $table->enum('status', ['available', 'full', 'under_maintenance'])->default('available');
            $table->timestamps();
            $table->unique(['room_number', 'block_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};

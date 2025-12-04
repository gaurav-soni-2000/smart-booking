<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->date('date');          // booking date
            $table->time('start_time');    // appointment start time
            $table->time('end_time');      // appointment end time
            $table->string('client_email');
            $table->string('client_name')->nullable();
            $table->enum('status', ['booked','cancelled','completed'])->default('booked');
            $table->timestamps();

            $table->unique(['date','start_time','service_id'],'unique_booking_per_service');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};

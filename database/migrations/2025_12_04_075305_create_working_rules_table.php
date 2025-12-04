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
        Schema::create('working_rules', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('weekday'); // 0=Sunday .. 6=Saturday
            $table->time('start_time'); // e.g. '09:00:00'
            $table->time('end_time');   // e.g. '17:00:00'
            $table->integer('slot_interval')->default(30); // default slot interval minutes
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('working_rules');
    }
};

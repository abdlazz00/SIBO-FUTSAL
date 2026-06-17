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
        Schema::create('courts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type', 20); // 'indoor', 'outdoor'
            $table->decimal('price', 12, 2);
            $table->integer('slot_duration'); // in minutes (default 60)
            $table->time('open_time');
            $table->time('close_time');
            $table->string('status', 20)->default('active'); // 'active', 'inactive', 'maintenance'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courts');
    }
};

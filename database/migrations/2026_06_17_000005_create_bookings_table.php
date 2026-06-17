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
            $table->string('booking_number', 20)->unique();
            $table->foreignId('court_id')->constrained('courts');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('customer_name');
            $table->string('customer_phone', 20);
            $table->string('customer_email')->nullable();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('total_price', 12, 2);
            $table->string('status', 20)->default('confirmed'); // 'confirmed', 'completed', 'cancelled'
            $table->text('cancel_reason')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users');
            $table->boolean('is_manual')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['court_id', 'date'], 'idx_bookings_court_date');
            $table->index('booking_number', 'idx_bookings_number');
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

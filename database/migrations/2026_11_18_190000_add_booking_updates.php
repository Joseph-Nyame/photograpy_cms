<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('bookings', 'booking_type')) {
            Schema::table('bookings', function (Blueprint $table): void {
                $table->string('booking_type')->default('standard')->index();
            });
        }

        if (! Schema::hasColumn('bookings', 'budget')) {
            Schema::table('bookings', function (Blueprint $table): void {
                $table->decimal('budget', 10, 2)->nullable();
            });
        }

        if (! Schema::hasColumn('bookings', 'payment_status')) {
            Schema::table('bookings', function (Blueprint $table): void {
                $table->string('payment_status')->default('unpaid')->index();
            });
        }

        Schema::create('booking_activities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('from_value')->nullable();
            $table->string('to_value')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_activities');

        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropColumn(['booking_type', 'budget', 'payment_status']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('bookings', 'budget')) {
            Schema::table('bookings', function (Blueprint $table): void {
                if (! Schema::hasColumn('bookings', 'total_cost')) {
                    $table->decimal('total_cost', 12, 2)->nullable()->after('budget');
                }

                if (! Schema::hasColumn('bookings', 'deposit_amount')) {
                    $table->decimal('deposit_amount', 12, 2)->nullable()->after('total_cost');
                }
            });
        }

        if (Schema::hasTable('settings')) {
            DB::table('settings')->where('key', 'booking.deposit_amount')->delete();
            DB::table('settings')->updateOrInsert(
                ['key' => 'booking.deposit_rate'],
                ['value' => '30'],
            );
        }
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropColumn(['total_cost', 'deposit_amount']);
        });
    }
};

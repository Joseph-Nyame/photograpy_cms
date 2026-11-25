<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('services', 'currency')) {
            Schema::table('services', function (Blueprint $table): void {
                $table->string('currency', 3)->default('GHS')->index();
            });
        }

        if (! Schema::hasColumn('bookings', 'currency')) {
            Schema::table('bookings', function (Blueprint $table): void {
                $table->string('currency', 3)->default('GHS')->index();
            });
        }

        if (Schema::hasTable('settings')) {
            DB::table('settings')->where('key', 'pricing.currency')->delete();
        }
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            $table->dropColumn('currency');
        });

        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropColumn('currency');
        });
    }
};

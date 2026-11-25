<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const TABLES = [
        'clients',
        'services',
        'bookings',
        'blocked_dates',
        'campaigns',
        'contact_messages',
        'email_messages',
        'client_galleries',
        'gallery_images',
        'portfolio_images',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            if (! Schema::hasColumn($table, 'uuid')) {
                Schema::table($table, function (Blueprint $table): void {
                    $table->uuid('uuid')->nullable()->unique();
                });
            }
        }

        foreach (self::TABLES as $table) {
            DB::table($table)
                ->whereNull('uuid')
                ->chunkById(500, function ($rows) use ($table): void {
                    foreach ($rows as $row) {
                        DB::table($table)
                            ->where('id', $row->id)
                            ->update(['uuid' => (string) Str::uuid()]);
                    }
                });
        }
    }

    public function down(): void
    {
        foreach (array_reverse(self::TABLES) as $table) {
            Schema::table($table, function (Blueprint $table): void {
                $table->dropColumn('uuid');
            });
        }
    }
};

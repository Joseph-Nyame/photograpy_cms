<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_packages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('sort')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('service_package_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('service_package_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->decimal('value', 10, 2)->nullable();
            $table->unsignedInteger('sort')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_package_items');
        Schema::dropIfExists('service_packages');
    }
};

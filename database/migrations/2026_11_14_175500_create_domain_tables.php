<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->text('notes')->nullable();
            $table->string('source')->default('manual')->index();
            $table->boolean('subscribed')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price_from', 10, 2)->nullable();
            $table->string('duration')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->unsignedInteger('sort')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->date('event_date')->index();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('location')->nullable();
            $table->text('message')->nullable();
            $table->string('status')->default('pending')->index();
            $table->text('decline_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('blocked_dates', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->string('reason')->nullable();
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedBigInteger('cover_image_id')->nullable();
            $table->unsignedInteger('sort')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('portfolio_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('alt')->nullable();
            $table->unsignedInteger('sort')->default(0)->index();
            $table->boolean('featured')->default(false)->index();
            $table->timestamps();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->foreign('cover_image_id')->references('id')->on('portfolio_images')->nullOnDelete();
        });

        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('message');
            $table->timestamp('read_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('client_galleries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('token', 64)->unique();
            $table->string('passcode_hash')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->boolean('allow_download')->default(true);
            $table->string('status')->default('active')->index();
            $table->timestamps();
        });

        Schema::create('gallery_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_id')->constrained('client_galleries')->cascadeOnDelete();
            $table->string('original_path');
            $table->string('preview_path')->nullable();
            $table->string('thumb_path')->nullable();
            $table->unsignedInteger('sort')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('favourites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_id')->constrained('client_galleries')->cascadeOnDelete();
            $table->foreignId('gallery_image_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['gallery_id', 'gallery_image_id']);
        });

        Schema::create('gallery_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_id')->constrained('client_galleries')->cascadeOnDelete();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('action')->index();
            $table->timestamps();
        });

        Schema::create('email_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('subject');
            $table->longText('body');
            $table->timestamp('sent_at')->nullable();
            $table->string('status')->default('queued')->index();
            $table->timestamps();
        });

        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->longText('body');
            $table->string('status')->default('draft')->index();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('campaign_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending')->index();
            $table->timestamps();
            $table->unique(['campaign_id', 'client_id']);
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->timestamps();
        });

        Schema::create('availability_slots', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('day_of_week')->index();
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('active')->default(true)->index();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
            $table->unique(['day_of_week', 'start_time', 'end_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('availability_slots');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('campaign_recipients');
        Schema::dropIfExists('campaigns');
        Schema::dropIfExists('email_messages');
        Schema::dropIfExists('gallery_access_logs');
        Schema::dropIfExists('favourites');
        Schema::dropIfExists('gallery_images');
        Schema::dropIfExists('client_galleries');
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('portfolio_images');
        Schema::dropIfExists('blocked_dates');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('services');
        Schema::dropIfExists('clients');
    }
};

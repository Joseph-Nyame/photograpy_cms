<?php

use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\CalendarController as AdminCalendarController;
use App\Http\Controllers\Admin\CampaignController as AdminCampaignController;
use App\Http\Controllers\Admin\ClientController as AdminClientController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EmailController as AdminEmailController;
use App\Http\Controllers\Admin\GalleryController as AdminGalleryController;
use App\Http\Controllers\Admin\LocationController as AdminLocationController;
use App\Http\Controllers\Admin\MessageController as AdminMessageController;
use App\Http\Controllers\Admin\PortfolioController as AdminPortfolioController;
use App\Http\Controllers\Admin\SearchController as AdminSearchController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ClientGalleryController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\GalleriesController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ServicesController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::get('/', HomeController::class)->name('home');
Route::inertia('/about', 'About')->name('about');
Route::get('/services', ServicesController::class)->name('services');
Route::get('/booking', [BookingController::class, 'create'])->name('booking');
Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
Route::get('/contact', [ContactController::class, 'create'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

Route::get('/galleries', [GalleriesController::class, 'index'])->name('galleries');
Route::get('/galleries/{category:slug}', [GalleriesController::class, 'show'])
    ->name('galleries.category');

Route::middleware('gallery.active')->group(function () {
    Route::get('/g/{gallery:token}', [ClientGalleryController::class, 'show'])->name('client-gallery');
    Route::post('/g/{gallery:token}/unlock', [ClientGalleryController::class, 'unlock'])
        ->middleware('throttle:gallery-unlock')
        ->name('client-gallery.unlock');
    Route::post('/g/{gallery:token}/lock', [ClientGalleryController::class, 'lock'])->name('client-gallery.lock');
    Route::post('/g/{gallery:token}/favourites/{image}', [ClientGalleryController::class, 'toggleFavourite'])
        ->name('client-gallery.favourite');
    Route::get('/g/{gallery:token}/images/{image}/preview', [ClientGalleryController::class, 'preview'])
        ->name('client-gallery.preview');
    Route::get('/g/{gallery:token}/images/{image}/download', [ClientGalleryController::class, 'download'])
        ->middleware('signed')
        ->name('client-gallery.download');
});

Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings');
    Route::post('/bookings', [AdminBookingController::class, 'store'])->name('bookings.store');

    Route::put('/bookings/{booking}', [AdminBookingController::class, 'update'])->name('bookings.update');
    Route::post('/bookings/{booking}/confirm', [AdminBookingController::class, 'confirm'])->name('bookings.confirm');
    Route::post('/bookings/{booking}/decline', [AdminBookingController::class, 'decline'])->name('bookings.decline');
    Route::post('/bookings/{booking}/reopen', [AdminBookingController::class, 'reopen'])->name('bookings.reopen');
    Route::get('/calendar', [AdminCalendarController::class, 'index'])->name('calendar');
    Route::post('/calendar/block', [AdminCalendarController::class, 'block'])->name('calendar.block');
    Route::delete('/calendar/blocked/{blockedDate}', [AdminCalendarController::class, 'unblock'])->name('calendar.unblock');
    Route::post('/calendar/availability', [AdminCalendarController::class, 'availability'])->name('calendar.availability');
    Route::get('/clients', [AdminClientController::class, 'index'])->name('clients');
    Route::post('/clients', [AdminClientController::class, 'store'])->name('clients.store');
    Route::post('/clients/{client}/toggle-subscription', [AdminClientController::class, 'toggleSubscription'])->name('clients.toggle-subscription');
    Route::get('/messages', [AdminMessageController::class, 'index'])->name('messages');
    Route::post('/messages/read-all', [AdminMessageController::class, 'readAll'])->name('messages.read-all');
    Route::post('/messages/{message}/read', [AdminMessageController::class, 'read'])->name('messages.read');
    Route::post('/messages/{message}/unread', [AdminMessageController::class, 'unread'])->name('messages.unread');
    Route::post('/messages/{message}/reply', [AdminMessageController::class, 'reply'])->name('messages.reply');
    Route::get('/services', [AdminServiceController::class, 'index'])->name('services');
    Route::post('/services', [AdminServiceController::class, 'store'])->name('services.store');
    Route::put('/services/{service}', [AdminServiceController::class, 'update'])->name('services.update');
    Route::post('/services/{service}/duplicate', [AdminServiceController::class, 'duplicate'])->name('services.duplicate');
    Route::post('/services/{service}/toggle', [AdminServiceController::class, 'toggle'])->name('services.toggle');
    Route::get('/locations', [AdminLocationController::class, 'index'])->name('locations');
    Route::post('/locations', [AdminLocationController::class, 'store'])->name('locations.store');
    Route::put('/locations/{location}', [AdminLocationController::class, 'update'])->name('locations.update');
    Route::post('/locations/{location}/toggle', [AdminLocationController::class, 'toggle'])->name('locations.toggle');
    Route::get('/portfolio', [AdminPortfolioController::class, 'index'])->name('portfolio');
    Route::post('/portfolio', [AdminPortfolioController::class, 'store'])->name('portfolio.store');
    Route::post('/portfolio/categories', [AdminPortfolioController::class, 'storeCategory'])->name('portfolio.categories.store');
    Route::put('/portfolio/categories/{category}', [AdminPortfolioController::class, 'updateCategory'])->name('portfolio.categories.update');
    Route::delete('/portfolio/categories/{category}', [AdminPortfolioController::class, 'destroyCategory'])->name('portfolio.categories.destroy');
    Route::post('/portfolio/{image}/reorder', [AdminPortfolioController::class, 'reorder'])->name('portfolio.reorder');
    Route::post('/portfolio/{image}/toggle-featured', [AdminPortfolioController::class, 'toggleFeatured'])->name('portfolio.toggle-featured');
    Route::post('/portfolio/{image}/set-cover', [AdminPortfolioController::class, 'setCover'])->name('portfolio.set-cover');
    Route::delete('/portfolio/{image}', [AdminPortfolioController::class, 'destroy'])->name('portfolio.destroy');
    Route::get('/galleries', [AdminGalleryController::class, 'index'])->name('galleries');
    Route::post('/galleries', [AdminGalleryController::class, 'store'])->name('galleries.store');
    Route::get('/galleries/{gallery}', [AdminGalleryController::class, 'show'])->name('galleries.show');
    Route::post('/galleries/{gallery}/images', [AdminGalleryController::class, 'upload'])->name('galleries.images.upload');
    Route::delete('/galleries/images/{image}', [AdminGalleryController::class, 'destroyImage'])->name('galleries.images.destroy');
    Route::post('/galleries/{gallery}/toggle-download', [AdminGalleryController::class, 'toggleDownload'])->name('galleries.toggle-download');
    Route::delete('/galleries/{gallery}', [AdminGalleryController::class, 'destroy'])->name('galleries.destroy');
    Route::get('/campaigns', [AdminCampaignController::class, 'index'])->name('campaigns');
    Route::post('/campaigns', [AdminCampaignController::class, 'store'])->name('campaigns.store');
    Route::post('/campaigns/{campaign}/send', [AdminCampaignController::class, 'send'])->name('campaigns.send');
    Route::post('/campaigns/{campaign}/duplicate', [AdminCampaignController::class, 'duplicate'])->name('campaigns.duplicate');
    Route::delete('/campaigns/{campaign}', [AdminCampaignController::class, 'destroy'])->name('campaigns.destroy');
    Route::get('/emails', [AdminEmailController::class, 'index'])->name('emails');
    Route::post('/emails', [AdminEmailController::class, 'store'])->name('emails.store');
    Route::post('/emails/{email}/resend', [AdminEmailController::class, 'resend'])->name('emails.resend');
    Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings');
    Route::put('/settings', [AdminSettingsController::class, 'update'])->name('settings.update');
    Route::get('/search', AdminSearchController::class)->name('search');
    Route::get('/{path}', fn () => redirect()->route('admin.dashboard'))->where('path', '.*');
});

<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\PortfolioImage;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_home_page_uses_database_catalog_and_settings(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Home')
                ->has('categories', 5)
                ->has('services', 3)
                ->where('services.0.name', 'Portrait session')
                ->where('services.0.price', 800)
                ->where('services.0.currency', 'GHS')
                ->where('settings.studio.name', 'Lumen Studio')
                ->where('settings.socials.0.label', 'Instagram'));
    }

    public function test_services_page_uses_active_services_only(): void
    {
        $this->get('/services')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Services')
                ->has('services', 3)
                ->where('services.1.name', 'Event coverage'));
    }

    public function test_services_page_exposes_pricing_packages(): void
    {
        $this->get('/services')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Services')
                ->has('services.0.packages', 2)
                ->where('services.0.currency', 'GHS')
                ->where('services.0.packages.0.name', 'Essential')
                ->where('services.0.packages.0.items.0.label', '60-minute shoot')
                ->where('services.0.packages.0.items.0.value', 800));
    }

    public function test_services_page_falls_back_when_a_service_has_no_packages(): void
    {
        $service = Service::query()->where('slug', 'portrait-session')->firstOrFail();
        $service->packages()->delete();

        $this->get('/services')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Services')
                ->has('services.0.packages', 1)
                ->where('services.0.packages.0.name', 'Standard')
                ->where('services.0.packages.0.items.0.label', 'Session')
                ->where('services.0.packages.0.items.0.value', 800));
    }

    public function test_booking_page_receives_services_and_calendar(): void
    {
        $this->get('/booking')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Booking')
                ->has('services', 3)
                ->where('services.0.deposit', 240)
                ->where('services.0.currency', 'GHS')
                ->has('services.0.packages', 2)
                ->has('locations', 3)
                ->where('locations.0.name', 'Accra, Ghana')
                ->where('locations.0.currency', 'GHS')
                ->where('settings.booking.deposit_rate', 30)
                ->has('calendar.cells')
                ->where('calendar.year', now()->year)
                ->where('calendar.month', now()->month)
                ->where('calendar.canPrev', false));
    }

    public function test_booking_calendar_accepts_a_month_and_marks_past_dates_unavailable(): void
    {
        $past = now()->subMonth();

        $this->get('/booking?year='.$past->year.'&month='.$past->month)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Booking')
                ->where('calendar.year', $past->year)
                ->where('calendar.month', $past->month)
                ->where('calendar.canPrev', false)
                ->where('calendar.canNext', true)
                ->where('calendar.cells.7.selectable', false));
    }

    public function test_booking_calendar_rejects_an_invalid_month(): void
    {
        $this->get('/booking?year=2026&month=13')->assertSessionHasErrors(['month']);
    }

    public function test_galleries_index_lists_categories(): void
    {
        $this->get('/galleries')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Galleries')
                ->has('categories', 5)
                ->where('category', null)
                ->has('images', 0)
                ->where('pagination.hasMore', false)
                ->where('categories.0.name', 'Weddings'));
    }

    public function test_gallery_cards_use_the_chosen_cover_image(): void
    {
        $category = Category::query()->where('slug', 'weddings')->firstOrFail();
        $cover = PortfolioImage::create([
            'category_id' => $category->id,
            'path' => 'portfolio/weddings/chosen-cover.jpg',
            'alt' => 'Chosen wedding cover',
        ]);
        $category->update(['cover_image_id' => $cover->id]);

        $this->get('/galleries')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Galleries')
                ->where('categories.0.cover.id', $cover->id)
                ->where('categories.0.cover.path', 'portfolio/weddings/chosen-cover.jpg')
                ->where('categories.0.cover.alt', 'Chosen wedding cover'));

        $category->update(['cover_image_id' => null]);

        $this->get('/galleries')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Galleries')
                ->where('categories.0.cover', null));
    }

    public function test_gallery_category_shows_portfolio_images(): void
    {
        $this->get('/galleries/weddings')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Galleries')
                ->where('category.name', 'Weddings')
                ->where('category.count', 9)
                ->has('images', 6)
                ->where('images.0.path', 'portfolio/weddings/01.jpg')
                ->where('images.0.alt', 'Ama & Kojo under the palms')
                ->where('images.0.featured', true)
                ->where('pagination.total', 9)
                ->where('pagination.hasMore', true)
                ->where('pagination.nextPage', 2));
    }

    public function test_gallery_category_can_load_another_page(): void
    {
        $this->get('/galleries/weddings?page=2')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Galleries')
                ->has('images', 3)
                ->where('images.0.path', 'portfolio/weddings/07.jpg')
                ->where('pagination.page', 2)
                ->where('pagination.hasMore', false)
                ->where('pagination.nextPage', null));
    }

    public function test_unknown_gallery_category_returns_not_found(): void
    {
        $this->get('/galleries/not-a-category')->assertNotFound();
    }
}

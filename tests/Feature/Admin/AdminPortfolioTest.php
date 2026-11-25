<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\PortfolioImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminPortfolioTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create();
    }

    private function category(): Category
    {
        return Category::create(['name' => 'Weddings', 'slug' => 'weddings', 'sort' => 1]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin/portfolio')->assertRedirect('/login');
    }

    public function test_admin_can_view_the_portfolio(): void
    {
        $category = $this->category();
        PortfolioImage::create([
            'category_id' => $category->id,
            'path' => 'portfolio/ama-and-kojo.jpg',
            'alt' => 'Ama & Kojo under the palms',
            'featured' => true,
            'sort' => 1,
        ]);

        $this->actingAs($this->admin())
            ->get('/admin/portfolio')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('AdminPortfolio')
                ->has('images', 1)
                ->where('images.0.category', 'Weddings')
                ->where('images.0.featured', true)
                ->has('categories', 1));
    }

    public function test_admin_can_add_an_image(): void
    {
        Storage::fake('public');
        $category = $this->category();

        $this->actingAs($this->admin())
            ->post('/admin/portfolio', [
                'alt' => 'Golden hour portrait',
                'category_id' => $category->id,
                'image' => UploadedFile::fake()->image('portrait.jpg'),
                'featured' => true,
            ])
            ->assertSessionHas('success', true);

        $image = PortfolioImage::query()->first();

        $this->assertNotNull($image);
        $this->assertSame($category->id, $image->category_id);
        $this->assertTrue($image->featured);
        $this->assertStringStartsWith('portfolio/', $image->path);
        Storage::disk('public')->assertExists($image->path);
    }

    public function test_add_image_requires_a_file(): void
    {
        $category = $this->category();

        $this->actingAs($this->admin())
            ->post('/admin/portfolio', [
                'alt' => 'Golden hour portrait',
                'category_id' => $category->id,
            ])
            ->assertSessionHasErrors('image');
    }

    public function test_admin_can_toggle_featured(): void
    {
        $category = $this->category();
        $image = PortfolioImage::create([
            'category_id' => $category->id,
            'path' => 'portfolio/one.jpg',
            'alt' => 'One',
            'featured' => false,
            'sort' => 1,
        ]);

        $this->actingAs($this->admin())
            ->post("/admin/portfolio/{$image->uuid}/toggle-featured")
            ->assertSessionHas('success', true);

        $this->assertTrue($image->refresh()->featured);
    }

    public function test_admin_can_choose_the_card_cover_for_a_category(): void
    {
        $category = $this->category();
        $first = PortfolioImage::create([
            'category_id' => $category->id,
            'path' => 'portfolio/one.jpg',
            'alt' => 'One',
            'sort' => 1,
        ]);
        $second = PortfolioImage::create([
            'category_id' => $category->id,
            'path' => 'portfolio/two.jpg',
            'alt' => 'Two',
            'sort' => 2,
        ]);

        $this->actingAs($this->admin())
            ->post("/admin/portfolio/{$first->uuid}/set-cover")
            ->assertSessionHas('success', true);

        $this->assertSame($first->id, $category->fresh()->cover_image_id);

        $this->get('/admin/portfolio')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('categories.0.cover_image_id', $first->id)
                ->where('images.0.cover', true)
                ->where('images.1.cover', false));

        $this->post("/admin/portfolio/{$second->uuid}/set-cover")
            ->assertSessionHas('success', true);

        $this->assertSame($second->id, $category->fresh()->cover_image_id);
    }

    public function test_admin_can_delete_an_image(): void
    {
        $category = $this->category();
        $image = PortfolioImage::create([
            'category_id' => $category->id,
            'path' => 'portfolio/one.jpg',
            'alt' => 'One',
            'sort' => 1,
        ]);

        $this->actingAs($this->admin())
            ->delete("/admin/portfolio/{$image->uuid}")
            ->assertSessionHas('success', true);

        $this->assertDatabaseMissing('portfolio_images', ['id' => $image->id]);
    }

    public function test_adding_an_image_requires_caption_and_category(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/portfolio', [])
            ->assertSessionHasErrors(['alt', 'category_id', 'image']);
    }

    public function test_admin_can_add_a_category(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/portfolio/categories', [
                'name' => 'Anniversaries',
                'slug' => 'anniversaries',
                'sort' => 6,
            ])
            ->assertSessionHas('success', true);

        $this->assertDatabaseHas('categories', ['name' => 'Anniversaries', 'slug' => 'anniversaries', 'sort' => 6]);
    }

    public function test_category_name_and_slug_must_be_unique(): void
    {
        $category = $this->category();

        $this->actingAs($this->admin())
            ->post('/admin/portfolio/categories', [
                'name' => $category->name,
                'slug' => $category->slug,
            ])
            ->assertSessionHasErrors(['name', 'slug']);
    }

    public function test_admin_can_update_a_category(): void
    {
        $category = $this->category();

        $this->actingAs($this->admin())
            ->put("/admin/portfolio/categories/{$category->id}", [
                'name' => 'Wedding days',
                'slug' => 'wedding-days',
                'sort' => 3,
            ])
            ->assertSessionHas('success', true);

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Wedding days', 'slug' => 'wedding-days', 'sort' => 3]);
    }

    public function test_admin_can_move_an_image_to_any_position(): void
    {
        $category = $this->category();
        $first = PortfolioImage::create(['category_id' => $category->id, 'path' => 'portfolio/one.jpg', 'alt' => 'One', 'sort' => 1]);
        $second = PortfolioImage::create(['category_id' => $category->id, 'path' => 'portfolio/two.jpg', 'alt' => 'Two', 'sort' => 2]);
        $third = PortfolioImage::create(['category_id' => $category->id, 'path' => 'portfolio/three.jpg', 'alt' => 'Three', 'sort' => 3]);

        $admin = $this->actingAs($this->admin());

        $admin->post("/admin/portfolio/{$third->uuid}/reorder", ['position' => 1])
            ->assertSessionHas('success', true);

        $this->assertSame(2, $first->fresh()->sort);
        $this->assertSame(3, $second->fresh()->sort);
        $this->assertSame(1, $third->fresh()->sort);

        $admin->get('/galleries/weddings')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('images.0.id', $third->id)
                ->where('images.1.id', $first->id)
                ->where('images.2.id', $second->id));

        $admin->post("/admin/portfolio/{$first->uuid}/reorder", ['position' => 3])
            ->assertSessionHas('success', true);

        $this->assertSame(1, $third->fresh()->sort);
        $this->assertSame(2, $second->fresh()->sort);
        $this->assertSame(3, $first->fresh()->sort);
    }

    public function test_admin_can_delete_a_category_with_its_images(): void
    {
        $category = $this->category();
        $image = PortfolioImage::create([
            'category_id' => $category->id,
            'path' => 'portfolio/one.jpg',
            'alt' => 'One',
            'sort' => 1,
        ]);

        $this->actingAs($this->admin())
            ->delete("/admin/portfolio/categories/{$category->id}")
            ->assertSessionHas('success', true);

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
        $this->assertDatabaseMissing('portfolio_images', ['id' => $image->id]);
    }
}

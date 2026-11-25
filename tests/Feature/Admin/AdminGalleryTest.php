<?php

namespace Tests\Feature\Admin;

use App\Enums\ClientSource;
use App\Models\Client;
use App\Models\ClientGallery;
use App\Models\GalleryImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminGalleryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        Storage::fake('public');
    }

    private function admin(): User
    {
        return User::factory()->create();
    }

    private function client(): Client
    {
        return Client::create([
            'name' => 'Ama Boateng',
            'email' => 'ama@example.com',
            'source' => ClientSource::Manual,
        ]);
    }

    private function gallery(): ClientGallery
    {
        return ClientGallery::create([
            'client_id' => $this->client()->id,
            'title' => 'Ama · Wedding',
            'token' => 'token123456789',
            'expires_at' => now()->addMonth(),
            'allow_download' => true,
        ]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin/galleries')->assertRedirect('/login');
    }

    public function test_admin_can_view_galleries(): void
    {
        $this->gallery();

        $this->actingAs($this->admin())
            ->get('/admin/galleries')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('AdminGalleries')
                ->has('galleries', 1)
                ->where('galleries.0.client', 'Ama Boateng')
                ->where('galleries.0.status', 'Active')
                ->has('clients'));
    }

    public function test_admin_can_create_a_gallery(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/galleries', [
                'client_id' => $this->client()->id,
                'title' => 'Esi · Portraits',
                'expires_at' => now()->addWeeks(2)->toDateString(),
                'allow_download' => true,
                'passcode' => 'secret1',
            ])
            ->assertSessionHas('success', true);

        $gallery = ClientGallery::query()->first();

        $this->assertNotNull($gallery);
        $this->assertSame('Esi · Portraits', $gallery->title);
        $this->assertNotNull($gallery->passcode_hash);
        $this->assertSame(16, strlen($gallery->token));
    }

    public function test_creating_a_gallery_validates_required_fields(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/galleries', [])
            ->assertSessionHasErrors(['client_id', 'title']);
    }

    public function test_admin_can_upload_images(): void
    {
        $gallery = $this->gallery();

        $this->actingAs($this->admin())
            ->post("/admin/galleries/{$gallery->uuid}/images", [
                'images' => [
                    UploadedFile::fake()->image('one.jpg'),
                    UploadedFile::fake()->image('two.jpg'),
                ],
            ])
            ->assertSessionHas('success', true);

        $this->assertSame(2, $gallery->images()->count());
        $this->assertNotNull(GalleryImage::query()->first()->original_path);
    }

    public function test_admin_can_delete_an_image(): void
    {
        $gallery = $this->gallery();
        $image = $gallery->images()->create(['original_path' => "galleries/{$gallery->id}/a.jpg", 'sort' => 1]);

        $this->actingAs($this->admin())
            ->delete("/admin/galleries/images/{$image->uuid}")
            ->assertSessionHas('success', true);

        $this->assertDatabaseMissing('gallery_images', ['id' => $image->id]);
    }

    public function test_admin_can_toggle_downloads(): void
    {
        $gallery = $this->gallery();

        $this->actingAs($this->admin())
            ->post("/admin/galleries/{$gallery->uuid}/toggle-download")
            ->assertSessionHas('success', true);

        $this->assertFalse($gallery->refresh()->allow_download);
    }

    public function test_admin_can_delete_a_gallery(): void
    {
        $gallery = $this->gallery();

        $this->actingAs($this->admin())
            ->delete("/admin/galleries/{$gallery->uuid}")
            ->assertRedirect(route('admin.galleries'));

        $this->assertDatabaseMissing('client_galleries', ['id' => $gallery->id]);
    }
}

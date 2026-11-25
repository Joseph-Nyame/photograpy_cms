<?php

namespace Tests\Feature;

use App\Enums\ClientSource;
use App\Enums\GalleryAccessAction;
use App\Models\Client;
use App\Models\ClientGallery;
use App\Models\GalleryImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ClientGalleryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    private function gallery(bool $withPasscode = true, bool $allowDownload = true): ClientGallery
    {
        $client = Client::create([
            'name' => 'Ama Boateng',
            'email' => 'ama@example.com',
            'source' => ClientSource::Manual,
        ]);

        return ClientGallery::create([
            'client_id' => $client->id,
            'title' => 'Ama · Wedding',
            'token' => 'token1234567890ab',
            'passcode_hash' => $withPasscode ? Hash::make('secret1') : null,
            'expires_at' => now()->addWeek(),
            'allow_download' => $allowDownload,
        ]);
    }

    private function image(ClientGallery $gallery): GalleryImage
    {
        $path = "galleries/{$gallery->id}/one.png";
        Storage::disk('public')->put($path, UploadedFile::fake()->image('one.png')->getContent());

        return $gallery->images()->create([
            'original_path' => $path,
            'preview_path' => $path,
            'thumb_path' => $path,
            'sort' => 1,
        ]);
    }

    private function unlock(ClientGallery $gallery)
    {
        return $this->post("/g/{$gallery->token}/unlock", ['passcode' => 'secret1']);
    }

    public function test_a_protected_gallery_shows_the_unlock_form_without_images(): void
    {
        $gallery = $this->gallery();
        $this->image($gallery);

        $this->get("/g/{$gallery->token}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('ClientGallery')
                ->where('unlocked', false)
                ->where('gallery.title', 'Ama · Wedding')
                ->has('images', 0));
    }

    public function test_a_wrong_passcode_is_rejected(): void
    {
        $gallery = $this->gallery();

        $this->post("/g/{$gallery->token}/unlock", ['passcode' => 'wrong'])
            ->assertSessionHasErrors('passcode');
    }

    public function test_unlock_attempts_are_throttled(): void
    {
        $gallery = $this->gallery();

        for ($i = 0; $i < 5; $i++) {
            $this->post("/g/{$gallery->token}/unlock", ['passcode' => 'wrong'])
                ->assertSessionHasErrors('passcode');
        }

        $this->post("/g/{$gallery->token}/unlock", ['passcode' => 'secret1'])
            ->assertStatus(429);
    }

    public function test_the_correct_passcode_unlocks_the_gallery_and_logs_a_view(): void
    {
        $gallery = $this->gallery();
        $image = $this->image($gallery);

        $this->unlock($gallery)->assertRedirect(route('client-gallery', $gallery->token));

        $this->get("/g/{$gallery->token}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('unlocked', true)
                ->has('images', 1)
                ->where('images.0.id', $image->id)
                ->where('images.0.favourite', false)
                ->has('images.0.download_url'));

        $this->assertSame(1, $gallery->accessLogs()->where('action', GalleryAccessAction::View)->count());
    }

    public function test_a_gallery_without_a_passcode_is_open_immediately(): void
    {
        $gallery = $this->gallery(withPasscode: false);
        $this->image($gallery);

        $this->get("/g/{$gallery->token}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('unlocked', true)
                ->has('images', 1));
    }

    public function test_an_expired_gallery_is_gone(): void
    {
        $gallery = $this->gallery();
        $gallery->update(['expires_at' => now()->subDay()]);

        $this->get("/g/{$gallery->token}")->assertStatus(410);
    }

    public function test_previews_require_an_unlocked_gallery(): void
    {
        $gallery = $this->gallery();
        $image = $this->image($gallery);
        $url = route('client-gallery.preview', ['gallery' => $gallery->token, 'image' => $image->uuid]);

        $this->get($url)->assertForbidden();

        $this->unlock($gallery);

        $this->get($url)
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_favourites_can_be_toggled(): void
    {
        $gallery = $this->gallery();
        $image = $this->image($gallery);
        $this->unlock($gallery);

        $url = "/g/{$gallery->token}/favourites/{$image->uuid}";

        $this->post($url)->assertSessionHas('success', true);
        $this->assertSame([$image->id], session("gallery.favourites.{$gallery->id}"));

        $this->post($url)->assertSessionHas('success', true);
        $this->assertSame([], session("gallery.favourites.{$gallery->id}"));
        $this->assertSame(2, $gallery->accessLogs()->where('action', GalleryAccessAction::Favourite)->count());
    }

    public function test_originals_require_a_signed_url_when_downloads_are_allowed(): void
    {
        $gallery = $this->gallery();
        $image = $this->image($gallery);
        $url = URL::signedRoute('client-gallery.download', ['gallery' => $gallery->token, 'image' => $image->uuid]);

        $this->get($url)->assertForbidden();

        $this->unlock($gallery);

        $this->get($url)->assertOk();
        $this->assertSame(1, $gallery->accessLogs()->where('action', GalleryAccessAction::Download)->count());
    }

    public function test_a_tampered_signed_url_is_rejected(): void
    {
        $gallery = $this->gallery();
        $image = $this->image($gallery);
        $this->unlock($gallery);

        $url = URL::signedRoute('client-gallery.download', ['gallery' => $gallery->token, 'image' => $image->uuid]);

        $this->get($url.'&tampered=1')->assertForbidden();
    }

    public function test_downloads_can_be_disabled_for_a_gallery(): void
    {
        $gallery = $this->gallery(allowDownload: false);
        $image = $this->image($gallery);
        $this->unlock($gallery);

        $url = URL::signedRoute('client-gallery.download', ['gallery' => $gallery->token, 'image' => $image->uuid]);

        $this->get($url)->assertForbidden();
    }
}

<?php

namespace Tests\Feature\Admin;

use App\Enums\CampaignRecipientStatus;
use App\Enums\CampaignStatus;
use App\Enums\ClientSource;
use App\Mail\ClientEmail;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminCampaignTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        Mail::fake();
    }

    private function admin(): User
    {
        return User::factory()->create();
    }

    private function client(string $name, string $email, bool $subscribed = true): Client
    {
        return Client::create([
            'name' => $name,
            'email' => $email,
            'source' => ClientSource::Manual,
            'subscribed' => $subscribed,
        ]);
    }

    private function campaign(Client ...$clients): Campaign
    {
        $campaign = Campaign::create([
            'subject' => 'December sessions',
            'body' => 'Book your session now.',
            'status' => CampaignStatus::Draft,
        ]);

        foreach ($clients as $client) {
            $campaign->recipients()->create([
                'client_id' => $client->id,
                'status' => CampaignRecipientStatus::Pending,
            ]);
        }

        return $campaign;
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin/campaigns')->assertRedirect('/login');
    }

    public function test_admin_can_view_campaigns(): void
    {
        $campaign = $this->campaign($this->client('Ama Boateng', 'ama@example.com'));

        $this->actingAs($this->admin())
            ->get('/admin/campaigns')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('AdminCampaigns')
                ->has('campaigns', 1)
                ->where('campaigns.0.subject', 'December sessions')
                ->where('campaigns.0.status', 'Draft')
                ->where('campaigns.0.recipients', 1)
                ->has('audiences', 3));
    }

    public function test_admin_can_queue_a_campaign_for_subscribed_clients(): void
    {
        $subscribed = $this->client('Ama Boateng', 'ama@example.com');
        $this->client('Esi Owusu', 'esi@example.com', false);

        $this->actingAs($this->admin())
            ->post('/admin/campaigns', [
                'subject' => 'December sessions',
                'body' => 'Book your session now.',
                'audience' => 'subscribed',
            ])
            ->assertSessionHas('success', true);

        $campaign = Campaign::query()->first();

        $this->assertNotNull($campaign);
        $this->assertSame(CampaignStatus::Draft, $campaign->status);
        $this->assertSame(1, $campaign->recipients()->count());
        $this->assertSame($subscribed->id, $campaign->recipients()->first()->client_id);
        $this->assertSame(CampaignRecipientStatus::Pending, $campaign->recipients()->first()->status);
    }

    public function test_audience_all_includes_every_client(): void
    {
        $this->client('Ama Boateng', 'ama@example.com');
        $this->client('Esi Owusu', 'esi@example.com', false);

        $this->actingAs($this->admin())
            ->post('/admin/campaigns', [
                'subject' => 'Studio news',
                'body' => 'Hello.',
                'audience' => 'all',
            ])
            ->assertSessionHas('success', true);

        $this->assertSame(2, Campaign::query()->first()->recipients()->count());
    }

    public function test_queueing_a_campaign_validates_input(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/campaigns', [])
            ->assertSessionHasErrors(['subject', 'body', 'audience']);
    }

    public function test_admin_can_send_a_campaign(): void
    {
        $ama = $this->client('Ama Boateng', 'ama@example.com');
        $kojo = $this->client('Kojo Mensah', 'kojo@example.com');
        $campaign = $this->campaign($ama, $kojo);

        $this->actingAs($this->admin())
            ->post("/admin/campaigns/{$campaign->uuid}/send")
            ->assertSessionHas('success', true);

        $campaign->refresh();

        $this->assertSame(CampaignStatus::Sent, $campaign->status);
        $this->assertNotNull($campaign->sent_at);
        $this->assertSame(2, $campaign->recipients()->where('status', CampaignRecipientStatus::Sent)->count());
        $this->assertSame(2, $ama->emailMessages()->count() + $kojo->emailMessages()->count());
        Mail::assertSent(ClientEmail::class, 2);
    }

    public function test_a_sent_campaign_cannot_be_sent_again(): void
    {
        $campaign = $this->campaign($this->client('Ama Boateng', 'ama@example.com'));
        $campaign->update(['status' => CampaignStatus::Sent, 'sent_at' => now()]);

        $this->actingAs($this->admin())
            ->post("/admin/campaigns/{$campaign->uuid}/send")
            ->assertSessionHas('success', false);

        Mail::assertNothingSent();
    }

    public function test_admin_can_duplicate_a_campaign(): void
    {
        $campaign = $this->campaign($this->client('Ama Boateng', 'ama@example.com'));

        $this->actingAs($this->admin())
            ->post("/admin/campaigns/{$campaign->uuid}/duplicate")
            ->assertSessionHas('success', true);

        $copy = Campaign::query()->where('id', '!=', $campaign->id)->first();

        $this->assertNotNull($copy);
        $this->assertSame('December sessions (copy)', $copy->subject);
        $this->assertSame(CampaignStatus::Draft, $copy->status);
        $this->assertSame(1, $copy->recipients()->count());
        $this->assertSame(CampaignRecipientStatus::Pending, $copy->recipients()->first()->status);
    }

    public function test_admin_can_delete_a_campaign(): void
    {
        $campaign = $this->campaign($this->client('Ama Boateng', 'ama@example.com'));

        $this->actingAs($this->admin())
            ->delete("/admin/campaigns/{$campaign->uuid}")
            ->assertRedirect(route('admin.campaigns'));

        $this->assertDatabaseMissing('campaigns', ['id' => $campaign->id]);
        $this->assertDatabaseMissing('campaign_recipients', ['campaign_id' => $campaign->id]);
    }
}

<?php

namespace App\Actions\Campaigns;

use App\DTOs\Campaigns\CreateCampaignDTO;
use App\Enums\CampaignRecipientStatus;
use App\Enums\CampaignStatus;
use App\Models\Campaign;
use App\Services\AdminCampaignService;
use App\Support\Result;

class CreateCampaignAction
{
    public function __construct(private readonly AdminCampaignService $campaigns) {}

    public function execute(CreateCampaignDTO $dto): Result
    {
        $campaign = Campaign::create([
            'subject' => $dto->subject,
            'body' => $dto->body,
            'status' => CampaignStatus::Draft,
        ]);

        foreach ($this->campaigns->recipients($dto->audience) as $client) {
            $campaign->recipients()->create([
                'client_id' => $client->id,
                'status' => CampaignRecipientStatus::Pending,
            ]);
        }

        $campaign->loadCount('recipients');

        return Result::ok($this->campaigns->present($campaign), 'Campaign queued.');
    }
}

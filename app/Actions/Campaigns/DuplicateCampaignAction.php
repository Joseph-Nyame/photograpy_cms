<?php

namespace App\Actions\Campaigns;

use App\Enums\CampaignRecipientStatus;
use App\Enums\CampaignStatus;
use App\Models\Campaign;
use App\Services\AdminCampaignService;
use App\Support\Result;

class DuplicateCampaignAction
{
    public function __construct(private readonly AdminCampaignService $campaigns) {}

    public function execute(Campaign $campaign): Result
    {
        $copy = Campaign::create([
            'subject' => $campaign->subject.' (copy)',
            'body' => $campaign->body,
            'status' => CampaignStatus::Draft,
        ]);

        foreach ($campaign->recipients as $recipient) {
            $copy->recipients()->create([
                'client_id' => $recipient->client_id,
                'status' => CampaignRecipientStatus::Pending,
            ]);
        }

        $copy->loadCount('recipients');

        return Result::ok($this->campaigns->present($copy), 'Campaign duplicated as a draft.');
    }
}

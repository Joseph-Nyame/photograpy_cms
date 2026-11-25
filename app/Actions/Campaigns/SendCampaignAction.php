<?php

namespace App\Actions\Campaigns;

use App\Enums\CampaignRecipientStatus;
use App\Enums\CampaignStatus;
use App\Enums\EmailMessageStatus;
use App\Models\Campaign;
use App\Models\EmailMessage;
use App\Services\AdminCampaignService;
use App\Services\EmailDeliveryService;
use App\Support\Result;

class SendCampaignAction
{
    public function __construct(
        private readonly AdminCampaignService $campaigns,
        private readonly EmailDeliveryService $delivery,
    ) {}

    public function execute(Campaign $campaign): Result
    {
        if ($campaign->status === CampaignStatus::Sent) {
            return Result::fail('Campaign has already been sent.');
        }

        $recipients = $campaign->recipients()
            ->with('client')
            ->where('status', CampaignRecipientStatus::Pending)
            ->get();

        foreach ($recipients as $recipient) {
            $client = $recipient->client;

            if (! $client) {
                $recipient->update(['status' => CampaignRecipientStatus::Failed]);

                continue;
            }

            try {
                $this->delivery->send($client->email, $campaign->subject, $campaign->body, $client->name);

                EmailMessage::create([
                    'client_id' => $client->id,
                    'subject' => $campaign->subject,
                    'body' => $campaign->body,
                    'sent_at' => now(),
                    'status' => EmailMessageStatus::Sent,
                ]);

                $recipient->update(['status' => CampaignRecipientStatus::Sent]);
            } catch (\Throwable) {
                $recipient->update(['status' => CampaignRecipientStatus::Failed]);
            }
        }

        $campaign->update([
            'status' => CampaignStatus::Sent,
            'sent_at' => now(),
        ]);
        $campaign->loadCount('recipients');

        return Result::ok($this->campaigns->present($campaign), 'Campaign sent.');
    }
}

<?php

namespace App\Actions\Campaigns;

use App\Models\Campaign;
use App\Support\Result;

class DeleteCampaignAction
{
    public function execute(Campaign $campaign): Result
    {
        $campaign->delete();

        return Result::ok(null, 'Campaign deleted.');
    }
}

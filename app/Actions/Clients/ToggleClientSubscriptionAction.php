<?php

namespace App\Actions\Clients;

use App\Models\Client;
use App\Support\Result;

class ToggleClientSubscriptionAction
{
    public function execute(Client $client): Result
    {
        $client->update(['subscribed' => ! $client->subscribed]);

        return Result::ok(
            $client->refresh(),
            $client->subscribed ? 'Client subscribed.' : 'Client unsubscribed.',
        );
    }
}

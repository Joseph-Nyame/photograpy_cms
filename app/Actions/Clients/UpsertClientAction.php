<?php

namespace App\Actions\Clients;

use App\DTOs\Clients\UpsertClientDTO;
use App\Models\Client;

class UpsertClientAction
{
    public function execute(UpsertClientDTO $dto): Client
    {
        $client = Client::firstOrNew(['email' => $dto->email]);

        $client->name = $dto->name;

        if ($dto->phone !== null && $dto->phone !== '') {
            $client->phone = $dto->phone;
        }

        if (! $client->exists) {
            $client->source = $dto->source;
        }

        $client->save();

        return $client;
    }
}

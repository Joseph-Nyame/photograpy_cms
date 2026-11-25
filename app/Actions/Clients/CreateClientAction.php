<?php

namespace App\Actions\Clients;

use App\DTOs\Clients\CreateClientDTO;
use App\Models\Client;
use App\Support\Result;

class CreateClientAction
{
    public function execute(CreateClientDTO $dto): Result
    {
        $client = Client::create([
            'name' => $dto->name,
            'email' => $dto->email,
            'phone' => $dto->phone,
            'source' => $dto->source,
            'subscribed' => $dto->subscribed,
        ]);

        return Result::ok($client, 'Client added.');
    }
}

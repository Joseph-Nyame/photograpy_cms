<?php

namespace App\Actions\Contact;

use App\Actions\Clients\UpsertClientAction;
use App\Actions\Notifications\NotifyAdminsAction;
use App\DTOs\Clients\UpsertClientDTO;
use App\DTOs\Contact\CreateContactMessageDTO;
use App\Enums\ClientSource;
use App\Models\ContactMessage;
use App\Support\Result;
use Illuminate\Support\Str;

class CreateContactMessageAction
{
    public function __construct(
        private readonly UpsertClientAction $upsertClient,
        private readonly NotifyAdminsAction $notifyAdmins,
    ) {}

    public function execute(CreateContactMessageDTO $dto): Result
    {
        $client = $this->upsertClient->execute(new UpsertClientDTO(
            name: $dto->name,
            email: $dto->email,
            phone: $dto->phone,
            source: ClientSource::Contact,
        ));

        $message = ContactMessage::create([
            'client_id' => $client->id,
            'name' => $dto->name,
            'email' => $dto->email,
            'phone' => $dto->phone,
            'message' => $dto->message,
        ]);

        $this->notifyAdmins->execute(
            title: 'New contact message',
            body: Str::limit("{$dto->name}: {$dto->message}", 90),
            url: route('admin.messages'),
            tone: 'warning',
        );

        return Result::ok($message, 'Message sent. We will reply within one working day.');
    }
}

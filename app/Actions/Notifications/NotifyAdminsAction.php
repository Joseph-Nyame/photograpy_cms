<?php

namespace App\Actions\Notifications;

use App\Models\User;
use App\Notifications\AdminNotification;
use App\Services\AdminNavigationService;
use App\Support\Result;
use Throwable;

class NotifyAdminsAction
{
    public function __construct(private readonly AdminNavigationService $navigation) {}

    public function execute(string $title, string $body, string $url, string $tone = 'info'): Result
    {
        $notification = new AdminNotification(
            title: $title,
            body: $body,
            url: $url,
            counts: $this->navigation->counts(),
            tone: $tone,
        );

        foreach (User::query()->orderBy('id')->get() as $admin) {
            try {
                $admin->notify($notification);
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return Result::ok($notification, 'Admins notified.');
    }
}

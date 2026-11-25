<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Emails\ResendEmailAction;
use App\Actions\Emails\SendEmailAction;
use App\DTOs\Emails\SendEmailDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SendEmailRequest;
use App\Models\EmailMessage;
use App\Services\AdminEmailService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class EmailController extends Controller
{
    public function index(AdminEmailService $emails): Response
    {
        return Inertia::render('AdminEmails', [
            'emails' => $emails->emails(),
            'clients' => $emails->clients(),
            'templates' => $emails->templates(),
        ]);
    }

    public function store(SendEmailRequest $request, SendEmailAction $action): RedirectResponse
    {
        $result = $action->execute(SendEmailDTO::fromRequest($request));

        return back()
            ->with('data', $result->data)
            ->with('success', $result->success)
            ->with('message', $result->message);
    }

    public function resend(EmailMessage $email, ResendEmailAction $action): RedirectResponse
    {
        $result = $action->execute($email);

        return back()
            ->with('data', $result->data)
            ->with('success', $result->success)
            ->with('message', $result->message);
    }
}

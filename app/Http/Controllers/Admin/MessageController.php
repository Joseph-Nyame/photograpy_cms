<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Messages\MarkAllMessagesReadAction;
use App\Actions\Messages\MarkMessageReadAction;
use App\Actions\Messages\MarkMessageUnreadAction;
use App\Actions\Messages\ReplyToContactMessageAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReplyToMessageRequest;
use App\Models\ContactMessage;
use App\Services\AdminMessageService;
use App\Support\Result;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class MessageController extends Controller
{
    public function index(AdminMessageService $messages): Response
    {
        return Inertia::render('AdminMessages', [
            'messages' => $messages->messages(),
            'unread' => $messages->unreadCount(),
        ]);
    }

    public function read(ContactMessage $message, MarkMessageReadAction $action): RedirectResponse
    {
        return $this->flash($action->execute($message));
    }

    public function unread(ContactMessage $message, MarkMessageUnreadAction $action): RedirectResponse
    {
        return $this->flash($action->execute($message));
    }

    public function readAll(MarkAllMessagesReadAction $action): RedirectResponse
    {
        return $this->flash($action->execute());
    }

    public function reply(ContactMessage $message, ReplyToMessageRequest $request, ReplyToContactMessageAction $action): RedirectResponse
    {
        return $this->flash($action->execute($message, $request->validated('body')));
    }

    private function flash(Result $result): RedirectResponse
    {
        return back()
            ->with('data', $result->data)
            ->with('success', $result->success)
            ->with('message', $result->message);
    }
}

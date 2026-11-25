<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Campaigns\CreateCampaignAction;
use App\Actions\Campaigns\DeleteCampaignAction;
use App\Actions\Campaigns\DuplicateCampaignAction;
use App\Actions\Campaigns\SendCampaignAction;
use App\DTOs\Campaigns\CreateCampaignDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCampaignRequest;
use App\Models\Campaign;
use App\Services\AdminCampaignService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CampaignController extends Controller
{
    public function index(AdminCampaignService $campaigns): Response
    {
        return Inertia::render('AdminCampaigns', [
            'campaigns' => $campaigns->campaigns(),
            'audiences' => $campaigns->audiences(),
        ]);
    }

    public function store(StoreCampaignRequest $request, CreateCampaignAction $action): RedirectResponse
    {
        $result = $action->execute(CreateCampaignDTO::fromRequest($request));

        return back()
            ->with('data', $result->data)
            ->with('success', $result->success)
            ->with('message', $result->message);
    }

    public function send(Campaign $campaign, SendCampaignAction $action): RedirectResponse
    {
        $result = $action->execute($campaign);

        return back()
            ->with('data', $result->data)
            ->with('success', $result->success)
            ->with('message', $result->message);
    }

    public function duplicate(Campaign $campaign, DuplicateCampaignAction $action): RedirectResponse
    {
        $result = $action->execute($campaign);

        return back()
            ->with('data', $result->data)
            ->with('success', $result->success)
            ->with('message', $result->message);
    }

    public function destroy(Campaign $campaign, DeleteCampaignAction $action): RedirectResponse
    {
        $result = $action->execute($campaign);

        return redirect()
            ->route('admin.campaigns')
            ->with('success', $result->success)
            ->with('message', $result->message);
    }
}

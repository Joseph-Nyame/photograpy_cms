<?php

namespace App\Http\Middleware;

use App\Services\AdminNavigationService;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function __construct(
        private readonly SettingsService $settings,
        private readonly AdminNavigationService $navigation,
    ) {}

    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user?->only('id', 'name', 'email'),
            ],
            'navigation' => fn () => $user ? $this->navigation->counts() : [],
            'flash' => [
                'data' => fn () => $request->session()->get('data'),
                'success' => fn () => $request->session()->get('success'),
                'message' => fn () => $request->session()->get('message'),
            ],
            'settings' => fn () => $this->settings->public(),
        ];
    }
}

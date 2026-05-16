<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
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
        $notifications = [];
        $notificationsUnreadCount = 0;
        if ($request->user()) {
            try {
                $service = app(\App\Services\SystemNotificationService::class);
                $payload = $service->buildForUser($request->user());
                $notifications = $payload['notifications'];
                $notificationsUnreadCount = $payload['unreadCount'];
            } catch (\Exception $e) {
                // Silently ignore
            }
        }

        // Resolve active period year
        $periodeAktif = null;
        try {
            $aktif = \App\Models\TahunPeriode::where('status', 'Aktif')->first();
            $periodeAktif = $aktif?->tahun;
        } catch (\Exception $e) {
            // Silently ignore
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user'  => $request->user(),
                'roles' => $request->user() ? $request->user()->getRoleNames()->toArray() : [],
                'role'  => $request->user() ? ($request->user()->getRoleNames()->first() ?? '') : '',
            ],
            'periodeAktif'  => $periodeAktif,
            'notifications' => $notifications,
            'notificationsUnreadCount' => $notificationsUnreadCount,
            'sidebarOpen'   => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'flash' => [
                'success' => $request->session()->get('success'),
                'error'   => $request->session()->get('error'),
            ],
        ];
    }
}

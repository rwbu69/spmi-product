<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function markRead(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id' => ['required', 'string'],
        ]);

        $user = $request->user();
        if (! $user) {
            abort(403);
        }

        $notification = $user->hasRole('Admin')
            ? DatabaseNotification::query()->findOrFail($data['id'])
            : $user->notifications()->findOrFail($data['id']);

        $notification->markAsRead();

        return back();
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user) {
            abort(403);
        }

        $user->unreadNotifications()->update(['read_at' => now()]);

        return back();
    }
}

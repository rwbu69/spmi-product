<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SystemNotificationService;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class SystemNotificationController extends Controller
{
    public function index(Request $request, SystemNotificationService $service): Response
    {
        $user = $request->user();
        if (! $user) {
            abort(403);
        }
        $query = DatabaseNotification::query();

        $query->when($request->date_from, fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->whereDate('created_at', '<=', $request->date_to));

        if ($request->role) {
            $role = $request->role;
            $query->where('data->actor_role', 'like', '%' . $role . '%');
        }

        if ($request->status === 'read') {
            $query->whereNotNull('read_at');
        } elseif ($request->status === 'unread') {
            $query->whereNull('read_at');
        }

        $perPage = (int) ($request->per_page ?? 10);
        $data = $query->latest()->paginate($perPage)->withQueryString();
        $data->getCollection()->transform(fn ($notification) => $service->mapHistory($notification));

        return Inertia::render('Admin/LogSistem/Index', [
            'data' => $data,
            'filters' => $request->only(['date_from', 'date_to', 'role', 'status', 'per_page']),
            'roleOptions' => Role::orderBy('name')->pluck('name'),
        ]);
    }
}

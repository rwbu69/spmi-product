<?php

namespace App\Http\Controllers\Pengaturan;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PenggunaPortalController extends Controller
{
    public function index(Request $request): Response
    {
        // For simplicity, we use the same User model. In a real app, this might be filtered by role.
        $query = User::query();

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%");
        }

        $data = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Pengaturan/PenggunaPortal/Index', [
            'data' => $data,
            'filters' => $request->only(['search']),
        ]);
    }
}

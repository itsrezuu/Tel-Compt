<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $context = $this->routeContext($request);
        $user = $this->authorizedUser($request, $context['roles']);
        $filter = (string) $request->query('filter', 'all');

        if (! in_array($filter, ['all', 'unread', 'read'], true)) {
            $filter = 'all';
        }

        $notifications = $user->notifications()
            ->when($filter === 'unread', fn ($query) => $query->whereNull('read_at'))
            ->when($filter === 'read', fn ($query) => $query->whereNotNull('read_at'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('notifications.index', [
            'layout' => $context['layout'],
            'routePrefix' => $context['routePrefix'],
            'notifications' => $notifications,
            'filter' => $filter,
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }

    public function markRead(Request $request, string $notification): RedirectResponse
    {
        $context = $this->routeContext($request);
        $user = $this->authorizedUser($request, $context['roles']);
        $user->notifications()->whereKey($notification)->firstOrFail()->markAsRead();

        return back()->with('success', 'Notifikasi ditandai sudah dibaca.');
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $context = $this->routeContext($request);
        $user = $this->authorizedUser($request, $context['roles']);
        $user->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }

    /**
     * @return array{roles: list<string>, layout: string, routePrefix: string}
     */
    private function routeContext(Request $request): array
    {
        if ($request->routeIs('mahasiswa.notifikasi.*')) {
            return [
                'roles' => ['mahasiswa'],
                'layout' => 'layouts.mahasiswa',
                'routePrefix' => 'mahasiswa.notifikasi',
            ];
        }

        if ($request->routeIs('admin-hima.notifikasi.*')) {
            return [
                'roles' => ['admin'],
                'layout' => 'layouts.admin-hima',
                'routePrefix' => 'admin-hima.notifikasi',
            ];
        }

        if ($request->routeIs('dosen.notifikasi.*')) {
            return [
                'roles' => ['dosen', 'kaprodi'],
                'layout' => 'layouts.dosen',
                'routePrefix' => 'dosen.notifikasi',
            ];
        }

        abort(404);
    }

    /**
     * @param  list<string>  $roles
     */
    private function authorizedUser(Request $request, array $roles): User
    {
        $user = $request->user();
        abort_unless($user instanceof User && in_array($user->role, $roles, true), 403);

        return $user;
    }
}

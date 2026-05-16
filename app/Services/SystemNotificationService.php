<?php

namespace App\Services;

use App\Models\LaporanAmi;
use App\Models\ManajemenDokumen;
use App\Models\User;
use App\Notifications\SystemMenuNotification;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

class SystemNotificationService
{
    private const DEFAULT_LIMIT = 5;
    private const PORTAL_ROLES = ['Auditee', 'Auditor', 'Fakultas', 'Unit Penunjang'];
    private const MAX_DAYS = 30;

    public function buildForUser(User $user, int $limit = self::DEFAULT_LIMIT): array
    {
        $allowedKeys = $this->allowedMenuKeys($user);
        $since = now()->subDays(self::MAX_DAYS);

        $notifications = $user->notifications()
            ->whereIn('data->menu_key', $allowedKeys)
            ->where('created_at', '>=', $since)
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (DatabaseNotification $notification) => $this->mapNotification($notification))
            ->values()
            ->all();

        $unreadCount = $user->unreadNotifications()
            ->whereIn('data->menu_key', $allowedKeys)
            ->where('created_at', '>=', $since)
            ->count();

        return [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ];
    }

    public function mapNotification(DatabaseNotification $notification): array
    {
        $data = $notification->data ?? [];
        $menuColor = $data['color'] ?? 'blue';

        return [
            'id' => $notification->id,
            'title' => $data['title'] ?? 'Notifikasi',
            'message' => $data['message'] ?? '',
            'time' => $notification->created_at?->diffForHumans() ?? '',
            'type' => $menuColor,
            'link' => $data['link'] ?? null,
            'is_read' => (bool) $notification->read_at,
        ];
    }

    public function mapHistory(DatabaseNotification $notification): array
    {
        $data = $notification->data ?? [];

        return [
            'id' => $notification->id,
            'title' => $data['title'] ?? 'Notifikasi',
            'description' => $data['message'] ?? '',
            'causer_name' => $data['actor_name'] ?? '-',
            'causer_role' => $data['actor_role'] ?? '-',
            'time' => $notification->created_at?->diffForHumans() ?? '',
            'created_at' => $notification->created_at,
            'is_read' => (bool) $notification->read_at,
        ];
    }

    public function notifyFromActivity(Activity $activity): bool
    {
        $moduleKey = $this->resolveModuleKey($activity);
        if (! $moduleKey) {
            return false;
        }

        $module = config('notification_menus.modules.' . $moduleKey);
        if (! $module) {
            return false;
        }

        $roles = Arr::wrap($module['roles'] ?? []);
        if (count($roles) === 0) {
            return false;
        }

        $users = User::role($roles)->get();
        if ($users->isEmpty()) {
            return false;
        }

        $subject = $activity->subject;
        $event = $activity->event ?? $activity->description;
        $eventLabel = $this->eventLabel($event);
        $subjectName = $this->resolveSubjectName($subject, $activity->subject_type);
        $title = $module['label'] ?? 'Notifikasi';
        $message = $subjectName
            ? "{$title} '{$subjectName}' {$eventLabel}."
            : "{$title} {$eventLabel}.";

        $causer = $activity->causer;
        $actorRoles = $causer && method_exists($causer, 'getRoleNames')
            ? $causer->getRoleNames()->implode(', ')
            : '-';
        $color = $module['color'] ?? 'blue';

        foreach ($users as $user) {
            if ($this->notificationExists($user, $activity->id, $moduleKey)) {
                continue;
            }

            $link = $this->resolveLink($module, $user);

            $payload = [
                'activity_id' => $activity->id,
                'menu_key' => $moduleKey,
                'menu_label' => $title,
                'title' => $title,
                'message' => $message,
                'event' => $event,
                'link' => $link,
                'color' => $color,
                'actor_name' => $causer?->name ?? '-',
                'actor_role' => $actorRoles ?: '-',
                'subject_type' => $activity->subject_type,
                'subject_id' => $activity->subject_id,
            ];

            $user->notify(new SystemMenuNotification($payload));
        }

        return true;
    }

    public function allowedMenuKeys(User $user): array
    {
        $roles = $user->getRoleNames()->toArray();
        $modules = config('notification_menus.modules', []);

        return collect($modules)
            ->filter(fn ($module) => count(array_intersect($roles, Arr::wrap($module['roles'] ?? []))) > 0)
            ->keys()
            ->values()
            ->all();
    }

    public function resolveModuleKey(Activity $activity): ?string
    {
        $subjectType = $activity->subject_type;
        $map = config('notification_menus.model_map', []);

        if ($subjectType === User::class) {
            $subject = $activity->subject;
            if ($subject && method_exists($subject, 'getRoleNames')) {
                $roles = $subject->getRoleNames()->toArray();
                $isPortal = count(array_intersect($roles, self::PORTAL_ROLES)) > 0;

                return $isPortal ? 'pengaturan_pengguna_portal' : 'pengaturan_pengguna_backoffice';
            }
        }

        return $map[$subjectType] ?? null;
    }

    private function resolveLink(array $module, User $user): ?string
    {
        $routeName = $module['route'] ?? null;
        $routeByRole = $module['route_by_role'] ?? [];

        if (count($routeByRole) > 0) {
            foreach ($user->getRoleNames() as $role) {
                if (isset($routeByRole[$role])) {
                    $routeName = $routeByRole[$role];
                    break;
                }
            }
        }

        if (! $routeName || ! Route::has($routeName)) {
            return null;
        }

        return route($routeName);
    }

    private function resolveSubjectName(mixed $subject, ?string $subjectType): ?string
    {
        if (! $subject) {
            return null;
        }

        if ($subject instanceof LaporanAmi) {
            return $subject->auditee?->nama_auditee;
        }

        if ($subject instanceof ManajemenDokumen) {
            return $subject->nama_dokumen;
        }

        foreach (['nama_auditee', 'nama_unit', 'nama_lembaga', 'nama_kategori', 'nama_jenis', 'nama_standar', 'nama', 'kode'] as $field) {
            if (isset($subject->{$field}) && $subject->{$field}) {
                return (string) $subject->{$field};
            }
        }

        if ($subjectType === User::class) {
            return $subject->name ?? null;
        }

        return null;
    }

    private function eventLabel(?string $event): string
    {
        return match ($event) {
            'created' => 'ditambahkan',
            'updated' => 'diperbarui',
            'deleted' => 'dihapus',
            default => Str::of((string) $event)->lower()->toString() ?: 'diperbarui',
        };
    }

    private function notificationExists(User $user, int $activityId, string $menuKey): bool
    {
        return $user->notifications()
            ->where('data->activity_id', $activityId)
            ->where('data->menu_key', $menuKey)
            ->exists();
    }
}

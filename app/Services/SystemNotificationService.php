<?php

namespace App\Services;

use App\Models\LaporanAmi;
use App\Models\ManajemenDokumen;
use App\Models\User;
use App\Notifications\SystemMenuNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
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

        // Fetch notifications dynamically in real-time to prevent cached state synchronization bugs
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
            'route_name' => $data['route_name'] ?? null,
            'route_params' => $data['route_params'] ?? [],
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

        $users = $this->resolveRecipients($module);
        $causer = $activity->causer;
        if ($causer instanceof User && $this->moduleAllowsUser($module, $causer)) {
            $users = $users->push($causer)->unique('id')->values();
        }
        if ($users->isEmpty()) {
            return false;
        }

        $subject = $activity->subject;
        $event = $activity->event ?? $activity->description;
        $eventLabel = $this->eventLabel($event);
        $subjectName = $this->resolveSubjectName($subject, $activity->subject_type, $activity->properties?->toArray());
        $title = $module['label'] ?? 'Notifikasi';
        $message = $subjectName
            ? "{$title} '{$subjectName}' {$eventLabel}."
            : "{$title} {$eventLabel}.";

        $actorRoles = $causer && method_exists($causer, 'getRoleNames')
            ? $causer->getRoleNames()->implode(', ')
            : '-';
        $color = $module['color'] ?? 'blue';

        foreach ($users as $user) {
            if ($this->notificationExists($user, $activity->id, $moduleKey)) {
                continue;
            }

            $routeName = $this->resolveRouteName($module, $user);
            $routeParams = $this->resolveRouteParams($module, $user);
            $link = $this->resolveLink($routeName, $routeParams);

            $payload = [
                'activity_id' => $activity->id,
                'menu_key' => $moduleKey,
                'menu_label' => $title,
                'title' => $title,
                'message' => $message,
                'event' => $event,
                'link' => $link,
                'route_name' => $routeName,
                'route_params' => $routeParams,
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

    public function notifyFromModelChange(Model $model, string $event, ?User $causer = null): bool
    {
        $moduleKey = $this->resolveModuleKeyForModel($model);
        if (! $moduleKey) {
            return false;
        }

        $module = config('notification_menus.modules.' . $moduleKey);
        if (! $module) {
            return false;
        }

        $users = $this->resolveRecipients($module);
        if ($causer instanceof User && $this->moduleAllowsUser($module, $causer)) {
            $users = $users->push($causer)->unique('id')->values();
        }
        if ($users->isEmpty()) {
            return false;
        }

        $subjectName = $this->resolveSubjectName($model, $model::class, null);
        $eventLabel = $this->eventLabel($event);
        $title = $module['label'] ?? 'Notifikasi';
        $message = $subjectName
            ? "{$title} '{$subjectName}' {$eventLabel}."
            : "{$title} {$eventLabel}.";

        $actorRoles = $causer && method_exists($causer, 'getRoleNames')
            ? $causer->getRoleNames()->implode(', ')
            : '-';
        $color = $module['color'] ?? 'blue';

        foreach ($users as $user) {
            $routeName = $this->resolveRouteName($module, $user);
            $routeParams = $this->resolveRouteParams($module, $user);
            $link = $this->resolveLink($routeName, $routeParams);

            $payload = [
                'menu_key' => $moduleKey,
                'menu_label' => $title,
                'title' => $title,
                'message' => $message,
                'event' => $event,
                'link' => $link,
                'route_name' => $routeName,
                'route_params' => $routeParams,
                'color' => $color,
                'actor_name' => $causer?->name ?? '-',
                'actor_role' => $actorRoles ?: '-',
                'subject_type' => $model::class,
                'subject_id' => $model->getKey(),
            ];

            $user->notify(new SystemMenuNotification($payload));
        }

        return true;
    }

    public function allowedMenuKeys(User $user): array
    {
        $modules = config('notification_menus.modules', []);

        // Admin has complete bypass access to see notifications from all system menus and groups
        if ($user->hasRole('Admin')) {
            return array_keys($modules);
        }

        return collect($modules)
            ->filter(fn ($module) => $this->moduleAllowsUser($module, $user))
            ->keys()
            ->values()
            ->all();
    }

    public function resolveModuleKey(Activity $activity): ?string
    {
        $subjectType = $activity->subject_type;
        $map = config('notification_menus.model_map', []);

        if ($subjectType === User::class) {
            $subject = $activity->subject instanceof User ? $activity->subject : null;
            $attrs = [];
            if (! $subject) {
                $attrs = $activity->properties['old'] ?? $activity->properties['attributes'] ?? [];
            }

            return $this->resolveUserModuleKey($subject, $attrs);
        }

        return $map[$subjectType] ?? null;
    }

    private function resolveModuleKeyForModel(Model $model): ?string
    {
        $map = config('notification_menus.model_map', []);

        if ($model instanceof User) {
            return $this->resolveUserModuleKey($model, null);
        }

        return $map[$model::class] ?? null;
    }

    private function resolveUserModuleKey(?User $subject, ?array $attrs): ?string
    {
        $isPortal = false;

        if ($subject) {
            if (method_exists($subject, 'getRoleNames')) {
                $roles = $subject->getRoleNames()->toArray();
                $isPortal = count(array_intersect($roles, self::PORTAL_ROLES)) > 0;
            }
            if (! $isPortal) {
                $isPortal = $subject->auditee_id || $subject->auditor_id || $subject->auditee_pusat_id || $subject->unit_penunjang_id;
            }
        } else {
            $attrs = $attrs ?? [];
            $isPortal = !empty($attrs['auditee_id']) || !empty($attrs['auditor_id']) || !empty($attrs['auditee_pusat_id']) || !empty($attrs['unit_penunjang_id']);
        }

        return $isPortal ? 'pengaturan_pengguna_portal' : 'pengaturan_pengguna_backoffice';
    }

    private function resolveRouteName(array $module, User $user): ?string
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

        return $routeName;
    }

    private function resolveRouteParams(array $module, User $user): array
    {
        $params = $module['route_params'] ?? [];
        $paramsByRole = $module['route_params_by_role'] ?? [];

        if (count($paramsByRole) > 0) {
            foreach ($user->getRoleNames() as $role) {
                if (isset($paramsByRole[$role])) {
                    $params = $paramsByRole[$role];
                    break;
                }
            }
        }

        return is_array($params) ? $params : [];
    }

    private function resolveLink(?string $routeName, array $routeParams): ?string
    {
        if (! $routeName || ! Route::has($routeName)) {
            return null;
        }

        return route($routeName, $routeParams);
    }

    private function resolveRecipients(array $module)
    {
        $roles = Arr::wrap($module['roles'] ?? []);
        $roles = array_values(array_filter($roles, fn ($role) => $role !== '' && $role !== null));

        if ($this->rolesCoverAllUsers($roles)) {
            return User::query()->get();
        }

        if (count($roles) === 0) {
            return collect();
        }

        return User::role($roles)->get();
    }

    private function moduleAllowsUser(array $module, User $user): bool
    {
        $roles = Arr::wrap($module['roles'] ?? []);
        $roles = array_values(array_filter($roles, fn ($role) => $role !== '' && $role !== null));

        if ($this->rolesCoverAllUsers($roles)) {
            return true;
        }

        if (count($roles) === 0) {
            return false;
        }

        $userRoles = $user->getRoleNames()->toArray();

        return count(array_intersect($userRoles, $roles)) > 0;
    }

    private function rolesCoverAllUsers(array $roles): bool
    {
        if (count($roles) === 0) {
            return false;
        }

        if (in_array('*', $roles, true) || in_array('All', $roles, true) || in_array('Semua', $roles, true)) {
            return true;
        }

        $allRoles = Role::query()->pluck('name')->values()->all();

        return count($allRoles) > 0 && count(array_diff($allRoles, $roles)) === 0;
    }

    private function resolveSubjectName(mixed $subject, ?string $subjectType, ?array $properties = null): ?string
    {
        $data = $subject;
        if (! $data && $properties && isset($properties['old'])) {
            $data = (object) $properties['old'];
        }

        if (! $data) {
            return null;
        }

        if ($data instanceof LaporanAmi || (is_object($data) && isset($data->auditee_id) && !isset($data->nama_auditee) && $subjectType === LaporanAmi::class)) {
            $auditeeId = $data->auditee_id ?? null;
            if ($auditeeId) {
                $auditee = \App\Models\Auditee::find($auditeeId, ['nama_auditee']);
                if ($auditee) {
                    return $auditee->nama_auditee;
                }
            }
            return $data->auditee?->nama_auditee ?? null;
        }

        if ($data instanceof ManajemenDokumen || (is_object($data) && $subjectType === ManajemenDokumen::class)) {
            return $data->nama_dokumen ?? null;
        }

        foreach (['nama_auditee', 'nama_unit', 'nama_lembaga', 'nama_kategori', 'nama_jenis', 'nama_standar', 'nama', 'kode'] as $field) {
            if (isset($data->{$field}) && $data->{$field}) {
                return (string) $data->{$field};
            }
        }

        if ($subjectType === User::class) {
            return $data->name ?? null;
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

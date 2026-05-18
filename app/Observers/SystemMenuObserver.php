<?php

namespace App\Observers;

use App\Models\User;
use App\Services\SystemNotificationService;
use Illuminate\Database\Eloquent\Model;

class SystemMenuObserver
{
    public function __construct(private readonly SystemNotificationService $service)
    {
    }

    public function created(Model $model): void
    {
        $this->notify($model, 'created');
    }

    public function updated(Model $model): void
    {
        $this->notify($model, 'updated');
    }

    public function deleted(Model $model): void
    {
        $this->notify($model, 'deleted');
    }

    private function notify(Model $model, string $event): void
    {
        $causer = auth()->user();

        $this->service->notifyFromModelChange(
            $model,
            $event,
            $causer instanceof User ? $causer : null,
        );
    }
}

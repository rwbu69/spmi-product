<?php

namespace App\Listeners;

use App\Services\SystemNotificationService;
use Spatie\Activitylog\Events\ActivityLogged;

class SendMenuNotification
{
    public function __construct(private readonly SystemNotificationService $service)
    {
    }

    public function handle(ActivityLogged $event): void
    {
        $this->service->notifyFromActivity($event->activity);
    }
}

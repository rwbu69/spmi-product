<?php

namespace App\Models\Concerns;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

trait LogsMenuActivity
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll();
    }

    public function beforeActivityLogged(\Illuminate\Database\Eloquent\Model $activity, string $eventName)
    {
        // Completely exclude user_agent and ip properties
        $props = $activity->properties ? $activity->properties->toArray() : [];
        unset($props['ip']);
        unset($props['user_agent']);
        $activity->properties = collect($props);

        // Populate data_lama and data_baru JSON columns
        if ($eventName === 'created') {
            $activity->data_lama = null;
            $activity->data_baru = json_encode($this->getAttributes());
        } elseif ($eventName === 'updated') {
            $activity->data_lama = json_encode($this->getOriginal());
            $activity->data_baru = json_encode($this->getAttributes());
        } elseif ($eventName === 'deleted') {
            $activity->data_lama = json_encode($this->getOriginal() ?: $this->getAttributes());
            $activity->data_baru = null;
        }
    }
}

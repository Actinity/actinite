<?php

namespace Actinity\Actinite\AppEvents;

use Illuminate\Support\Collection;

class AppEventBus
{
    protected $events = [];

    public function push(AppEvent $event)
    {
        if (app()->runningUnitTests()) {
            $this->events[] = $event;
        }
        $event->save();
    }

    public function get(string $type = null): Collection
    {
        $events = collect($this->events);

        return $type ? $events->where('type', $type) : $events;
    }

    public function flush()
    {
        $this->events = [];
    }
}

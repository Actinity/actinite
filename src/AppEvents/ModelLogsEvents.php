<?php

namespace Actinity\Actinite\AppEvents;

use Illuminate\Database\Eloquent\Model;

trait ModelLogsEvents
{
    protected $justCreated = false;

    private function handleIgnoreFieldsForLogging(array $fields): array
    {
        $ignore = array_merge($this->logIgnoreFields ?? [], [
            'updated_at',
            'created_at',
            'deleted_at',
        ]);
        $assoc = [];
        foreach ($ignore as $key) {
            $assoc[$key] = true;
        }
        foreach ($this->custom_log_handlers ?? [] as $key => $func) {
            $assoc[$key] = true;
        }

        $dirty = array_diff_key($fields, $assoc);

        foreach ($this->json_fields ?? [] as $field) {
            if (array_key_exists($field, $dirty)) {

                $old = $this->getOriginal($field);
                if (! $old) {
                    $old = '';
                } elseif (is_array($old)) {
                    $old = json_encode($old);
                } else {
                    $old = json_encode(json_decode($old));
                }
                $new = $dirty[$field] ? json_encode(json_decode($dirty[$field])) : '';

                if ($old === $new) {
                    unset($dirty[$field]);
                }
            }
        }

        foreach ($this->custom_log_handlers ?? [] as $key => $func) {
            $dirty = array_merge($dirty, $this->$func());
        }

        return $dirty;
    }

    public function getEventModelName()
    {
        if (property_exists($this, 'modelName') && $this->modelName) {
            return $this->modelName;
        }
        if (property_exists($this, 'type') && $this->type) {
            return $this->type;
        }

        return get_class($this);
    }

    public static function bootModelLogsEvents()
    {
        static::created(function ($model) {
            evt($model->getEventModelName().'.created', $model->handleIgnoreFieldsForLogging($model->getAttributes()), $model);
        });

        static::saved(function (Model $model) {
            $dirty = $model->handleIgnoreFieldsForLogging($model->getDirty());
            if (! $model->wasRecentlyCreated && count($dirty)) {
                evt($model->getEventModelName().'.edited', $dirty, $model);
            }
        });

        static::deleted(function ($model) {
            evt($model->getEventModelName().'.deleted', [], $model);
        });
    }
}

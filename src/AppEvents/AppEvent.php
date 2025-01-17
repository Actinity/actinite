<?php

namespace Actinity\Actinite\AppEvents;

use Illuminate\Database\Eloquent\Model;

class AppEvent extends Model
{
    public function setDataAttribute(array $data)
    {
        if (array_key_exists('_token', $data)) {
            unset($data['_token']);
        }
        if (array_key_exists('_method', $data)) {
            unset($data['_method']);
        }
        $this->attributes['data'] = json_encode($data);
    }

    public function getDataAttribute()
    {
        return json_decode($this->attributes['data'],true);
    }
}

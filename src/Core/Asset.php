<?php

namespace Actinity\Actinite\Core;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Asset extends Model
{
	protected $table = 'ac_assets';

	protected $casts = [
		'sizes' => 'array',
	];

	protected $attributes = [
		'sizes' => '[]',
	];

    public static function boot()
    {
        parent::boot();

        self::saving(function($model) {
            if(!$model->uuid) {
                $model->uuid = Uuid::uuid4()->toString();
            }
        });
    }

    public function resolveRouteBinding($value, $field = null)
    {
        if((int) $value == $value) {
            return Asset::find($value);
        }
        return Asset::where('uuid',$value)->first();
    }

    public function getPathAttribute()
    {
        return implode('/',[
            '',
            'assets',
            $this->id,
            $this->sha,
            $this->file_name,
        ]);
    }

    public function getDirectoryAttribute()
    {
        return implode('/',[
            'assets',
            $this->id,
            $this->sha,
        ]);
    }

    public function getLegacyPathAttribute()
    {
        return $this->attributes['path'];
    }

    public function toArray()
    {
        $arr = parent::toArray();
        $arr['path'] = $this->path;
        return $arr;
    }
}

<?php
namespace Actinity\Actinite\Services;

use Vimeo\Vimeo;

class VimeoService
{
    public static function api(): Vimeo
    {
        if(!config('services.vimeo.id')) {
            throw new \Exception('Vimeo is not configured');
        }

        return new Vimeo(
            config('services.vimeo.id'),
            config('services.vimeo.secret'),
            config('services.vimeo.token')
        );
    }
}
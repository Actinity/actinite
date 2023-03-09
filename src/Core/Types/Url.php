<?php
namespace Actinity\Actinite\Core\Types;

use Actinity\Actinite\Core\Node;

class Url
    extends Node
{
    public string $icon = 'fas fa-link';

    public function getUrlAttribute()
    {
        return $this->data->url;
    }

    public function fields(): array
    {
        return [
            ['name' => 'url','label' => 'URL','type' => 'url','required' => true],
        ];
    }

}

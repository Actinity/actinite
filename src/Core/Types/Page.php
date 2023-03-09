<?php
namespace Actinity\Actinite\Core\Types;

use Actinity\Actinite\Core\Node;

class Page
    extends Node
{
    public string $icon = 'fas fa-file-alt';

    public function childTypes(): array
    {
        return [
            Page::class,
        ];
    }

    public function fields(): array
    {
        return [
            ['name' => 'title','label' => 'Title', 'type' => 'text'],
            ['name' => 'body','label' => 'Body','type' => 'html','required' => true],
        ];
    }

    public function pageTemplates(): array
    {
        return [
            'simple',
        ];
    }

    public function getSearchBody(): string
    {
        return $this->data->title." ".$this->data->body;
    }

}

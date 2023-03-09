<?php
namespace Actinity\Actinite\Core\Types;

use Actinity\Actinite\Core\Node;
use Actinity\Actinite\Core\NodeFactory;

class NodeList
    extends Node
{
    public string $icon = 'fas fa-list';

    public function getChildrenAttribute()
    {
        return NodeFactory::nodes($this->data->nodes);
    }

    public function fields(): array
    {
        return [
            ['name' => 'nodes','label' => 'Nodes','type' => 'nodelist'],
        ];
    }
}

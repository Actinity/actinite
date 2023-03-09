<?php

namespace Actinity\Actinite\Core\Events;

use Actinity\Actinite\Core\Node;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NodeEdited
{
    use Dispatchable, SerializesModels;

    public $node;

    public function __construct(Node $node)
    {
        $this->node = $node;
    }
}

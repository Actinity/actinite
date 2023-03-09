<?php

namespace Actinity\Actinite\Core\Events;

use Illuminate\Foundation\Events\Dispatchable;

class NodeUnpublished
{
    use Dispatchable;

    public $nodeId;

    public function __construct(int $nodeId)
    {
        $this->nodeId = $nodeId;
    }
}

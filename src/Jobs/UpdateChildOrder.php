<?php

namespace Actinity\Actinite\Jobs;

use Actinity\Actinite\Core\Node;
use Actinity\Actinite\Services\TypeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateChildOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $node;

    public function __construct(Node $node)
    {
        $this->node = $node;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if(TypeService::isArchive($this->node)) {
            return;
        }
        $this->node->child_order = $this->node->children->pluck('id')->all();
        $this->node->save();
    }
}

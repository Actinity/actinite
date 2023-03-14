<?php

namespace Actinity\Actinite\Jobs;

use Actinity\Actinite\Services\TreeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

class RebuildTree implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $published;
    private $root;
    private $left;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(bool $published = false, $root = null, $left = 0)
    {
        $this->published = $published;
        $this->root = $root;
        $this->left = $left;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        TreeService::rebuild($this->root,$this->left,$this->published ? 'ac_published_nodes' : 'ac_nodes');
    }
}

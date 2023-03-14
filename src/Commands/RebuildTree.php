<?php
namespace Actinity\Actinite\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RebuildTree
    extends Command
{

    protected $signature = 'actinite:rebuild-tree {--published}';

    protected $description = 'Rebuild tree';

    public function handle()
    {
        DB::table($this->option('published') ? 'ac_published_nodes' : 'ac_nodes')
            ->whereNotNull('deleted_at')
            ->update(['lft' => 0,'rgt' => 0]);

        dispatch_sync(new \Actinity\Actinite\Jobs\RebuildTree($this->option('published')));
    }
}

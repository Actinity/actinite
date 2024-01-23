<?php
namespace Actinity\Actinite\Commands;

use Actinity\Actinite\Core\Events\NodePublished;
use Actinity\Actinite\Core\Node;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class PublishOne
    extends Command
{
    protected $signature = 'actinite:publish-one {node}';

    protected $description = 'Publish one node';

    public function handle()
    {
        $nodeId = $this->argument('node');

        $node = Node::find($nodeId);

        if(!$node) {
            return;
        }

        $node->published_sha = $node->current_sha;
        $node->published_at = $node->published_at ?: now();
        $node->save();

        $data = (array) DB::table('ac_nodes')->find($nodeId);

        DB::table('ac_published_nodes')
            ->updateOrInsert([
                'id' => $nodeId
            ],$data);

        // Nodes are responsible for the order of their
        // children, so update that as well.
        foreach($node->child_order as $idx => $childId) {
            DB::table('ac_published_nodes')
                ->where('id','=',$childId)
                ->update(['ordering' => $idx + 1]);
        }

        Artisan::call('actinite:purge');

        event(new NodePublished($nodeId));
    }
}

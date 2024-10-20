<?php
namespace Actinity\Actinite\Http\Controllers;

use Actinity\Actinite\Core\Events\NodeUnpublished;
use Actinity\Actinite\Core\Node;
use Actinity\Actinite\Jobs\RebuildTree;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class PublishController
    extends Controller
{
    public function switchMode($mode, Request $request)
    {
        if($mode === 'draft') {
            session()->put('actinite:draft',true);
        } else {
            session()->forget('actinite:draft');
        }

        return redirect($request->return_to ?: '/');
    }

    public function publish(Request $request)
    {
        $this->validate($request,[
            'nodes' => 'required|array',
        ]);

        foreach($request->nodes as $id) {
            Artisan::call('actinite:publish-one '.$id);
        }

        dispatch(new RebuildTree(true));

        return Node::whereIn('id',$request->nodes)
            ->select('id','published_at','current_sha','published_sha')
            ->get();
    }

    public function publishAll(Request $request)
    {
        Artisan::call('actinite:publish');
        session()->forget('actinite:draft');

        dispatch(new RebuildTree(true));

        return redirect($request->return_to ?: '/');
    }

    public function unpublish(Node $node)
    {
        if($node->parent_id < 1 || $node->is_protected) {
            return;
        }

        DB::table('ac_published_nodes')
            ->where('id','=',$node->id)
            ->delete();

        $node->published_at = null;
        $node->published_sha = null;
        $node->save();

        event(new NodeUnpublished($node->id));
    }


}

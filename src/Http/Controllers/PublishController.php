<?php
namespace Actinity\Actinite\Http\Controllers;

use Actinity\Actinite\Core\Events\NodeUnpublished;
use Actinity\Actinite\Core\Node;
use Actinity\Actinite\Jobs\Publish;
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
            'node' => 'required|integer',
        ]);

        evt('publish',[
            'deep' => $request->get('deep'),
            'node' => $request->get('node'),
        ]);

        if($request->deep) {
            dispatch_sync((new Publish($request->node)));
        } else {
            Artisan::call('actinite:publish-one '.$request->node);

            dispatch(new RebuildTree(true));
        }

        return response()->noContent();
    }

    public function publishAll(Request $request)
    {
        Artisan::call('actinite:publish');
        session()->forget('actinite:draft');

        evt('publish-all');

        dispatch(new RebuildTree(true));

        return redirect($request->return_to ?: '/');
    }

    public function unpublish(Node $node)
    {
        if($node->parent_id < 1 || $node->is_protected) {
            return;
        }

        evt('unpublish',['node' => $node->id]);

        DB::table('ac_published_nodes')
            ->where('id','=',$node->id)
            ->delete();

        $node->published_at = null;
        $node->published_sha = null;
        $node->save();

        event(new NodeUnpublished($node->id));
    }


}

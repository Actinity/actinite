<?php

namespace Actinity\Actinite\Observers;

use Actinity\Actinite\Core\Events\NodeCreated;
use Actinity\Actinite\Core\Events\NodeDeleted;
use Actinity\Actinite\Core\Events\NodeEdited;
use Actinity\Actinite\Core\Node;
use Actinity\Actinite\Core\NodeFactory;
use Actinity\Actinite\Jobs\UpdateChildOrder;
use Actinity\Actinite\Services\AssetService;
use Illuminate\Support\Facades\DB;

class NodeObserver
{
    public function saving(Node $node)
    {
        $data = [
            $node->type,
            $node->name,
            $node->slug,
            $node->page_template,
            $node->parent_id,
            $node->deleted_at,
            json_encode($node->data),
            json_encode($node->child_order),
        ];

        $node->current_sha = sha1(implode('|', $data));

        $node->asset_cache = AssetService::scanForAssets($node);
    }

    public function saved(Node $node)
    {
        $fields = array_flip([
            'type',
            'name',
            'slug',
            'page_template',
            'parent_id',
            'data',
            'deleted_at',
            'is_ready',
            'is_locked',
            'is_protected',
            'child_order',
        ]);

        $dirty = array_intersect_key($node->getDirty(),$fields);
        unset($dirty['data']);

        $original_data = json_decode($node->getOriginal('data') ?: '{}',true);

        $json_keys = array_keys($original_data);
        $data = $node->data->toArray();
        $json_keys = array_merge(array_keys($data),$json_keys);
        $json_keys = array_unique($json_keys);

        $dirty_data = false;

        foreach($json_keys as $key) {
            if(($original_data[$key] ?? false) != ($data[$key] ?? false)) {
                $dirty_data = true;
                break;
            }
        }

        if(count($dirty) || $dirty_data) {

            $data = array_intersect_key($node->attributesToArray(),$fields);
            $data['data'] = $node->getAttribute('data');

            foreach(['child_order'] as $arrayField) {
                if(array_key_exists($arrayField,$data)) {
                    $data[$arrayField] = json_encode($data[$arrayField]);
                }
            }

            DB::table('ac_node_snapshots')
                ->insert(array_merge([
                    'node_id' => $node->id,
                    'user_id' => auth()->user() ? auth()->user()->id : null,
                    'created_at' => now(),
                ],$data));

            if($node->wasRecentlyCreated) {
                event(new NodeCreated($node));
            } else {
                event(new NodeEdited($node));
            }
        }
    }

    public function deleted(Node $node)
    {
        dispatch(new UpdateChildOrder(NodeFactory::get($node->parent_id)));

        event(new NodeDeleted($node->id));
    }
}

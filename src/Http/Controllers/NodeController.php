<?php
namespace Actinity\Actinite\Http\Controllers;

use Actinity\Actinite\Core\NodeFactory;
use Actinity\Actinite\Services\NodeManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NodeController
    extends Controller
{
    public function keepalive()
    {
        return response()->noContent();
    }

    public function move(Request $request)
    {
        $this->validate($request,[
            'nodes' => 'required|array',
			'nodes.*' => 'required|int',
            'parent' => 'required|int',
        ]);

        NodeManager::move($request->nodes,$request->parent);

        return ['success' => true];
    }

    public function show($id)
    {
        $id = (int) $id;
        if(!$id) {
            abort(404);
        }

        $node = NodeFactory::get($id);

        if(!$node) {
            throw new NotFoundHttpException();
        }

        // TODO: Duplicated in TreeController

        if(auth()->user()->restrict_to_nodes) {
            $found = in_array($node->id,auth()->user()->restrict_to_nodes);
            foreach($node->parents as $parent) {
                if(in_array($parent->id,auth()->user()->restrict_to_nodes)) {
                    $found = true;
                }
            }

            if(!$found) {
                abort(401,'Node not accessible');
            }
        }


        $relations = [];
        $related = [];

        $relationInfo = collect(config('actinite.relations'));

        foreach($relationInfo->where('source','=',$node->type) as $sourceRelation) {
            $relations[] = [
                'name' => $sourceRelation['name'],
                'is_source' => true,
                'allow_multiple' => false,
                'related_type' => $sourceRelation['target'],
            ];

            $related = array_merge($related,$this->addRelated($sourceRelation['name'],$sourceRelation['target'],NodeFactory::relatedToId($id,$sourceRelation['name'])));
        }

        foreach($relationInfo->where('target','=',$node->type) as $targetRelation) {
            $relations[] = [
                'name' => $targetRelation['name'],
                'is_source' => false,
                'allow_multiple' => true,
                'related_type' => $targetRelation['source'],
            ];

            $related = array_merge($related,$this->addRelated($targetRelation['name'],$targetRelation['source'],NodeFactory::relatedFromId($id,$targetRelation['name'])));
        }

        $response = $node->toArray();

        $type = app($node->type);
        foreach($type->fields() as $field) {
            $response['data'][$field['name']] = $response['data'][$field['name']] ?? null;
        }


        $response['ac_related'] = $related;
        $response['ac_relations'] = $relations;

        return $response;
    }

    private function addRelated($name,$type,$nodes)
    {
        $related = [];
        foreach($nodes as $node) {
            $related[] = array_merge([
                'id' => $node->id,
                'type' => $node->type,
                'name' => $node->name,
                'parent_id' => $node->parent__id,
                'published_at' => $node->published_at,
                'current_sha' => $node->current_sha,
                'published_sha' => $node->published_sha,
                'relation_name' => $name,
                'relation' => 'source',
                'relation_type' => $type,
            ]);
        }
        return $related;
    }

    public function store(Request $request)
    {
        $node = NodeManager::create($request->all(),$request->parent_id);
        return $this->show($node->id);
    }

    public function order($node, Request $request)
    {
        NodeManager::setChildOrder($node,$request->all());
    }

    public function update($id,Request $request)
    {
        $node = NodeFactory::get($id);

        if(!$node) {
            abort(404);
        }

        $data = $request->get('data');
        $node->editing = true;

        if ($data) {
            foreach ($data as $key => $value) {
                $node->setDataValue($key, $value);
            }
        }

        $node->name = $request->name;
        $node->published_at = $request->published_at;
        $node->slug = $request->slug ?: '';
        $node->page_template = $request->page_template;

        $node->save();

        $relations = collect(config('actinite.relations'))->where('source',get_class($node));

        foreach($relations as $relation) {
            $value = $node->data->{$relation['field']};

            $base = [
                'source' => $node->id,
                'key' => $relation['name'],
            ];

            if($value) {
                DB::table('ac_node_relations')
                    ->upsert(array_merge($base,['target' => $value,'created_at' => now()]), $base,['source','target','key',]);
            } else {
                DB::table('ac_node_relations')
                    ->where($base)
                    ->delete();
            }
        }

        return $this->show($node->id);
    }

    public function trash($node)
    {
        $node = NodeFactory::get($node);
        if($node) {
            $node->delete();
        }
        return response()->noContent();
    }
}

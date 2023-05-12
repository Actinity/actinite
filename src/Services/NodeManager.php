<?php
namespace Actinity\Actinite\Services;

use Actinity\Actinite\Core\Node;
use Actinity\Actinite\Core\NodeFactory;
use Actinity\Actinite\Jobs\RebuildTree;
use Actinity\Actinite\Jobs\UpdateChildOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NodeManager
{
    public static function setChildOrder(int $parent_id, array $childIds)
    {
        $nodes = collect(NodeFactory::nodes($childIds));

        foreach($childIds as $idx => $id) {
            $node = $nodes->where('id','=',$id)->first();
            if($node) {
                $node->ordering = $idx + 1;
                $node->save();
            }
        }

        $parent = Node::find($parent_id);

        dispatch_sync(new UpdateChildOrder($parent));

        dispatch(new RebuildTree(false,$parent->id,$parent->lft));
    }

    public static function move(array $nodeIds, int $parentId)
    {
        if(in_array($parentId,$nodeIds)) {
            return; // A node can't be its own parent
        }

		foreach($nodeIds as $nodeId) {
			$node = NodeFactory::get($nodeId);

			if($node->parent_id === $parentId) {
				continue;
			}

			$parent = NodeFactory::get($parentId);

			if(!in_array($node->type,$parent->childTypes())) {
				throw new \Exception('Target does not allow that node type here');
			}

			$node->parent_id = $parentId;
			$node->ordering = $parent->children->max('ordering') + 1;
			$node->save();
		}


        dispatch_sync(new RebuildTree());
    }

    public static function create(array $data, int $parent_id): Node
    {
        $parent = NodeFactory::get($parent_id);
        $parent->editing = true;

        $new = new $data['type'];
        if(!is_a($new,Node::class)) {
            throw new \Exception('Not a node type: '.$data['type']);
        }
        $new->editing = true;
        $new->populateDefaultData();
        $new->parent_id = $parent->id;
        $new->type = $data['type'];
        $new->name = $data['name'];
        $new->slug = SlugFarm::generate($data['name'],$parent);
        $new->path = ($parent->path ? $parent->path."/" : "").$new->slug;
        $new->page_template = $data['page_template'] ?? null;
        $new->is_ready = $parent->is_ready;

        $updateAfter = $parent->rgt;

        if($data['before'] ?? false) {

            $sibling = NodeFactory::get($data['before']);

            $new->ordering = max(0,$sibling->ordering - 1);
            $new->lft = $sibling->lft;
            $new->rgt = $sibling->lft + 1;

            DB::statement("update ac_nodes set ordering = ordering + 1 where parent_id = ? and ordering > ?",[
                $parent->id,
                $sibling->ordering - 1,
            ]);

            $updateAfter = $sibling->lft;


        } else if($data['after'] ?? false) {

            $sibling = NodeFactory::get($data['after']);
            $new->ordering = $sibling->ordering + 1;
            $new->lft = $sibling->rgt + 1;
            $new->rgt = $sibling->rgt + 2;

            DB::statement("update ac_nodes set ordering = ordering + 1 where parent_id = ? and ordering > ?",[
                $parent->id,
                $sibling->ordering,
            ]);

            $updateAfter = $new->lft;

        } else {
            $new->ordering = count($parent->children);
            $new->lft = $parent->rgt;
            $new->rgt = $parent->rgt + 1;
        }

        if($data['related'] ?? false) {
            $relation = collect(config('actinite.relations'))
                ->where('source',$new->type)
                ->where('name',$data['related']['relationName'])
                ->first();

            if($relation && $relation['field']) {
                $new->setDataValue($relation['field'],$data['related']['target']);
            }
        }

        foreach($new->inherits() as $rule) {
            if($related_id = $new->data->{$rule['foreign']}) {
                $obj = NodeFactory::get($related_id);
                if($obj && $value = $obj->data->{$rule['from_field']}) {
                    $new->setDataValue($rule['to_field'],$value);
                }
            }
        }

        $new->save();

        DB::statement("update ac_nodes set rgt = rgt + 2 where id != ? and rgt >= ?",[$new->id,$updateAfter]);
        DB::statement("update ac_nodes set lft = lft + 2 where id != ? and lft >= ?",[$new->id,$updateAfter]);

        if($data['related'] ?? false) {
            DB::table('ac_node_relations')
                ->insert([
                    'target' => $data['related']['target'],
                    'key' => $data['related']['relationName'],
                    'source' => $new->id,
                    'created_at' => now(),
                ]);
        }


        dispatch(new UpdateChildOrder($parent));
        return NodeFactory::get($new->id);
    }
}

<?php
namespace Actinity\Actinite\Http\Controllers;

use Actinity\Actinite\Core\Node;
use Actinity\Actinite\Core\NodeFactory;
use Actinity\Actinite\Services\TypeService;
use Illuminate\Database\Eloquent\Builder;

class TreeController
    extends Controller
{
    public function from($node = null)
    {
        if($node && !(int) $node) {
            abort(404);
        }
        $query = $this->getQuery();

        if ($node) {
            $query->where('parent_id', '=', $node);
        } else if(auth()->user()->restrict_to_nodes && !request()->get('unrestricted')) {
            $query->whereIn('id',auth()->user()->restrict_to_nodes);
        } else {
            $query->whereNull('parent_id');
        }

        $is_archive = false;

        if($node) {
            $is_archive = TypeService::isArchive(NodeFactory::get($node));
            $node = $this->getQuery()->where('id','=',$node)->first();
        }

        if($is_archive) {
            $query->orderBy('published_at','desc');
        } else {
            $query->orderBy('lft');
        }

        return [
            'nodes' => $query->get(),
            'parent' => $node];
    }


    public function to($node = null)
    {
        if($node && !(int) $node) {
            abort(404);
        }
        $node = NodeFactory::get($node);
        if(!$node) {
            abort(404,'Node not found');
        }

        // TODO: Duplicated in NodeController

        if(!request()->get('unrestricted')) {
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
        }


        $query = $this->getQuery();


        $query->whereRaw("(parent_id is null or parent_id in (select id from ac_nodes where lft < {$node->lft} and rgt > {$node->rgt}))");

        $nodes = $query->orderBy('lft')->get();

        $open = $nodes->where('lft', '<', $node->lft)
            ->where('rgt', '>', $node->rgt)
            ->sortBy('lft')
            ->pluck('id');

        return [
            'open' => $open,
            'nodes' => $nodes,
        ];
    }

    private function getQuery(): Builder
    {
        return Node::select('id', 'name', 'parent_id', 'lft', 'rgt', 'published_at', 'published_sha', 'current_sha', 'page_template', 'type', 'ordering','is_ready');
    }
}

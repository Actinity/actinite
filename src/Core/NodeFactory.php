<?php
namespace Actinity\Actinite\Core;

use Actinity\Actinite\Services\TypeService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NodeFactory
{
    public static function url($path): ?Node
    {
        return static::path($path,true);
    }

    public static function path($path,$require_page = false): ?Node
    {
        $query = DB::table(actinite_table('nodes'))
            ->whereNull('deleted_at')
            ->where('path','=',$path);

        if($require_page) {
            $query->whereNotNull('page_template');
        }

        if(!$raw = $query->first()) {
            return null;
        }

        return static::hydrateOne($raw);
    }

    public static function get(?int $id = null): ?Node
    {
        if(!$id) {
            return null;
        }

        $raw = Node::where('id','=',$id)
            ->first();

        return $raw ? static::hydrateOne($raw) : null;
    }

    public static function hydrateOne($raw)
    {
        $obj = app()->make($raw->type);
        $obj->fill(is_a($raw,Node::class) ? $raw->attributesToArray() : (array) $raw);
        $obj->exists = true;
        $obj->syncOriginal();
        return $obj;
    }

    public static function nodes(array $ids)
    {

        $result = Node::whereIn('id',$ids)
            ->get();

        $return = [];

        /**
         * Ensure that the results are in the same
         * order that they were requested
         */

        foreach($result as $row) {
            $return[array_search($row->id,$ids)] = static::hydrateOne($row);
        }

        ksort($return);
        return array_values($return);

    }

    public static function query(array $args, $loadAssets = true): Collection
    {
        $return = [];
        $query = Node::query();

        if($args['order_by'] ?? false) {
            $query->orderByRaw($args['order_by']);
        }  else {
            $query->orderBy('ordering');
        }

        if($args['after'] ?? false) {
            if(is_int($args['after'])) {
                $query->where('id','>',$args['after']);
            } else {
                $query->where('published_at','>',$args['after']);
            }
        }

        if($args['before'] ?? false) {
            if(is_int($args['before'])) {
                $query->where('id','<',$args['before']);
            } else {
                $query->where('published_at','<',$args['before']);
            }
        }

        if(array_key_exists('type',$args)) {
            if(is_array($args['type'])) {
                $query->whereIn('type',$args['type']);
            } else {
                $query->where('type','=',$args['type']);
            }
        }

        if(array_key_exists('parent',$args)) {
            $parent = $args['parent'];
            if(!$parent) {
                $query->whereNull('parent_id');
            } else if(is_int($parent)) {
                $query->where('parent_id','=',$parent);
            } else {
                if($args['deep'] ?? false) {
                    $query->where('lft','>',$parent->lft)
                        ->where('rgt','<',$parent->rgt);

                    if($args['parents'] ?? false) {
                        $query->whereIn('parent_id',$args['parents']->pluck('id'));
                    }
                } else {
                    $query->where('parent_id','=',$parent->id);
                }
            }
        }

        if($args['take'] ?? false) {
            $query->take($args['take']);
        }

        if(array_key_exists('routable',$args)) {
            if($args['routable']) {
                $query->whereNotNull('page_template');
            } else {
                $query->whereNull('page_template');
            }
        }

        if(array_key_exists('exclude',$args)) {
            $query->whereNotIn('id',$args['exclude']);
        }

        foreach($query->get() as $node) {
            $return[] = static::hydrateOne($node);

            if($loadAssets) {
                app('AssetProvider')->queue($node->asset_cache);
            }
        }

        if($loadAssets) {
            app('AssetProvider')->loadQueue();
        }

        return collect($return);
    }

    public static function relatedToId($source_id,$key,$exclude = []): Collection
    {
        $query = DB::table(actinite_table('nodes'))
            ->join(actinite_table('relations'),'nodes.id','=','relations.target')
            ->where('relations.source','=',$source_id)
            ->where('relations.key','=',$key)
            ->whereNull('nodes.deleted_at')
            ->select('nodes.*');

        if(count($exclude)) {
            $query->whereNotIn('nodes.id',$exclude);
        }

        $return = [];
        foreach($query->get() as $row) {
            $return[] = static::hydrateOne($row);
        }
        return collect($return);
    }

    public static function relatedFromId($target_id,$key,$exclude = [],$limit = null): Collection
    {
        $query = DB::table(actinite_table('nodes'))
            ->join(actinite_table('relations'),'nodes.id','=','relations.source')
            ->where('relations.target','=',$target_id)
            ->where('relations.key','=',$key)
            ->whereNull('nodes.deleted_at')
            ->select('nodes.*');

        if($limit) {
            $query->limit($limit);
        }

        if(count($exclude)) {
            $query->whereNotIn('nodes.id',$exclude);
        }

        $return = [];
        foreach($query->get() as $row) {
            $return[] = static::hydrateOne($row);
        }
        return collect($return);
    }


    public function fields($type): Collection
    {
        return collect(TypeService::forType($type)['fields']);
    }

    public function parents(Node $node): Collection
    {
        $parents = collect();

        $raw = Node::where('lft','<',$node->lft)
            ->where('rgt','>',$node->rgt)
            ->orderBy('lft')
            ->get();

        foreach($raw as $row) {
            $parents->push(static::hydrateOne($row));
        }
        return $parents->reverse();
    }
}

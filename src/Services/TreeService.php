<?php
namespace Actinity\Actinite\Services;

use Actinity\Actinite\Core\Node;
use Illuminate\Support\Facades\DB;

class TreeService
{
    public static function rootIds(): array
    {
        return Node::whereNull('parent_id')->select('id')->get()->pluck('id')->all();
    }

    public static function rebuild(?int $nodeId,int $left,$tableName = 'ac_nodes')
    {
        $right = $left + 1;

        $query = DB::table($tableName)
            ->whereNull('deleted_at')
            ->orderBy('ordering')
            ->select('id');

        if($nodeId) {
            $query->where('parent_id','=',$nodeId);
        } else {
            $query->whereNull('parent_id');
        }

        $result = $query->get();

        foreach($result as $row) {
            $right = static::rebuild($row->id, $right, $tableName);
        }
        if ($nodeId) {
            DB::table($tableName)
                ->where('id','=',$nodeId)
                ->update([
                    'lft' => $left ?: 0,
                    'rgt' => $right
                ]);
        }
        return $right+1;
    }
}

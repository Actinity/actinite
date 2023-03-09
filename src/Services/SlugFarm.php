<?php
namespace Actinity\Actinite\Services;

use Actinity\Actinite\Core\Node;
use Illuminate\Support\Str;

class SlugFarm
{
    public static function generate($name,$parent = null): string
    {
        $slug = Str::slug($name);
        $similar = static::getSimilar($slug,$parent);

        if(count($similar)) {
            $highest = 1;
            $similar = $similar->reject($slug);
            foreach($similar as $alt) {
                $highest = max($highest, (int) preg_match("/[0-9+]$/",$alt));
            }
            $slug .= "--".($highest+1);
        }

        return $slug;
    }

    private static function getSimilar($slug,$parent)
    {
        $query = Node::query()->where(function($sub) use ($slug) {
            $sub->where('slug','=',$slug)
                ->orWhere('slug','rlike',"^".$slug."--[0-9]+$");
        })
            ->select('slug')
            ->orderBy('slug');

        if($parent) {
            $query->where('parent_id',$parent->id);
        } else {
            $query->whereNull('parent_id');
        }

        return $query->get()->pluck('slug');
    }
}

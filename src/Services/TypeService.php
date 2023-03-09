<?php
namespace Actinity\Actinite\Services;

use Actinity\Actinite\Core\Node;
use Actinity\Actinite\Core\Types\PostArchive;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;
use ReflectionClass;

class TypeService
{
    public static function all(): Collection
    {
        if(config('actinite.caching')) {
            return cache()->rememberForever('actinite:types',function() {
                return static::_all();
            });
        }

        return static::_all();
    }

    private static function _all(): Collection
    {
		$core_path = __DIR__."/../Core/Types";

        return collect([
            ...static::fromFilesystem(base_path('app/Nodes'),app()->getNamespace(),app_path()),
            ...static::fromFilesystem($core_path,'Actinity\\Actinite\Core\Types\\',$core_path),
        ]);
    }

    public static function isArchive(string|Node $value): bool
    {
        $type = is_string($value) ? $value : $value->type;
        $instance = app($type);
        return is_a($instance,PostArchive::class);
    }

    public static function clearCache()
    {
        cache()->forget('actinite:types');
    }

    public static function fields(string $type): array
    {
        $match = static::all()->where('type',$type)->first();
        return $match ? $match['fields'] : [];
    }

    public static function forType(string $type)
    {
        return static::all()->where('type',$type)->first();
    }

    private static function fromFileSystem($path,$namespace,$prefix): array
    {
        $types = [];

        foreach ((new Finder)->in($path)->files() as $type) {
            $type = $namespace.str_replace(
                    ['/', '.php'],
                    ['\\', ''],
                    Str::after($type->getPathname(), $prefix.DIRECTORY_SEPARATOR)
                );

            if (is_subclass_of($type, Node::class) &&
                ! (new ReflectionClass($type))->isAbstract()) {
                $instance = app($type);

                $types[] = [
                    'type' => $type,
                    'childTypes' => $instance->childTypes(),
                    'pageTemplates' => $instance->pageTemplates(),
                    'fields' => array_map(function($field) { return array_merge(['required'=>false],$field); },$instance->fields()),
                    'icon' => $instance->icon,
                    'is_archive' => is_a($instance,PostArchive::class),
                ];
            }
        }
        return $types;
    }
}

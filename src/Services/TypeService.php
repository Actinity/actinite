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
        if(config('actinite.type_cache')) {
            return cache()->rememberForever('actinite:types',function() {
                return static::_all();
            });
        }

        return static::_all();
    }

    private static function _all(): Collection
    {
		$types = [];

		foreach((config('actinite.type_locations') ?: []) as $path => $namespace) {
			foreach(static::fromFilesystem(base_path($path),$namespace."\\") as $resolved) {
				$types[$resolved['type']] = $resolved;
			}
		}

		foreach(config('actinite.types') ?: [] as $className) {
			$types[$className] = static::typeFromClassName($className);
		}

        return collect(array_values($types));
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

	private static function typeFromClassName($className): ?array
	{
		if (is_subclass_of($className, Node::class) && !(new ReflectionClass($className))->isAbstract()) {
			$instance = app($className);

			return [
				'type' => $className,
				'childTypes' => $instance->childTypes(),
				'pageTemplates' => $instance->pageTemplates(),
				'fields' => array_map(function($field) { return array_merge(['required'=>false],$field); },$instance->fields()),
				'icon' => $instance->icon,
				'is_archive' => is_a($instance,PostArchive::class),
			];
		}

		return null;
	}

    private static function fromFileSystem($path,$namespace): array
    {
        $types = [];

        foreach ((new Finder)->in($path)->files() as $file) {
            $type = $namespace.str_replace(
                    ['/', '.php'],
                    ['\\', ''],
                    Str::after($file->getPathname(), $path.DIRECTORY_SEPARATOR)
                );

			if($data = static::typeFromClassName($type)) {
				$types[] = $data;
			}
        }
        return $types;
    }
}

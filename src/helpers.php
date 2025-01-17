<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

function actinite_table($slug) {
	if(config('actinite.mode') === 'draft') {
		return $slug === 'relations' ? DB::raw('ac_node_relations as relations') : DB::raw('ac_nodes as nodes');
	}

	return $slug === 'relations' ? DB::raw('ac_published_relations as relations') : DB::raw('ac_published_nodes as nodes');
}

function actinite_mix($path) {
	$manifest = json_decode(file_get_contents(__DIR__."/../dist/mix-manifest.json"),true);

	$path = $manifest[$path] ?? $path;

	return "/actinite/resources".$path;
}

if (! function_exists('log_json')) {
    function log_json(...$obj)
    {
        Log::info(json_encode($obj, JSON_PRETTY_PRINT));
    }
}

if (! function_exists('log_query')) {
    function log_query($query, $output = false)
    {
        if (is_a($query, \Illuminate\Database\Events\QueryExecuted::class)) {
            $sql = $query->sql;
            $bindings = $query->bindings;
        } else {
            $sql = $query->toSql();
            $bindings = $query->getBindings();
        }
        $query =
            vsprintf(str_replace('?', '%s', $sql), collect($bindings)->map(function ($binding) {
                return is_numeric($binding) ? $binding : "'{$binding}'";
            })->toArray());

        if ($output) {
            return $query;
        }

        Log::info($query);
    }
}

if(!function_exists('evt')) {



    function evt(string $type, array $data = [], $model = null)
    {
        $event = new \Actinity\Actinite\AppEvents\AppEvent;
        $event->type = $type;
        $event->data = $data;
        $event->user_id = auth()->user() ? auth()->user()->id : null;
        $event->server = gethostname();

        if ($impersonator = session()->get('impersonator')) {
            $event->impersonated_by = $impersonator['id'];
        }

        if ($model) {
            $event->eventable_type = get_class($model);
            $event->eventable_id = $model->id;
        }

        app('AppEvents')->push($event);

        return $event;
    }

}
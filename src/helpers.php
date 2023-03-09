<?php
use Illuminate\Support\Facades\DB;

function actinite_table($slug) {
	if(config('actinite.mode') === 'draft') {
		return $slug === 'relations' ? DB::raw('ac_node_relations as relations') : DB::raw('ac_nodes as nodes');
	}

	return $slug === 'relations' ? DB::raw('ac_published_relations as relations') : DB::raw('ac_published_nodes as nodes');
}
<?php
namespace Actinity\Actinite\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class Redirect extends Model
{
	protected $table = "ac_redirects";

	protected $guarded = [];

	public function node(): ? Relation
	{
		return $this->belongsTo(Node::class);
	}

	public static function repath(Node $node): ?Redirect
	{
		$original_path = $node->path;
		$parts = [$node->slug];
		$redirect = null;

		foreach($node->parents as $parent) {
			if($parent->is_routable && $parent->slug) {
				$parts[] = $parent->slug;
			}
		}

		$node->path = implode("/",array_reverse($parts));

		if($node->path !== $original_path) {
			$redirect = Redirect::create([
				'node_id' => $node->id,
				'path' => $original_path,
				'sha_path' => hash('sha256',$original_path),
			]);
		}

		$node->save();

		return $redirect;
	}
}
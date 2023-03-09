<?php
namespace Actinity\Actinite\Http\Controllers;

class ResourceController
	extends Controller
{
	public function css()
	{
		return $this->makeResponse("app.css","text/css");
	}

	public function js()
	{
		return $this->makeResponse("app.js","application/javascript");
	}

	public function tinymce($plugin)
	{
		if(strstr($plugin,".")) {
			abort(403);
		}

		return $this->makeResponse("plugins/".$plugin.".plugin.js","application/javascript");
	}

	private function makeResponse($path, $type) {
		return response()->make(
			$this->getFileFromDist($path),
			200,[
				'Content-Type' => $type
			]
		);
	}

	private function getFileFromDist($file)
	{
		return file_get_contents(__DIR__."./../../../dist/".$file);
	}
}
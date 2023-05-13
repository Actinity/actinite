<?php
namespace Actinity\Actinite\Http\Controllers;

use Actinity\Actinite\Core\Asset;
use Actinity\Actinite\Jobs\ResizeAsset;
use Actinity\Actinite\Services\AssetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class AssetController
    extends Controller
{
    public function index(Request $request)
    {
        $query = Asset::query();

        if($request->type) {
            $query->where('type','=',$request->type);
        }

        $query->whereNull('deleted_at');

        if($request->search) {
            $query->where('file_name','like','%'.$request->search.'%');
        }

        $query->orderBy('updated_at','desc');

        return $query->paginate(50);
    }

    public function show($asset)
    {
        return Asset::find($asset);
    }

    public function upload(Request $request)
    {
        $this->validate($request,[
            'file' => 'required|file|max:8192'
        ]);

        $file = $request->file('file');

        $mime = $file->getClientMimeType();
        $slice = substr($mime,0,5);

        $asset = new Asset();
        $asset->sha = sha1_file($file->getRealPath());
        $asset->extension = $file->getClientOriginalExtension();
        $asset->file_name = $file->getClientOriginalName();
        $asset = AssetService::updateFromPath($asset,$file->getRealPath());
        $asset->save();

        $file->storePubliclyAs($asset->directory,$asset->file_name);

        dispatch_sync(new ResizeAsset($asset,450));
        dispatch(new ResizeAsset($asset,600));

        return $asset;
    }

	public function update(Asset $asset,Request $request)
	{
		if($request->has('pos_x') && $request->has('pos_y')) {
			$asset->pos_x = $request->get('pos_x');
			$asset->pos_y = $request->get('pos_y');
		}

		$asset->save();

		return $asset;
	}
}

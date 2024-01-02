<?php
namespace Actinity\Actinite\Http\Controllers;

use Actinity\Actinite\Core\Asset;
use Actinity\Actinite\Services\AssetService;
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Http\Request;

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

        $query->orderBy('created_at','desc');

        return $query->paginate(50);
    }

    public function show(Asset $asset)
    {
        return $asset;
    }

    public function upload(Request $request)
    {
        $this->validate($request,[
            'file' => 'required|file|max:'.AssetService::getMaxUploadSize(),
        ]);

        $file = $request->file('file');

        $asset = new Asset();
        $asset->sha = sha1_file($file->getRealPath());
        $asset->extension = $file->getClientOriginalExtension();
        $asset->file_name = $file->getClientOriginalName();
        $asset = AssetService::updateFromPath($asset,$file->getRealPath());
        $asset->save();

        if($asset->type === 'image') {
            $api = new UploadApi();
            $api->upload($file->getRealPath(),[
                'public_id' => $asset->uuid,
                'use_filename' => true,
                'overwrite' => true
            ]);
        } else {
            $file->storePubliclyAs($asset->directory,$asset->file_name);
        }

		//AssetService::generateThumbnails($asset);

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

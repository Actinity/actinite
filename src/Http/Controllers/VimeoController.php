<?php
namespace Actinity\Actinite\Http\Controllers;

use Actinity\Actinite\Services\VimeoService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class VimeoController extends Controller
{
    public function thumbnail($id)
    {
        return VimeoService::api()->request('/videos/'.$id.'?fields=status,pictures')['body'];
    }

    public function upload(Request $request)
    {
        $this->validate($request,[
            'size' => 'required|int',
            'name' => 'required|string'
        ]);

        $response = VimeoService::api()->request('/me/videos',[
            'name' => $request->get('name'),
            'upload' => ['approach' => 'tus','size' => $request->get('size')],
            'embed' => [
                'buttons' => [
                    'watchlater' => false,
                    'share' => false,
                    'like' => false,
                    'embed' => false,
                ],
                'logos' => [
                    'vimeo' => false,
                ],
            ],
            'privacy' => [
                'view' => 'unlisted',
                'add' => false,
                'comments' => 'nobody',
            ],
        ],'POST');

        return [
            'status' => $response['status'],
            'error' => Arr::get($response,'body.error'),
            'player_url' => Arr::get($response,'body.player_embed_url'),
            'upload_url' => Arr::get($response,'body.upload.upload_link')
        ];
    }
}
<?php

namespace Actinity\Actinite\Jobs;

use Actinity\Actinite\Core\Asset;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ResizeAsset implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $asset;
    private $width;

    public function __construct(Asset $asset,int $width)
    {
        $this->asset = $asset;
        $this->width = $width;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if($this->asset->type !== 'image') {
            return;
        }
        $img = Image::make(Storage::get($this->asset->path));

        $img->resize($this->width,$this->width,function($constraint) {
            $constraint->aspectRatio();
        });

        Storage::put(
            $this->asset->directory.'/'.$this->width.'.jpg',
            $img->encode('png')->getEncoded(),
            'public'
        );
    }
}

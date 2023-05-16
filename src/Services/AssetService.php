<?php
namespace Actinity\Actinite\Services;

use Actinity\Actinite\Core\Node;
use Actinity\Actinite\Core\Asset;
use Actinity\Actinite\Jobs\ResizeAsset;

class AssetService
{
    public static function updateFromPath(Asset $asset, string $path)
    {
        if(!$asset->extension) {
            $dotparts = explode(".", $path);
            $asset->extension = array_pop($dotparts);
        }

        if(!$asset->file_name) {
            $slashparts = explode("/", $path);
            $asset->file_name = array_pop($slashparts);
        }

        $asset->mime = mime_content_type($path);

        switch(substr($asset->mime,0,5)) {
            case "image": $asset->type = 'image'; break;
            case "audio": $asset->type = 'audio'; break;
            case "video": $asset->type = 'video'; break;
            default: $asset->type = 'file';
        }

        if ($asset->type === 'image' && $dims = getimagesize($path)) {
            $asset->width = $dims[0];
            $asset->height = $dims[1];
        }

        if($asset->type === 'audio') {
            $duration = shell_exec("ffmpeg -i ".$path.' 2>&1 | grep -o \'Duration: [0-9:.]*\'');
            $duration = trim(str_replace('Duration: ','',$duration));

            if($duration) {
                $times = explode(":",$duration);
                $duration = ((int) $times[0]*3600) + ((int) $times[1]*60) + round(((float) $times[2]));
                $asset->duration = (int) $duration;
            }
        }

        $asset->size = filesize($path);

        return $asset;
    }

    public static function scanForAssets(Node $node): array
    {
        $node->editing = true;
        $assets = [];
        foreach(TypeService::fields($node->type) as $field) {
            if(in_array($field['type'],['image','audio','video','file'])) {
                if($value = $node->data->{$field['name']}) {
					$assets[] = $value;
                }
            }
            if($field['type'] === 'html') {
                preg_match_all('!{{asset_url:([0-9]+)}}!',$node->data->{$field['name']},$matches);
                $assets = array_merge($assets,$matches[1]);
            }
        }
        $assets = array_map(fn ($id) => (int) $id,$assets);
        return array_unique($assets);
    }

	public static function generateThumbnails(Asset $asset, bool $forceQueue = false): void
	{
		if($asset->type !== 'image') {
			return;
		}

		$default = new ResizeAsset($asset,450);

		$sizes = [];

		if($forceQueue) {
			dispatch($default);
		} else {
			dispatch_sync($default);
		}

		foreach(config('actinite.image_sizes') as $width) {
			if($width <= $asset->width) {
				dispatch(new ResizeAsset($asset,$width));
				$sizes[] = $width;
			}
		}

		$asset->sizes = $sizes;
		$asset->save();
	}

	public static function getMaxUploadSize(): int
	{
		static $max_size = -1;

		if ($max_size < 0) {
			// Start with post_max_size.
			$post_max_size = static::parse_size(ini_get('post_max_size'));
			if ($post_max_size > 0) {
				$max_size = $post_max_size;
			}

			// If upload_max_size is less, then reduce. Except if upload_max_size is
			// zero, which indicates no limit.
			$upload_max = static::parse_size(ini_get('upload_max_filesize'));
			if ($upload_max > 0 && $upload_max < $max_size) {
				$max_size = $upload_max;
			}
		}
		return $max_size;
	}

	private static function parse_size($size)
	{
		$unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
		$size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
		if ($unit) {
			// Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
			return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
		}
		else {
			return round($size);
		}
	}

}

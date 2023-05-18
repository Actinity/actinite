<?php
namespace Actinity\Actinite\Services;

use Wa72\HtmlPageDom\HtmlPageCrawler;

class Parser
{
    public static function output(string $content)
    {
        preg_match_all('!cms://node/([0-9]+)!',$content,$matches);
        foreach($matches[1] as $idx => $node_id) {
            $node = app('Actinite')->get($node_id);
            if($node) {
                $content = str_replace(
                    $matches[0][$idx],
                    $node->url,
                    $content
                );
            }
        }

        return $content;
    }

	public static function editing(string $content)
	{
		/*preg_match_all('!cms://assets/([0-9]+)!',$content,$matches);
		foreach($matches[1] as $idx => $asset_id) {
			$asset = app('AssetProvider')->get($asset_id);
			if($asset) {
				$content = str_replace(
					$matches[0][$idx],
					asset($asset->path),
					$content
				);
			}
		}*/

		return $content;
	}

	public static function input($field, $content)
	{
		if($field['type'] === 'html') {

			$content = strip_tags($content,[
				'p','br',
				'h1','h2','h3','h4','h5','h6',
				'strong','em',
				'a',
				'ul','ol','li',
				'iframe',
				'img','audio',
			]);

			/*$dom = HtmlPageCrawler::create($content);
			$dom->filter("img[data-ac-asset]")->each(function($img) {
				$asset = app('AssetProvider')->get($img->getAttribute('data-ac-asset'));

				$payload = ['id' => $asset->id,'sha' => $asset->sha,'sizes' => []];
				foreach($asset->sizes ?? [] as $size) {
					$payload['sizes'][$size] = "t/".$size.".webp";
				}
				$payload['sizes'][$asset->width] = $asset->file_name;




				$string = "actinite://asset/".json_encode($payload);

				$img->setAttribute('src',$string);
			});

			$content = $dom->saveHTML();*/


		} else {
			$content = strip_tags($content);
		}

		return $content;
	}
}

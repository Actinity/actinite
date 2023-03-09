<?php
namespace Actinity\Actinite\Services;

use Actinity\Actinite\Core\Asset;

class AssetProvider
{
    protected $loaded = [];
    protected $queued = [];

    public function queue(array $ids)
    {
        $this->queued = array_unique(array_merge($this->queued,$ids));
    }

    public function loadQueue()
    {
        $toLoad = array_diff($this->queued,array_keys($this->loaded));
        if(count($toLoad)) {
            $assets = Asset::whereIn('id',$toLoad)->get();
            foreach($assets as $asset) {
                $this->loaded[$asset->id] = $asset;
            }
        }

        $this->queued = [];
    }

    public function load(array $ids)
    {

    }

    public function get(string $id)
    {
        if(preg_match('!^/assets/([0-9]+)/!',$id,$matches)) {
            $id = $matches[1];
        }
        if(!($this->loaded[$id] ?? false)) {
            $this->loaded[$id] = Asset::find($id) ?: new Asset();
        }

        return $this->loaded[$id];

    }
}

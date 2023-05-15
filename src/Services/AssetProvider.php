<?php
namespace Actinity\Actinite\Services;

use Actinity\Actinite\Core\Asset;

class AssetProvider
{
    protected $loaded = [];
    protected $queued = [];

    public function queue(array $ids): void
    {
        $this->queued = array_unique(array_merge($this->queued,$ids));
    }

	public function getLoaded(): array
	{
		return $this->loaded;
	}

    public function loadQueue(): void
    {
		$this->load($this->queued);
        $this->queued = [];
    }

    public function load(array $ids): void
    {
		$toLoad = array_values(array_diff($ids,array_keys($this->loaded)));
		if(count($toLoad)) {
			$assets = Asset::whereIn('id',$toLoad)->get();
			foreach($assets as $asset) {
				$this->loaded[$asset->id] = $asset;
			}
		}
    }

    public function get($id): ?Asset
    {
		$id = (int) $id;
        if(!($this->loaded[$id] ?? false)) {
            $this->loaded[$id] = Asset::find($id) ?: new Asset();
        }

        return $this->loaded[$id];

    }
}

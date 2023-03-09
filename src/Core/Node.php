<?php

namespace Actinity\Actinite\Core;

use Actinity\Actinite\Observers\NodeObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Node extends Model
{
    use SoftDeletes;

    protected $guarded = [];
    protected $table = 'ac_nodes';
    protected $child_cache = null;
    protected $default_template = null;
    protected $_parents;
    public $editing = false;
    protected $attributes = [
        'is_ready' => 1,
        'data' => '{}',
        'asset_cache' => '[]',
        'child_order' => '[]',
    ];

    protected $casts = [
        'asset_cache' => 'array',
        'child_order' => 'array',
    ];

    private $decoded_data = null;

    public string $icon = 'far fa-circle';

    public function getTable()
    {
        return config('actinite.mode') === 'draft' ? 'ac_nodes' : 'ac_published_nodes';
    }

    public function childTypes(): array
    {
        return [];
    }

    public function fields(): array
    {
        return [];
    }

    public function pageTemplates(): array
    {
        return [];
    }

    public function getUrlAttribute()
    {
        return $this->page_template ? '/' . $this->path : null;
    }

    public function getChildrenAttribute()
    {
        if($this->child_cache === null) {
            $this->child_cache = NodeFactory::query(['parent' => $this->id]);
        }
        return $this->child_cache;
    }

	public function getIsRoutableAttribute()
	{
		return !!$this->page_template;
	}

    protected function getDataAttribute()
    {
        if(!$this->decoded_data) {
            $data = $this->attributes['data'] ?: '{}';
            $class = $this->editing ? RawDataModel::class : DataModel::class;
            $this->decoded_data = new $class(is_array($data) ? $data : json_decode($data,true));
        }
        return $this->decoded_data;
    }

    public function setDataValue($key,$value)
    {
        if(!$this->editing) {
            return false;
        }
        $data = $this->data;
        $data->$key = $value;
        $this->attributes['data'] = json_encode($data);
    }

    public function getParentsAttribute()
    {
        if(!$this->_parents) {
            $this->_parents = app('Actinite')->parents($this);
        }
        return $this->_parents;
    }

    public function newQuery()
    {
        if(get_class($this) !== Node::class && get_class($this) !== PublishedNode::class) {
            return parent::newQuery()->where('type','=',get_class($this));
        }
        return parent::newQuery();
    }

    public function getPageTemplateAttribute()
    {
        return $this->attributes['page_template'] ?? $this->default_template;
    }

    public function getTypeAttribute()
    {
        return $this->attributes['type'] ?? get_class($this);
    }

    public static function boot()
    {
        parent::boot();

        parent::observe(NodeObserver::class);
    }

    public function toJson($options = 0)
    {
        return parent::toJson(JSON_FORCE_OBJECT);
    }

    public function getSearchBody(): string
    {
        return $this->data->body ?: "";
    }

    public function populateDefaultData() {
        //
    }

    public function inherits(): array
    {
        return [];
    }
}

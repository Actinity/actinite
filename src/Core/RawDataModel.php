<?php
namespace Actinity\Actinite\Core;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use ArrayAccess;
use JsonSerializable;

class RawDataModel
	implements ArrayAccess, Arrayable, Jsonable, JsonSerializable
{
	protected $attributes = [];

	public function __construct(array $attributes = [])
	{
		$this->attributes = $attributes;
	}

	public function __get($key)
	{
		return $this->attributes[$key] ?? null;
	}

	public function __set($key,$value)
	{
		$this->attributes[$key] = $value;
	}

	public function offsetExists($offset)
	{
		return isset($this->$offset);
	}

	public function offsetGet($offset)
	{
		return $this->$offset;
	}

	public function offsetSet($offset, $value)
	{
		$this->$offset = $value;
	}

	public function offsetUnset($offset)
	{
		unset($this->$offset);
	}

	public function __isset($key)
	{
		return (isset($this->attributes[$key]));
	}

	public function __unset($key)
	{
		unset($this->attributes[$key]);
	}

	public function __toString()
	{
		return $this->toJson();
	}

	public function toJson($options = 0)
	{
		return json_encode($this->jsonSerialize(), $options);
	}

	public function jsonSerialize()
	{
		return $this->toArray();
	}

	/**
	 * Convert the model instance to an array.
	 *
	 * @return array
	 */
	public function toArray()
	{
		return $this->attributes;
	}
}

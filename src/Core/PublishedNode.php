<?php
namespace Actinity\Actinite\Core;

class PublishedNode
extends Node
{
    protected $table = 'ac_published_nodes';

	public function getTable()
	{
		return $this->table;
	}
}

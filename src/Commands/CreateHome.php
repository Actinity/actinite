<?php
namespace Actinity\Actinite\Commands;

use Actinity\Actinite\Core\Node;
use Illuminate\Console\Command;

class CreateHome
	extends Command
{
	protected $signature = 'actinite:create-home';

	protected $description = 'Create a Home node';

	public function handle()
	{
		config(['actinite.mode' => 'draft']);

		if(Node::where('path','=','')->first()) {
			$this->info('Home node already exists');
			return Command::FAILURE;
		}

		$node = new Node();
		$node->name = 'Home';
		$node->type = 'App\Nodes\Page';
		$node->slug = '';
		$node->path = '';
		$node->page_template = 'simple';
		$node->editing = true;
		$node->setDataValue('title','Welcome');
		$node->save();

		$this->info('Home node created');

		return Command::SUCCESS;

	}
}

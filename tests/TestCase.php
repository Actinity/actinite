<?php
namespace Tests;

use Actinity\Actinite\ActiniteServiceProvider;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class TestCase extends \Orchestra\Testbench\TestCase
{

	protected function getPackageProviders($app)
	{
		return [
			ActiniteServiceProvider::class,
		];
	}

	protected function getEnvironmentSetUp($app)
	{
		$app['config']->set('database.default', 'testing');
	}

}
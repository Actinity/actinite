<?php
namespace Actinity\Actinite;

use Actinity\Actinite\Commands\CreateHome;
use Actinity\Actinite\Commands\Publish;
use Actinity\Actinite\Commands\PublishOne;
use Actinity\Actinite\Commands\PublishOnSchedule;
use Actinity\Actinite\Commands\PurgeCache;
use Actinity\Actinite\Commands\RebuildTree;
use Actinity\Actinite\Core\Node;
use Actinity\Actinite\Core\NodeFactory;
use Actinity\Actinite\Observers\NodeObserver;
use Actinity\Actinite\Services\AssetProvider;
use Actinity\Actinite\AppEvents\AppEventBus;
use Cloudinary\Configuration\Configuration;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class ActiniteServiceProvider
    extends ServiceProvider
{

    public function register()
    {
        // Set up Cloudinary
        Configuration::instance(['cloud_name' => config('services.cloudinary.name'),
            'api_key' => config('services.cloudinary.key'),
            'api_secret' => config('services.cloudinary.secret'),
        ]);
    }

    public function boot()
    {
        $this->commands([
            RebuildTree::class,
            Publish::class,
            PurgeCache::class,
            PublishOne::class,
            PublishOnSchedule::class,
			CreateHome::class,
        ]);

        $this->loadRoutesFrom(__DIR__."/routes.php");

        $this->loadViewsFrom(__DIR__."/views", 'actinite');
		$this->loadMigrationsFrom(__DIR__.'/../database/migrations');

		$this->publishes([
			__DIR__.'/config.php' => config_path('actinite.php'),
			__DIR__.'/../resources/stubs/Nodes' => app_path('Nodes'),
		],'actinite');

        app()->singleton('AssetProvider',function() {
            return new AssetProvider();
        });

		config(['actinite.features' => array_filter(array_diff(
			array_merge(
				[],
				explode(',',config('actinite.features_enabled'))
			),
			explode(',',config('actinite.features_disabled'))
		))]);


		app()->bind('Actinite',function() {
			return new NodeFactory();
		});

        app()->singleton('AppEvents', function () {
            return new AppEventBus;
        });

        Node::observe(NodeObserver::class);

        Gate::define('actinite:admin',function() {
            return auth()->user()->is_admin;
        });

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('actinite:publish-scheduled')->everyMinute()->onOneServer();
        });

    }
}

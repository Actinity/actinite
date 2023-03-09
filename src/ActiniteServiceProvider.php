<?php
namespace Actinity\Actinite;

use Actinity\Actinite\Commands\CreateHome;
use Actinity\Actinite\Commands\Publish;
use Actinity\Actinite\Commands\PublishOne;
use Actinity\Actinite\Commands\PublishOnSchedule;
use Actinity\Actinite\Commands\PurgeCache;
use Actinity\Actinite\Commands\RebuildTree;
use Actinity\Actinite\Core\Node;
use Actinity\Actinite\Observers\NodeObserver;
use Actinity\Actinite\Services\AssetProvider;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class ActiniteServiceProvider
    extends ServiceProvider
{

    public function register()
    {

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

        app()->singleton('AssetProvider',function() {
            return new AssetProvider();
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

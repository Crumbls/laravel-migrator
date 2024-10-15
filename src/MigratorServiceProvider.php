<?php

namespace Crumbls\Migrator;

use Crumbls\Comments\Services\CommentService;
use Crumbls\Migrator\Commands\Migrate;
use Crumbls\Migrator\Managers\MigrationManager;
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Application;

class MigratorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
	    $this->publishes([
		    __DIR__.'/Config/migrator.php' => config_path('migrator.php'),
	    ], 'config');

	    if ($this->app->runningInConsole()) {
		    $this->commands([
				Migrate::class
//			    InstallCommand::class,
//			    NetworkCommand::class,
		    ]);
	    }

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
	    $this->mergeConfigFrom(
		    __DIR__.'/Config/config.php', 'migrator'
	    );
	    $this->app->singleton('crumbls-migrator', function (Application $app) {
		    return new MigrationManager($app);
	    });
    }

}

<?php

namespace Crumbls\Migrator;

use Crumbls\Migrator\Commands\Migrate;
use Crumbls\Migrator\Filament\Pages\ImportDatabase;
use Crumbls\Migrator\Filament\Pages\ImportPage;
use Crumbls\Migrator\Filament\Resources\ColumnResource;
use Crumbls\Migrator\Filament\Resources\MigratorResource;
use Crumbls\Migrator\Filament\Resources\TableResource;
use Crumbls\Migrator\Managers\MigrationManager;
use Crumbls\Comments\Services\CommentService;
use Filament\Facades\Filament;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Application;
use Livewire\Livewire;

class MigratorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
	    $this->loadMigrationsFrom(__DIR__.'/Migrations');

	    $this->publishes([
		    __DIR__.'/Config/migrator.php' => config_path('migrator.php'),
	    ], 'config');

	    if ($this->app->runningInConsole()) {
		    $this->commands([
				Migrate::class
		    ]);
	    }

		$this->loadViewsFrom(__DIR__.'/Views', 'crumbls-importer');

		if (true || class_exists(Filament::class)) {
			Filament::registerPages([
//				ImportPage::class,
//				ImportDatabase::class
			]);
			Filament::registerResources([
				ColumnResource::class,
				MigratorResource::class,
				TableResource::class
			]);
			FilamentAsset::register([
				Css::make('laravel-migrator', __DIR__ . '/../resources/css/build.css')
					->loadedOnRequest(),
			]);
		}

		//Livewire::component('crumbls.migrator.filament.pages.import-page', ImportPage::class);

	    Livewire::component('crumbls.migrator.filament.resources.migrator-resource.pages.list-migrators', MigratorResource\Pages\ListMigrators::class);
	    Livewire::component('crumbls.migrator.filament.resources.migrator-resource.pages.create-migrator', MigratorResource\Pages\CreateMigrator::class);
	    Livewire::component('crumbls.migrator.filament.resources.migrator-resource.pages.view-migrator', MigratorResource\Pages\ViewMigrator::class);
	    Livewire::component('crumbls.migrator.filament.resources.migrator-resource.relation-managers.tables-relation-manager', MigratorResource\RelationManagers\TablesRelationManager::class);
Livewire::component('crumbls.migrator.filament.resources.table-resource.relation-managers.columns-relation-manager', TableResource\RelationManagers\ColumnsRelationManager::class);
Livewire::component('crumbls.migrator.filament.resources.column-resource.pages.edit-column', ColumnResource\Pages\EditColumn::class);
	    //
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

<?php

namespace Crumbls\Migrator\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Contracts\View\View;
use Filament\Panel;
use Illuminate\Support\Facades\Route;


class ImportDatabase extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'crumbls-importer::filament.pages.import';

	// Override this method to hide the page from the sidebar
	public static function shouldRegisterNavigation(): bool
	{
		return false;
	}

		public function getTitle() : string{
		return __METHOD__;
	}
	public function getHeading() : string{
		return __METHOD__;
		return __('Migrations');
	}

	public static function getNavigationLabel() : string {
		return __METHOD__;
		return __('Migrations');
	}

	public static function getSlug() : string {
		return 'migrator-database';
	}

	public function mount()
	{
		dd(get_class_methods(get_called_class()));
		dd($id);
		$record = YourModel::find($id);
		$this->data = $record->data;
	}

	public static function registerRoutes(Panel $panel): void
	{
		if (filled(static::getCluster())) {
			Route::name(static::prependClusterRouteBaseName('pages.'))
				->prefix(static::prependClusterSlug(''))
				->group(fn () => static::routes($panel));

			return;
		}

		Route::name('pages.')->group(fn () => static::routes($panel));
	}


	public static function routes(Panel $panel): void
	{
//		dd(static::getRoutePath());
		Route::get(static::getRoutePath(), static::class)
			->middleware(static::getRouteMiddleware($panel))
			->withoutMiddleware(static::getWithoutRouteMiddleware($panel))
			->name(static::getRelativeRouteName());
	}

		/**
	 * @return array<string, mixed>
	 */
	protected function getViewData(): array
	{
		return [
			'connections' => array_keys(config('database.connections'))
		];
	}
}

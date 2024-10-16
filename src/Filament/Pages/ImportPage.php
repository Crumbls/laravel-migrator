<?php

namespace Crumbls\Migrator\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Contracts\View\View;
Use Filament\Facades\Filament;
use Illuminate\Support\Str;

class ImportPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'crumbls-importer::filament.pages.import';

	public function getTitle() : string{
		return __METHOD__;
	}
	public function getHeading() : string{
		return __('Migrations');
	}

	public static function getNavigationLabel() : string {
		return __('Migrations');
	}

	public static function getSlug() : string {
		return 'migrator';
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

	public function redirectToBuilder(string $type, string $sourceValue, string $destinationValue) {

		if ($type == 'connection') {
			return redirect()->route($this->getRouteName(), array_filter([
				'tenant' => Filament::getTenant(),
				'type' => $type,
				'source' => $sourceValue,
				'destination' => $destinationValue
			]));
		}
		dd($target, $sourceValue, $destinationValue);
	}



	public static function getRoutePath(): string
	{
		return '/' . static::getSlug().'/{type?}/{source?}/{destination?}';
	}

	public function mount() {//string $type = '', ?string $source, ?string $destination) {
		$type = request()->route('type');
		if ($type && in_array($type, [
			'connection'
			])) {
			$method = 'mount'.ucfirst(Str::camel($type));
			$this->$method();
			return;
		}
	}

	protected function mountConnection() {
		$source = request()->route('source');
		$destination = request()->route('destination');
		$connections = array_keys(config('database.connections'));

		if (!in_array($source, $connections) || !in_array($destination, $connections)) {
			/**
			 * TODO: Add in an error.
			 */
			return redirect()->route($this->getRouteName(), array_filter([
				'tenant' => Filament::getTenant(),
			]));
		}

		static::$view = 'crumbls-importer::filament.pages.connection';
	}


	public function getView(): string
	{
//		dd(static::$view);
		return static::$view;
	}
}

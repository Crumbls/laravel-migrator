<?php

namespace Crumbls\Migrator\Filament\Resources\MigratorResource\Pages;

use Crumbls\Migrator\Filament\Resources\MigratorResource;
use Crumbls\Migrator\Jobs\MigratorCreated;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Pages\Page;
use Illuminate\Contracts\View\View;
Use Filament\Facades\Filament;
use Illuminate\Support\Str;
use Filament\Pages\Concerns\HasRoutes;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Filament\Resources\Pages\PageRegistration;
use Filament\Panel;
use Illuminate\Database\Eloquent\Model;
use Filament\Support\Facades\FilamentView;

class CreateMigrator extends CreateRecord //Page
{
	//	use HasRoutes;
	public ?array $data = [];

	protected static string $resource = MigratorResource::class;
	protected static ?string $navigationIcon = 'heroicon-o-document-text';

	protected static string $view = 'crumbls-importer::filament.resources.migrator-resource.pages.create-record';

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
			'connections' => array_keys(config('database.connections')),
			'data' => $this->data
		];
	}



	public static function dis_getRoutePath(): string
	{
		return '/' . static::getSlug().'/{type?}/{source?}/{destination?}';
	}

	public static function aroute() : string {
		return 'create';
	}

	public static function route(string $path): PageRegistration
	{
		return new PageRegistration(
			page: static::class,
			route: fn (Panel $panel): Route => RouteFacade::get($path, static::class)
				->middleware(static::getRouteMiddleware($panel))
				->withoutMiddleware(static::getWithoutRouteMiddleware($panel)),
		);
	}

	public function getRules() : array {
		return [
			'data.type' => [
				'required',
				'string',
				'in:connection'
			],
			'data.source' => [
				'required',
				'string'
			],
			'data.destination' => [
				'required',
				'string',
				'different:data.source'
			]
		];
	}

	public function create(bool $another = false) : void {

		$this->authorizeAccess();

		try {
			$this->beginDatabaseTransaction();

			$this->callHook('beforeValidate');

			$data = $this->validate();

			$data = $data['data'];
//			$data = $this->form->getState();

			$this->callHook('afterValidate');

			$data = $this->mutateFormDataBeforeCreate($data);

			$this->callHook('beforeCreate');

			$this->record = $this->handleRecordCreation($data);

//			$this->form->model($this->getRecord())->saveRelationships();

			$this->callHook('afterCreate');

			$this->commitDatabaseTransaction();
		} catch (Halt $exception) {
			$exception->shouldRollbackDatabaseTransaction() ?
				$this->rollBackDatabaseTransaction() :
				$this->commitDatabaseTransaction();

			return;
		} catch (Throwable $exception) {
			$this->rollBackDatabaseTransaction();

			throw $exception;
		}

		$this->rememberData();

		$this->getCreatedNotification()?->send();

		if ($another) {
			// Ensure that the form record is anonymized so that relationships aren't loaded.
			$this->form->model($this->getRecord()::class);
			$this->record = null;

			$this->fillForm();

			return;
		}

		$redirectUrl = $this->getRedirectUrl();

		$this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode() && is_app_url($redirectUrl));

	}

	/**
	 * @param  array<string, mixed>  $data
	 */
	protected function handleRecordCreation(array $data): Model
	{
		$record = new ($this->getModel())($data);

		if (
			static::getResource()::isScopedToTenant() &&
			($tenant = Filament::getTenant())
		) {
			return $this->associateRecordWithTenant($record, $tenant);
		}

		$record->save();

		return $record;
	}


	/**
	 * @param  array<string, mixed>  $data
	 * @return array<string, mixed>
	 */
	protected function mutateFormDataBeforeCreate(array $data): array
	{
		if (!array_key_exists('name', $data) || !$data['name']) {
			$data['name'] = sprintf('%s migrator from %s to %s built on %s', $data['type'], $data['source'], $data['destination'], now()->format('l, F j, Y g:i A'));
		}
		return $data;
	}


	/**
	 * Executed once the record is created.
	 * @return void
	 */
	protected function afterCreate(): void
	{

	}
}

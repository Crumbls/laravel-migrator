<?php

namespace Crumbls\Migrator\Filament\Resources\MigratorResource\Pages;

use Crumbls\Migrator\Filament\Resources\MigratorResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

use Filament\Notifications\Notification;
class ViewMigrator extends ViewRecord
{
    protected static string $resource = MigratorResource::class;

    protected function getHeaderActions(): array
    {
//		dd(get_class_methods(get_called_class()));
        return [
            Actions\DeleteAction::make(),
	        Actions\Action::make('Execute')
		        ->action(function() {
			        /**
			         * Move this into a job.
			         */

			        $migrator = app('crumbls-migrator');
			        $migrator
				        ->driver($this->record->driver)
				        ->initialize($this->record)
				        ->parse()
				        ->generateModels(force: false)
				        ->generateMigrations(force: false)
				        ->generateFilamentResources(force: false)
			        ;

			        Notification::make()
				        ->title('Migration successful')
				        ->success()
				        ->send();
		        })
        ];
    }
}

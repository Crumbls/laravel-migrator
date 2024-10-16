<?php

namespace Crumbls\Migrator\Filament\Resources\MigratorResource\Pages;

use Crumbls\Migrator\Filament\Resources\MigratorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMigrators extends ListRecords
{
    protected static string $resource = MigratorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

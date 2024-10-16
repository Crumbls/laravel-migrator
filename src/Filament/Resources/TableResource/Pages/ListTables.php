<?php

namespace Crumbls\Migrator\Filament\Resources\TableResource\Pages;

use Crumbls\Migrator\Filament\Resources\TableResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTables extends ListRecords
{
    protected static string $resource = TableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

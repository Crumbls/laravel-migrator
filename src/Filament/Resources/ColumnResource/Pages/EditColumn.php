<?php

namespace Crumbls\Migrator\Filament\Resources\ColumnResource\Pages;

use Crumbls\Migrator\Filament\Resources\ColumnResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditColumn extends EditRecord
{
    protected static string $resource = ColumnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

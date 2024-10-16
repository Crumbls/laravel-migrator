<?php

namespace Crumbls\Migrator\Filament\Resources\ColumnResource\Pages;

use Crumbls\Migrator\Filament\Resources\ColumnResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateColumn extends CreateRecord
{
    protected static string $resource = ColumnResource::class;
}

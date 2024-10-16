<?php

namespace Crumbls\Migrator\Filament\Resources\TableResource\Pages;

use Crumbls\Migrator\Filament\Resources\TableResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTable extends CreateRecord
{
    protected static string $resource = TableResource::class;
}

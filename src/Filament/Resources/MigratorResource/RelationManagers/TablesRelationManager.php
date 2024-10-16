<?php

namespace Crumbls\Migrator\Filament\Resources\MigratorResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Facades\Filament;

class TablesRelationManager extends RelationManager
{
    protected static string $relationship = 'tables';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
            ])
            ->filters([
                //
            ])
            ->headerActions([
//                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
	            Tables\Actions\Action::make('Edit')
		            ->url(function(\Crumbls\Migrator\Models\Table $record) {
			            return route('filament.admin.resources.tables.edit', array_filter([
							'tenant' => Filament::getTenant(),
				            'record' => $record
			            ]));
						dd($record);
		            })
,
            ])
            ->bulkActions([
            ]);
    }
}

<?php

namespace Crumbls\Migrator\Filament\Resources\TableResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Facades\Filament;

class ColumnsRelationManager extends RelationManager
{
    protected static string $relationship = 'columns';

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
	            Tables\Columns\TextColumn::make('source'),
                Tables\Columns\TextColumn::make('destination')
            ])
            ->filters([
                //
            ])
            ->headerActions([
//                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
//                Tables\Actions\EditAction::make(),
  //              Tables\Actions\DeleteAction::make(),
	            Tables\Actions\Action::make('Edit')
		            ->url(function(\Crumbls\Migrator\Models\Column $record) {
			            return route('filament.admin.resources.columns.edit', array_filter([
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

<?php

namespace Crumbls\Migrator\Filament\Resources;

use App\Filament\Resources\TableResource\Pages;
use App\Filament\Resources\TableResource\RelationManagers;
use App\Models\Table as Model;
use Crumbls\Migrator\Filament\Resources\TableResource\RelationManagers\ColumnsRelationManager;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TableResource extends Resource
{
    protected static ?string $model = Model::class;

	protected static bool $isScopedToTenant = false;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
	public static function shouldRegisterNavigation(): bool
	{
		return false;
	}
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('migrator_id')
                    ->relationship('migrator', 'name')
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('source')
                    ->required(),
                Forms\Components\TextInput::make('destination')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('migrator.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('source')
                    ->searchable(),
                Tables\Columns\TextColumn::make('destination')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
			ColumnsRelationManager::class
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => \Crumbls\Migrator\Filament\Resources\TableResource\Pages\ListTables::route('/'),
            'create' => \Crumbls\Migrator\Filament\Resources\TableResource\Pages\CreateTable::route('/create'),
            'edit' => \Crumbls\Migrator\Filament\Resources\TableResource\Pages\EditTable::route('/{record}/edit'),
        ];
    }
}

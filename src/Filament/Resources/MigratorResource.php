<?php

namespace Crumbls\Migrator\Filament\Resources;

use App\Filament\Resources\MigratorResource\Pages;
use App\Filament\Resources\MigratorResource\RelationManagers;
use Crumbls\Migrator\Filament\Resources\MigratorResource\RelationManagers\TablesRelationManager;
use Crumbls\Migrator\Models\Migrator;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MigratorResource extends Resource
{
    protected static ?string $model = Migrator::class;
	protected static bool $isScopedToTenant = false;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

	public function getTitle() : string{
		return __METHOD__;
	}
	public function getHeading() : string{
		return __('Migrations');
	}

	public static function getNavigationLabel() : string {
		return __('Migrations');
	}

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
	            Section::make('Settings')
		        ->schema([
					Forms\Components\TextInput::make('name')
                        ->required()
	                    ->readonly(),
					Forms\Components\TextInput::make('type')
                        ->required()
	                    ->readonly(),
                    Forms\Components\TextInput::make('source')
	                    ->required()
		                ->readonly(),
                    Forms\Components\TextInput::make('destination')
                        ->required()
				        ->readonly()
			        ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
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
                Tables\Actions\ViewAction::make(),
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
			TablesRelationManager::class
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => \Crumbls\Migrator\Filament\Resources\MigratorResource\Pages\ListMigrators::route('/'),
            'create' => \Crumbls\Migrator\Filament\Resources\MigratorResource\Pages\CreateMigrator::route('/create'),
	        'view' => \Crumbls\Migrator\Filament\Resources\MigratorResource\Pages\ViewMigrator::route('/{record}'),
        ];
    }
}

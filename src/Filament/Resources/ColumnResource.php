<?php

namespace Crumbls\Migrator\Filament\Resources;

use Crumbls\Migrator\Filament\Resources\ColumnResource\Pages;
use Crumbls\Migrator\Filament\Resources\ColumnResource\RelationManagers;
use App\Models\Column;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ColumnResource extends Resource
{
    protected static ?string $model = Column::class;
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
                Forms\Components\Select::make('table_id')
                    ->relationship('table', 'name')
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('source')
                    ->required(),
                Forms\Components\TextInput::make('destination')
                    ->required(),
                Forms\Components\TextInput::make('type_name')
                    ->required(),
                Forms\Components\TextInput::make('type')
                    ->required(),
                Forms\Components\TextInput::make('collation'),
                Forms\Components\Toggle::make('nullable')
                    ->required(),
                Forms\Components\TextInput::make('default'),
                Forms\Components\Toggle::make('auto_increment')
                    ->required(),
                Forms\Components\TextInput::make('comment'),
                Forms\Components\TextInput::make('generation'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('table.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('source')
                    ->searchable(),
                Tables\Columns\TextColumn::make('destination')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('collation')
                    ->searchable(),
                Tables\Columns\IconColumn::make('nullable')
                    ->boolean(),
                Tables\Columns\TextColumn::make('default')
                    ->searchable(),
                Tables\Columns\IconColumn::make('auto_increment')
                    ->boolean(),
                Tables\Columns\TextColumn::make('comment')
                    ->searchable(),
                Tables\Columns\TextColumn::make('generation')
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListColumns::route('/'),
            'create' => Pages\CreateColumn::route('/create'),
            'edit' => Pages\EditColumn::route('/{record}/edit'),
        ];
    }
}

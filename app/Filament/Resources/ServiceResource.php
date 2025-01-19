<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Filament\Resources\ServiceResource\RelationManagers;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->required()
                    ->label('Заголовок'),
                Textarea::make('description')
                    ->required()
                    ->label('Описание'),
                TextInput::make('price')
                    ->numeric()
                    ->label('Цена')
                    ->required(),
                FileUpload::make('image')
                    ->label('Изображение')
                    ->image(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Заголовок'),
                TextColumn::make('price')->label('Цена'),
            ])
            ->filters([
                Filter::make('price')
                    ->form([
                        TextInput::make('min_price')->numeric()->placeholder('Минимальная цена'),
                        TextInput::make('max_price')->numeric()->placeholder('Максимальная цена'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['min_price'], fn($query) => $query->where('price', '>=', $data['min_price']))
                            ->when($data['max_price'], fn($query) => $query->where('price', '<=', $data['max_price']));
                    })
                    ->label('Диапазон цен'),
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
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}

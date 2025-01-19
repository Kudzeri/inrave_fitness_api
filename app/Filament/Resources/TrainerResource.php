<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrainerResource\Pages;
use App\Filament\Resources\TrainerResource\RelationManagers;
use App\Models\Trainer;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MultiSelect;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TrainerResource extends Resource
{
    protected static ?string $model = Trainer::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('first_name')
                    ->required()
                    ->label('Имя'),
                TextInput::make('last_name')
                    ->required()
                    ->label('Фамилия'),
                Textarea::make('description')
                    ->label('Описание'),
                FileUpload::make('photo')
                    ->label('Фотография')
                    ->image(),
                MultiSelect::make('services')
                    ->relationship('services', 'title')
                    ->label('Услуги')
                    ->placeholder('Выберите услуги'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('first_name')->label('Имя'),
                TextColumn::make('last_name')->label('Фамилия'),
                ImageColumn::make('photo')->label('Фотография')->size(50),
                TextColumn::make('services_count')
                    ->counts('services')
                    ->label('Количество услуг'),
            ])
            ->filters([
                // Фильтр по имени тренера
                Filter::make('first_name')
                    ->label('Фильтр по имени')
                    ->form([
                        TextInput::make('name')
                            ->label('Имя')
                            ->placeholder('Введите имя'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->when($data['name'], fn($query, $name) => $query->where('first_name', 'like', "%{$name}%"));
                    }),

                // Фильтр по числу услуг
                Filter::make('services')
                    ->label('Фильтр по количеству услуг')
                    ->form([
                        TextInput::make('min_services')->numeric()->label('Минимум услуг'),
                        TextInput::make('max_services')->numeric()->label('Максимум услуг'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['min_services'], fn($query) => $query->has('services', '>=', $data['min_services']))
                            ->when($data['max_services'], fn($query) => $query->has('services', '<=', $data['max_services']));
                    }),
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
            'index' => Pages\ListTrainers::route('/'),
            'create' => Pages\CreateTrainer::route('/create'),
            'edit' => Pages\EditTrainer::route('/{record}/edit'),
        ];
    }
}

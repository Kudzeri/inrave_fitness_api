<?php

namespace App\Filament\Resources\Messages;

use App\Filament\Resources\Messages\TrainingRequestResource\Pages\CreateTrainingRequest;
use App\Filament\Resources\Messages\TrainingRequestResource\Pages\EditTrainingRequest;
use App\Filament\Resources\Messages\TrainingRequestResource\Pages\ListTrainingRequests;
use App\Models\Messages\TrainingRequest;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TrainingRequestResource extends Resource
{
    protected static ?string $model = TrainingRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->required()
                    ->maxLength(20),
                Textarea::make('message'),
                Checkbox::make('consent')
                    ->label('Согласие на обработку персональных данных')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('phone'),
                TextColumn::make('message')->limit(50),
                BooleanColumn::make('consent'),
                TextColumn::make('created_at')->label('Created'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTrainingRequests::route('/'),
            'create' => CreateTrainingRequest::route('/create'),
            'edit' => EditTrainingRequest::route('/{record}/edit'),
        ];
    }
}

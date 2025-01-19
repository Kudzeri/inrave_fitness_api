<?php

namespace App\Filament\Resources\Messages\TrainingRequestResource\Pages;

use App\Filament\Resources\Messages\TrainingRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTrainingRequests extends ListRecords
{
    protected static string $resource = TrainingRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

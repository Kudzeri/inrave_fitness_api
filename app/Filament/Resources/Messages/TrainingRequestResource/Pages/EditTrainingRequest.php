<?php

namespace App\Filament\Resources\Messages\TrainingRequestResource\Pages;

use App\Filament\Resources\Messages\TrainingRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTrainingRequest extends EditRecord
{
    protected static string $resource = TrainingRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

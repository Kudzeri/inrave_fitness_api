<?php

namespace App\Filament\Widgets;

use App\Models\Service;
use App\Models\Trainer;
use App\Models\Product;
use App\Models\News;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class StatsOverview extends BaseWidget
{
    protected function getCards(): array
    {
        return [
            Card::make('Услуги', Service::count()),
            Card::make('Тренеры', Trainer::count()),
            Card::make('Товары', Product::count()),
            Card::make('Новости', News::count()),
        ];
    }
}

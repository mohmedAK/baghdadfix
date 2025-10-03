<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\OrderService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

use Illuminate\Support\Facades\Cache;
class OrdersByStatusStats extends StatsOverviewWidget
{
     protected  ?string $heading = 'Orders – By Status';

    protected function getStats(): array
    {
        // if you have an enum, swap these strings to your enum values.
        $created   = Cache::remember(
            'orders.status.created',
            60,
            fn() =>
            OrderService::where('status', 'created')->count()
        );

        $assigned  = Cache::remember(
            'orders.status.assigned',
            60,
            fn() =>
            OrderService::where('status', 'assigned')->count()
        );

        $awaiting  = Cache::remember(
            'orders.status.awaiting',
            60,
            fn() =>
            OrderService::where('status', 'awaiting_customer_approval')->count()
        );

        return [
            Stat::make('Created', number_format($created))
                ->icon('heroicon-o-sparkles')
                ->color('gray'),

            Stat::make('Assigned', number_format($assigned))
                ->icon('heroicon-o-user-plus')
                ->color('info'),

            Stat::make('Awaiting approval', number_format($awaiting))
                ->icon('heroicon-o-check-circle')
                ->color('warning'),
        ];
    }
}

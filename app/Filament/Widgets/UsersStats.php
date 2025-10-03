<?php

namespace App\Filament\Widgets;

use App\Enums\UserRole;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\OrderService;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

use Illuminate\Support\Facades\Cache;

class UsersStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $customers   = Cache::remember(
            'users.count.customers',
            60,
            fn() =>
            User::where('role', UserRole::Customer)->count()
        );

        $technicians = Cache::remember(
            'users.count.technicians',
            60,
            fn() =>
            User::where('role', UserRole::Technical)->count()
        );

        return [
            Stat::make('Customers', number_format($customers))
                ->icon('heroicon-o-users')
                ->color('success'),

            Stat::make('Technicians', number_format($technicians))
                ->icon('heroicon-o-wrench')
                ->color('primary'),
        ];
    }
}

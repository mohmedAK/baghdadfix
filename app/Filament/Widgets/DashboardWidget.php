<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\OrderService;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Facades\Cache;

class DashboardWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $now = now();

        // date ranges
        $todayStart     = $now->copy()->startOfDay();
        $todayEnd       = $now->copy()->endOfDay();

        $lastWeekStart  = $now->copy()->subWeek()->startOfWeek();
        $lastWeekEnd    = $now->copy()->subWeek()->endOfWeek();

        $lastMonthStart = $now->copy()->subMonthNoOverflow()->startOfMonth();
        $lastMonthEnd   = $now->copy()->subMonthNoOverflow()->endOfMonth();

        // cache counts briefly to keep the dashboard snappy
        $todayCount = Cache::remember(
            'orders.count.today',
            60,
            fn() =>
            OrderService::whereBetween('created_at', [$todayStart, $todayEnd])->count()
        );

        $lastWeekCount = Cache::remember(
            'orders.count.last_week',
            60,
            fn() =>
            OrderService::whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])->count()
        );

        $lastMonthCount = Cache::remember(
            'orders.count.last_month',
            60,
            fn() =>
            OrderService::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count()
        );

        return [
            Stat::make('Today', number_format($todayCount))
                ->icon('heroicon-o-calendar')
                ->color('success'),

            Stat::make('Last week', number_format($lastWeekCount))
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('info'),

            Stat::make('Last month', number_format($lastMonthCount))
                ->icon('heroicon-o-chart-bar')
                ->color('warning'),

        ];
    }
}

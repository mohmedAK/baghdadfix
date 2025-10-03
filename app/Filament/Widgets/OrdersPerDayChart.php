<?php

namespace App\Filament\Widgets;

use App\Models\OrderService;
use Filament\Widgets\ChartWidget;

class OrdersPerDayChart extends ChartWidget
{
  protected  ?string $heading = 'Orders (30 days)';
    protected function getData(): array {
        $from = now()->subDays(29)->startOfDay();
        $rows = OrderService::query()
            ->selectRaw('DATE(created_at) d, COUNT(*) c')
            ->where('created_at', '>=', $from)
            ->groupBy('d')->orderBy('d')
            ->pluck('c','d');
        return [
            'datasets' => [['label' => 'Orders', 'data' => array_values($rows->toArray())]],
            'labels'   => array_keys($rows->toArray()),
        ];
    }
    protected function getType(): string { return 'line'; }
}

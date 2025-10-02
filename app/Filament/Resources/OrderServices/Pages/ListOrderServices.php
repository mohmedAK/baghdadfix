<?php

namespace App\Filament\Resources\OrderServices\Pages;

use App\Filament\Resources\OrderServices\OrderServiceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;

// use Filament\Resources\Pages\ListRecords\Tab;
use Filament\Schemas\Components\Tabs\Tab;      // <-- required import
use Illuminate\Database\Eloquent\Builder;

class ListOrderServices extends ListRecords
{
    protected static string $resource = OrderServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }


    public function getTabs(): array
    {
        return [
            // 'all' => Tab::make('All'),
            // 'today' => Tab::make('Today')
            //     ->modifyQueryUsing(fn($q) => $q->whereDate('created_at', today())),
            // 'last_7_days' => Tab::make('Last 7 days')
            //     ->modifyQueryUsing(
            //         fn(Builder $q): Builder =>
            //         $q->whereDate('created_at', '>=', now()->subDays(7)->toDateString())
            //     ),

            // 'last_30_days' => Tab::make('Last 30 days')
            //     ->modifyQueryUsing(
            //         fn(Builder $q): Builder =>
            //         $q->whereDate('created_at', '>=', now()->subDays(30)->toDateString())
            //     ),
            // 'awaiting' => Tab::make('Awaiting approval')
            //     ->modifyQueryUsing(fn($q) => $q->where('status', 'awaiting_customer_approval')),
        ];
    }
}

<?php

namespace App\Filament\Resources\OrderServices;

use App\Filament\Resources\OrderServices\Pages\CreateOrderService;
use App\Filament\Resources\OrderServices\Pages\EditOrderService;
use App\Filament\Resources\OrderServices\Pages\ListOrderServices;
use App\Filament\Resources\OrderServices\Schemas\OrderServiceForm;
use App\Filament\Resources\OrderServices\Tables\OrderServicesTable;
use App\Models\OrderService;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderServiceResource extends Resource
{
    protected static ?string $model = OrderService::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return OrderServiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrderServicesTable::configure($table);
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
            'index' => ListOrderServices::route('/'),
            'create' => CreateOrderService::route('/create'),
            'edit' => EditOrderService::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

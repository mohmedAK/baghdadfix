<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\OrderService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use App\Filament\Resources\OrderServices\OrderServiceResource;
use Filament\Actions\Action;

class AwaitingApprovalTable extends TableWidget
{
    protected static ?string $heading = 'Awaiting approval';
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            // نفس فكرة getTableQuery()
            ->query(
                fn(): Builder =>
                OrderService::query()
                    ->where('status', OrderStatus::QuotePending)
                    ->orWhere('status', OrderStatus::Created)
                    ->orWhere('status', OrderStatus::Assigned)
                    ->orWhere('status', OrderStatus::AwaitingCustomerApproval) // إذا كنت تستخدم Enum
                    ->latest()
            )

            // نفس فكرة getTableColumns()
            ->columns([
                // TextColumn::make('id')
                //     ->label('Order #')
                //     ->copyable(),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),

                TextColumn::make('customer.phone')
                    ->label('Customer Phone')
                    ->searchable(),

                TextColumn::make('service.name')
                    ->label('Service')
                    ->searchable(),

                TextColumn::make('technical.name')
                    ->label('Technician')
                    ->toggleable(),
                TextColumn::make('technical.phone')
                    ->label('Technician Phone')
                    ->toggleable(),

                // TextColumn::badge() بديل BadgeColumn في v4
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(?OrderStatus $state) => Str::headline($state?->value ?? ''))
                    ->color(fn(?OrderStatus $state) => OrderStatus::color($state?->value ?? 'created'))
                    ->sortable(),


                TextColumn::make('created_at')
                    ->label('Created')
                    ->since()
                    ->sortable(),
            ])

            // (اختياري) إجراءات على الصف
            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-m-eye')
                    ->url(fn($record) => OrderServiceResource::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab(false),

                Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-m-pencil-square')
                    ->url(fn($record) => OrderServiceResource::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(false),
            ])->recordUrl(fn($record) => OrderServiceResource::getUrl('edit', ['record' => $record]))

            // (اختياري) حالة الجدول الفارغ
            ->emptyStateHeading('No orders waiting for approval');
    }
}

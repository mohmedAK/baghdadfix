<?php

namespace App\Filament\Resources\OrderServices\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class OrderServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                TextColumn::make('customer_id_fk')
                    ->searchable(),
                TextColumn::make('service_id_fk')
                    ->searchable(),
                TextColumn::make('technical_id_fk')
                    ->searchable(),
                TextColumn::make('assigned_by_admin_id_fk')
                    ->searchable(),
                TextColumn::make('assigned_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('assignment_note')
                    ->searchable(),
                TextColumn::make('state_id_fk')
                    ->searchable(),
                TextColumn::make('area_id_fk')
                    ->searchable(),
                TextColumn::make('gps_lat')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('gps_lng')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('admin_initial_price')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('admin_initial_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('admin_initial_note')
                    ->searchable(),
                TextColumn::make('final_price')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status'),
                IconColumn::make('submit')
                    ->boolean(),
                ImageColumn::make('image'),
                TextColumn::make('video')
                    ->searchable(),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}

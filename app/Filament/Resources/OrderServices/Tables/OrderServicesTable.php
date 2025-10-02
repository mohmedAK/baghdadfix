<?php

namespace App\Filament\Resources\OrderServices\Tables;

use App\Enums\OrderStatus;
use App\Models\OrderService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use Filament\Tables\Components\Tabs\Tab;




use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class OrderServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),

                TextColumn::make('service.name')
                    ->label('Service')
                    ->searchable(),

                TextColumn::make('technical.name')
                    ->label('Technician')
                    ->toggleable(),

                // TextColumn::badge() بديل BadgeColumn في v4
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(?OrderStatus $state) => Str::headline($state?->value ?? ''))
                    ->color(fn(?OrderStatus $state) => OrderStatus::color($state?->value ?? 'created'))
                    ->sortable(),

                IconColumn::make('submit')
                    ->label('Approved')
                    ->boolean(),

                TextColumn::make('admin_initial_price')
                    ->label('Init $')
                    ->money('usd', true)
                    ->sortable(),

                // عدد الصور والفيديو
                TextColumn::make('images_count')->counts('images')->label('Imgs'),
                TextColumn::make('videos_count')->counts('videos')->label('Vids'),

                TextColumn::make('technician_quote_price')
                    ->label('Tech $')->money('usd', true)->sortable(),

                TextColumn::make('final_price')
                    ->label('Final $')->money('usd', true)->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('status')->options(OrderStatus::options()),
                Filter::make('approved')
                    ->label('Approved by customer')
                    ->query(fn(Builder $q) => $q->where('submit', true)),

                SelectFilter::make('status')
                    ->options(\App\Enums\OrderStatus::options())
                    ->multiple(),

                // Date range on created_at
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('from')->label('From'),
                        DatePicker::make('to')->label('To'),
                    ])
                    ->query(function (EloquentBuilder $query, array $data): EloquentBuilder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn(EloquentBuilder $q, $date) =>
                                $q->whereDate('created_at', '>=', $date)
                            )
                            ->when(
                                $data['to'] ?? null,
                                fn(EloquentBuilder $q, $date) =>
                                $q->whereDate('created_at', '<=', $date)
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $badges = [];
                        if (! empty($data['from'])) $badges[] = 'From: ' . \Carbon\Carbon::parse($data['from'])->toFormattedDateString();
                        if (! empty($data['to']))   $badges[] = 'To: '   . \Carbon\Carbon::parse($data['to'])->toFormattedDateString();
                        return $badges;
                    }),

                // Customer approved?
                TernaryFilter::make('submit')
                    ->label('Customer approved')
                    ->queries(
                        true: fn(EloquentBuilder $q) => $q->where('submit', true),
                        false: fn(EloquentBuilder $q) => $q->where('submit', false),
                        blank: fn(EloquentBuilder $q) => $q, // no constraint
                    ),


            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),

                    Action::make('assignTech')
                        ->label('Assign')
                        ->icon('heroicon-m-user-plus')
                        ->form([
                            Select::make('technical_id_fk')
                                ->label('Technician')
                                ->relationship(
                                    name: 'technical',
                                    titleAttribute: 'name',
                                    modifyQueryUsing: fn(Builder $q) => $q->where('role', 'technical')
                                )
                                ->required()
                                ->preload()
                                ->searchable(),
                            Textarea::make('assignment_note')->rows(2),
                        ])
                        ->action(function (OrderService $record, array $data): void {
                            $record->fill([
                                'technical_id_fk'          => $data['technical_id_fk'],
                                'assignment_note'          => $data['assignment_note'] ?? null,
                                'assigned_at'              => now(),
                                'assigned_by_admin_id_fk'  => auth()->id(),
                                'status'                   => OrderStatus::Assigned,
                            ])->save();
                        }),

                    Action::make('setInitialPrice')
                        ->label('Set initial $')
                        ->icon('heroicon-m-currency-dollar')
                        ->form([
                            TextInput::make('admin_initial_price')
                                ->numeric()
                                ->required(),
                            Textarea::make('admin_initial_note')->rows(2),
                        ])
                        ->action(function (OrderService $record, array $data): void {
                            $record->update([
                                'admin_initial_price'    => $data['admin_initial_price'],
                                'admin_initial_note'     => $data['admin_initial_note'] ?? null,
                                'admin_initial_at'       => now(),
                                'admin_initial_by_id_fk' => auth()->id(),
                                'status'                 => OrderStatus::AdminEstimated,
                            ]);
                        }),
                ])->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ;
    }
}

<?php

namespace App\Filament\Resources\OrderServices\Schemas;

use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use App\Enums\OrderStatus;
use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Section;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class OrderServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // dd(auth()->user()?->id),
                Section::make('Customer & Service')
                    ->columns(2)
                    ->schema([
                        Select::make('customer_id_fk')
                            ->label('Customer')
                            ->relationship('customer', 'name')
                            ->preload()->searchable()
                            ->disabled()->dehydrated(false),

                        Select::make('service_id_fk')
                            ->label('Service')
                            ->relationship(
                                name: 'service',
                                titleAttribute: 'name',
                                modifyQueryUsing: static function (EloquentBuilder $query): void {
                                    $query->where('is_active', true);
                                },
                            )
                            ->preload()->searchable()
                            ->disabled()->dehydrated(false),

                        Textarea::make('description')->rows(3)
                            ->disabled()->dehydrated(false)->columnSpan(2),
                    ]),

                // الموقع (قراءة فقط)
                Section::make('Location')
                    ->columns(2)
                    ->schema([
                        Select::make('state_id_fk')
                            ->label('State')
                            ->relationship(
                                name: 'state',
                                titleAttribute: 'name',
                                modifyQueryUsing: static function (EloquentBuilder $query): void {
                                    $query->where('is_active', true);
                                },
                            )
                            ->preload()->searchable()
                            ->disabled()->dehydrated(false),

                        Select::make('area_id_fk')
                            ->label('Area')
                            ->relationship(
                                name: 'area',
                                titleAttribute: 'name',
                                modifyQueryUsing: static function (EloquentBuilder $query): void {
                                    $query->where('is_active', true);
                                },
                            )
                            ->preload()->searchable()
                            ->disabled()->dehydrated(false),

                        TextInput::make('gps_lat')->disabled()->dehydrated(false),
                        TextInput::make('gps_lng')->disabled()->dehydrated(false),

                    ]),


                Section::make('Location')
                    ->columns(3)
                    ->schema([
                        ViewField::make('map')
                            ->label('Map')
                            ->view('filament/forms/components/order-map')
                            ->columnSpanFull(),

                        // Links to open in external map apps
                        ViewField::make('map-links')
                            ->view('filament/forms/components/map-links')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                // أقسام تعديل الأدمن فقط


                Section::make('Admin')->schema([
                    Section::make('Assignment (Admin)')
                        ->columns(2)
                        ->schema([
                            Select::make('technical_id_fk')
                                ->label('Technician')
                                ->relationship('technical', 'name')
                                ->preload()
                                // ->disabled(fn(?Model $record) => filled($record?->technical_id_fk))
                                ->dehydrated(fn(?Model $record) => blank($record?->technical_id_fk))
                                ->searchable()
                                ->live() // trigger afterStateUpdated immediately
                                ->afterStateUpdated(function ($state, $set, $get) {
                                    if (filled($state)) {
                                        $set('status', 'assigned');
                                        $set('assigned_by_admin_id_fk', Filament::auth()->id());
                                        if (blank($get('assigned_at'))) {
                                            $set('assigned_at', now());
                                        }
                                    }
                                }),

                            Select::make('assigned_by_admin_id_fk')
                                ->label('Assigned By')
                                ->relationship('assignedBy', 'name')
                                // If value is missing in DB, set it to the current user and stamp assigned_at.
                                ->afterStateHydrated(function (callable $set, ?Model $record, $state) {
                                    if (blank($state)) {
                                        $set('assigned_by_admin_id_fk', Filament::auth()->id());
                                        $set('assigned_at', now());
                                    }
                                })
                                // also works when creating a new record
                                ->default(fn() => Filament::auth()->id())
                                // make it not editable but still saved
                                ->disabled()
                                ->dehydrated(),   // keep the value persisted even when disabled

                            DateTimePicker::make('assigned_at')
                                ->label('Assigned at')
                                ->readOnly()      // or ->disabled()
                                ->dehydrated(),   // persist the value
                            Select::make('status')
                                ->options(OrderStatus::options())
                                ->native(false)
                                ->required(),


                            Textarea::make('assignment_note')->rows(2)->columnSpanFull(),
                        ]),

                    Section::make('Pricing (Admin)')
                        ->columns(3)
                        ->schema([




                            TextInput::make('admin_initial_price')->numeric()->prefix('IQD')
                                ->disabled(fn(?Model $record) => filled($record?->admin_initial_price))
                                ->dehydrated(fn(?Model $record) => blank($record?->admin_initial_price))
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state) {
                                        $set('admin_initial_at', now());
                                    } else {
                                        $set('admin_initial_at', null);
                                    }
                                }),


                            Select::make('assigned_by_admin_id_fk')
                                ->label('Assigned By')
                                ->relationship('assignedBy', 'name')
                                // If value is missing in DB, set it to the current user and stamp assigned_at.
                                ->afterStateHydrated(function (callable $set, ?Model $record, $state) {
                                    if (blank($state)) {
                                        $set('assigned_by_admin_id_fk', Filament::auth()->id());
                                        $set('admin_initial_at', now());
                                    }
                                })
                                // also works when creating a new record
                                ->default(fn() => Filament::auth()->id())
                                // make it not editable but still saved
                                ->disabled()
                                ->dehydrated(),   // keep the value persisted even when disabled

                            DateTimePicker::make('admin_initial_at')
                                ->label('Initial priced at')
                                ->readOnly()      // or ->disabled()
                                ->dehydrated(),   // persist the value





                            Textarea::make('admin_initial_note')->rows(2)->columnSpanFull(),

                        ]),
                ])->columnSpanFull(),



                Section::make('Technician Quote')
                    ->columns(2)
                    ->schema([
                        TextInput::make('technician_quote_price')
                            ->label('Technician price')
                            ->numeric()->prefix('IQD')
                            ->disabled()->dehydrated(false),



                        DateTimePicker::make('technician_quote_at')
                            ->label('Quoted at')
                            ->disabled()->dehydrated(false),
                        Textarea::make('technician_quote_note')
                            ->label('Technician note')
                            ->rows(2)
                            ->disabled()->dehydrated(false)->columnSpanFull(),
                    ])->columnSpanFull(),

                Section::make('Final Pricing (Admin)')

                    ->schema([
                        TextInput::make('final_price')
                            ->numeric()
                            ->prefix('IQD')
                            ->live(debounce: 400)   // so the callback fires as the user types
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (filled($state)) {
                                    $set('status', 'awaiting_customer_approval');
                                    // optionally reset any flags:
                                    // $set('submit', false);
                                    // $set('customer_decided_at', null);
                                }
                            })
                            ->afterStateHydrated(function ($component, $state, callable $set) {
                                if (filled($state)) {
                                    $set('status', 'awaiting_customer_approval');
                                }
                            })
                    ]),

                Section::make('Customer Decision')
                    ->columns(2)
                    ->schema([
                        Toggle::make('submit')
                            ->label('Customer approved?')
                            ->disabled()->dehydrated(false),

                        DateTimePicker::make('customer_decided_at')
                            ->label('Customer decided at')
                            ->disabled()->dehydrated(false),
                    ]),







            ]);
    }
}

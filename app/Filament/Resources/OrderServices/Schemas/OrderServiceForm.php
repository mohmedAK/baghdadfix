<?php

namespace App\Filament\Resources\OrderServices\Schemas;

use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use App\Enums\OrderStatus;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

use Filament\Schemas\Components\Section;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;



use Illuminate\Support\Str;

class OrderServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                    ]),

                // الموقع (قراءة فقط)
                Section::make('Location')
                    ->columns(4)
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

                // وصف/مرفقات الزبون (قراءة فقط)
                Section::make('Customer Inputs')
                    ->columns(2)
                    ->schema([
                        Textarea::make('description')->rows(3)
                            ->disabled()->dehydrated(false),

                        Toggle::make('submit')
                            ->label('Customer approved?')
                            ->disabled()->dehydrated(false),

                        FileUpload::make('image')->image()
                            ->disk('public')->directory('orders')->visibility('public')
                            ->disabled()->dehydrated(false),

                        FileUpload::make('video')
                            ->acceptedFileTypes(['video/*'])
                            ->disk('public')->directory('orders')->visibility('public')
                            ->disabled()->dehydrated(false),
                    ]),

                // أقسام تعديل الأدمن فقط
                Section::make('Assignment (Admin)')
                    ->columns(3)
                    ->schema([
                        Select::make('technical_id_fk')
                            ->label('Technician')
                            ->relationship(
                                name: 'technical',
                                titleAttribute: 'name',
                                // modifyQueryUsing: static fn(EloquentBuilder $q) => $q->where('role', 'technical'),
                            )
                            ->preload()->searchable(),

                        Select::make('assigned_by_admin_id_fk')
                            ->label('Assigned By')
                            ->relationship(
                                name: 'assignedBy',
                                titleAttribute: 'name',
                                // modifyQueryUsing: fn(Builder $q) => $q->where('role', 'admin')
                            )
                            ->preload()->searchable(),

                        DateTimePicker::make('assigned_at'),

                        Textarea::make('assignment_note')->rows(2)->columnSpanFull(),
                    ]),

                Section::make('Technician Quote')
                    ->columns(3)
                    ->schema([
                        TextInput::make('technician_quote_price')
                            ->label('Technician price')
                            ->numeric()->prefix('$')
                            ->disabled()->dehydrated(false),

                        Textarea::make('technician_quote_note')
                            ->label('Technician note')
                            ->rows(2)
                            ->disabled()->dehydrated(false),

                        DateTimePicker::make('technician_quote_at')
                            ->label('Quoted at')
                            ->disabled()->dehydrated(false),
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

                Section::make('Pricing (Admin)')
                    ->columns(3)
                    ->schema([
                        TextInput::make('admin_initial_price')->numeric()->prefix('$'),
                        Select::make('admin_initial_by_id_fk')
                            ->label('Priced By')
                            ->relationship(
                                name: 'adminInitialBy',
                                titleAttribute: 'name',
                                // modifyQueryUsing: static fn(EloquentBuilder $q) => $q->where('role', 'admin'),
                            )
                            ->preload()->searchable(),
                        DateTimePicker::make('admin_initial_at'),
                        Textarea::make('admin_initial_note')->rows(2)->columnSpanFull(),
                        TextInput::make('final_price')->numeric()->prefix('$'),
                    ]),

                Section::make('Status (Admin)')
                    ->schema([
                        Select::make('status')
                            ->options(OrderStatus::options())
                            ->native(false)
                            ->required(),
                    ]),




            ]);
    }
}

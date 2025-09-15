<?php

namespace App\Filament\Resources\OrderServices\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OrderServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('customer_id_fk')
                    ->required(),
                TextInput::make('service_id_fk')
                    ->required(),
                TextInput::make('technical_id_fk')
                    ->default(null),
                TextInput::make('assigned_by_admin_id_fk')
                    ->default(null),
                DateTimePicker::make('assigned_at'),
                TextInput::make('assignment_note')
                    ->default(null),
                TextInput::make('state_id_fk')
                    ->default(null),
                TextInput::make('area_id_fk')
                    ->default(null),
                TextInput::make('gps_lat')
                    ->numeric()
                    ->default(null),
                TextInput::make('gps_lng')
                    ->numeric()
                    ->default(null),
                TextInput::make('admin_initial_price')
                    ->numeric()
                    ->default(null),
                DateTimePicker::make('admin_initial_at'),
                TextInput::make('admin_initial_note')
                    ->default(null),
                TextInput::make('final_price')
                    ->numeric()
                    ->default(null),
                Textarea::make('description')
                    ->default(null)
                    ->columnSpanFull(),
                Select::make('status')
                    ->options([
            'created' => 'Created',
            'admin_estimated' => 'Admin estimated',
            'assigned' => 'Assigned',
            'inspecting' => 'Inspecting',
            'quote_pending' => 'Quote pending',
            'awaiting_customer_approval' => 'Awaiting customer approval',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'in_progress' => 'In progress',
            'completed' => 'Completed',
            'canceled' => 'Canceled',
        ])
                    ->default('created')
                    ->required(),
                Toggle::make('submit')
                    ->required(),
                FileUpload::make('image')
                    ->image(),
                TextInput::make('video')
                    ->default(null),
            ]);
    }
}

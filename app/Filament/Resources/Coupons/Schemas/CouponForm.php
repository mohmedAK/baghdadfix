<?php

namespace App\Filament\Resources\Coupons\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')->unique(ignoreRecord: true)->required()->maxLength(250),
                TextInput::make('discount')->numeric()->minValue(0)->maxValue(100)->suffix('%')->required(),
                Toggle::make('is_active')->default(true),
                DateTimePicker::make('starts_at'),
                DateTimePicker::make('ends_at'),
            ])->columns(2);
    }
}

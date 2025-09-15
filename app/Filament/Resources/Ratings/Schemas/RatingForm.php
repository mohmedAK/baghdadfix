<?php

namespace App\Filament\Resources\Ratings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class RatingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_service_id_fk')
                    ->required(),
                TextInput::make('rater_id_fk')
                    ->required(),
                TextInput::make('technical_id_fk')
                    ->required(),
                TextInput::make('rate')
                    ->required()
                    ->numeric(),
                Textarea::make('comment')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}

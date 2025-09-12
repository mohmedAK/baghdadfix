<?php

namespace App\Filament\Resources\Users\Schemas;

use Dom\Text;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255),


                TextInput::make('state')
                    ->label('State')
                    ->maxLength(250)
                    ->nullable(),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User Information')
                    ->columns(2)
                    ->description('Please provide the user information below.')
                    ->schema([
                        TextInput::make('name')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->default(null),
                        TextInput::make('phone')
                            ->tel()
                            ->default(null)
                            ->required(),
                        Select::make('role')->options(UserRole::labels())->required()->native(false)
                            ->required(),
                        TextInput::make('state')
                            ->default(null),
                        TextInput::make('area')
                            ->default(null),
                        // DateTimePicker::make('email_verified_at'),
                        TextInput::make('password')->revealable()
                            ->password()
                            ->placeholder('Enter password')
                            ->required(),
                    ])->columnSpan(2),
            ]);
    }
}

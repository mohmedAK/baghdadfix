<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Models\ServiceCategory;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->description('Enter the basic details of the service.')
                    ->schema([
                        TextInput::make('name')->rules('string|max:20')
                            ->required(),
                        Select::make('service_category_id_fk')
                            ->label('Service Category')
                            ->relationship('category', 'name')
                            // ->searchable()
                            ->required(),
                    ])->columnSpan(2),
                Section::make('Meta Data')
                    ->description('Add Image and activation status.')
                    ->schema([
                        FileUpload::make('image')
                            ->label('Service Image')
                            ->image()                          // يعرض معاينة ويتأكد أنها صورة
                            ->disk('public')                   // يخزن على قرص public
                            ->directory('services')  // المسار: storage/app/public/services
                            ->visibility('public')
                            ->required(),            // لتكون قابلة للعرض عبر /storage


                        Toggle::make('is_active')
                            ->required(),
                    ])->columnSpan(1)
            ])->columns(3);
    }
}

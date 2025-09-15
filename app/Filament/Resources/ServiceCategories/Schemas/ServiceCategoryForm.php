<?php

namespace App\Filament\Resources\ServiceCategories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServiceCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->description('Enter the basic details of the service category.')
                    ->schema([

                        TextInput::make('name')
                            ->required(),
                        TextInput::make('sort_order')
                            ->required()
                            ->numeric()
                            ->default(0),
                    ])->columnSpan(2),
                Section::make('Meta Data')
                    ->description('Add Image and activation status.')
                    ->schema([
                        FileUpload::make('image')
                            ->label('Category Image')
                            ->image()                          // يعرض معاينة ويتأكد أنها صورة
                            ->disk('public')                   // يخزن على قرص public
                            ->directory('service-categories')  // المسار: storage/app/public/service_categories
                            ->visibility('public'),
                        Toggle::make('is_active')
                            ->required(),
                    ])->columnSpan(1)

            ])->columns(3);
    }
}

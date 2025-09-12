<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Models\ServiceCategory;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                FileUpload::make('image')
                    ->label('Service Image')
                    ->image()                          // يعرض معاينة ويتأكد أنها صورة
                    ->disk('public')                   // يخزن على قرص public
                    ->directory('services')  // المسار: storage/app/public/services
                    ->visibility('public'),            // لتكون قابلة للعرض عبر /storage
                Select::make('service_category_id_fk')
                    ->label('Service Category')
                    ->options(ServiceCategory::query()->pluck('name', 'id'))
                    ->searchable(),

                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}

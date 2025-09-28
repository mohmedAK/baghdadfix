<?php

namespace App\Filament\Resources\OrderServices\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

use Filament\Infolists\Components\ViewEntry;
use Illuminate\Support\Facades\Storage;
class OrderServiceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

            ]);
    }
}

<?php

namespace App\Filament\Resources\OrderServices\Pages;

use App\Filament\Resources\OrderServices\OrderServiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrderService extends CreateRecord
{
    protected static string $resource = OrderServiceResource::class;
}

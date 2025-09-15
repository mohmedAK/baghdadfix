<?php

namespace App\Filament\Resources\OrderServices\Pages;

use App\Filament\Resources\OrderServices\OrderServiceResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditOrderService extends EditRecord
{
    protected static string $resource = OrderServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}

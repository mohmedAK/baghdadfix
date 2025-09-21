<?php

namespace App\Filament\Resources\OrderServices\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UsedCouponsRelationManager extends RelationManager
{
    protected static string $relationship = 'usedCoupons';
    protected static ?string $title = 'Used Coupons';
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('used_at')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('used_at')
            ->columns([
                TextColumn::make('coupon.code')->label('Code')->searchable(),
                TextColumn::make('coupon.discount')->label('Discount'),
                TextColumn::make('used_at')->dateTime()->since(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->headerActions([

            ])
            ->recordActions([

            ])
            ->toolbarActions([


            ])
            ->modifyQueryUsing(fn(Builder $query) => $query
                ->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ]));
    }
}

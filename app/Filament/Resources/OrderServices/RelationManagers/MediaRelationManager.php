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
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;

class MediaRelationManager extends RelationManager
{
    protected static string $relationship = 'media';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('id')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('type')
                    ->badge()
                    ->color(fn(string $t) => $t === 'image' ? 'success' : 'info'),

                // thumbnail للصورة
                ImageColumn::make('path')
                    ->disk(fn($r) => $r->disk ?? 'public')
                    ->square()
                    ->visible(fn($r) => $r->type === 'image'),

                // رابط الملف (صورة/فيديو)
                TextColumn::make('url')
                    ->label('URL')
                    ->url(fn($r) => Storage::disk($r->disk ?? 'public')->url($r->path), true)
                    ->openUrlInNewTab()
                    ->copyable(),

                TextColumn::make('sort_order')->sortable(),
                TextColumn::make('created_at')->since(),
                TextColumn::make('deleted_at')->since()->label('Deleted')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([])

            ->modifyQueryUsing(fn(Builder $query) => $query
                ->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ]));
    }
}

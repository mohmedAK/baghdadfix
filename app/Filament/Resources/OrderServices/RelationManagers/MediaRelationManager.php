<?php

namespace App\Filament\Resources\OrderServices\RelationManagers;

use Filament\Actions\Action;
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
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
            ->defaultSort('sort_order')
            ->columns([
                ViewColumn::make('preview')
                    ->label('Preview')
                    ->view('filament.tables.media-thumb')
                    ->grow(false) // لا يتمدّد مع الصف
                    ->extraAttributes([
                        // نثبت عرض خلية العمود نفسها كي لا تتمدّد
                        'style' => 'width:136px;min-width:136px;max-width:136px;',
                    ]),
                TextColumn::make('sort_order')->sortable(),
                TextColumn::make('created_at')->since()->sortable(),
            ])

            ->filters([
                TrashedFilter::make(),
            ])
            ->headerActions([])
            ->recordActions([
                Action::make('preview')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Media preview')
                    ->modalWidth('7xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(
                        /** @param OrderServiceMedia $record */
                        fn($record) => view('filament.modals.media-preview', [
                            'isImage' => $record->type === 'image',
                            'url'     => Storage::disk('public')->url($record->file_path),
                            'mime'    => $record->mime,
                        ])
                    ),
            ])
            ->toolbarActions([])

            ->modifyQueryUsing(fn(Builder $query) => $query
                ->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ]));
    }
}

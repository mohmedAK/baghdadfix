<?php

namespace App\Filament\Resources\ServiceCategories\RelationManagers;

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
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServicesRelationManager extends RelationManager
{
    protected static string $relationship = 'services';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->description('Enter the basic details of the service.')
                    ->schema([
                        TextInput::make('name')->rules('string|max:20')
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->rowIndex()        // per-page index
                    ->alignCenter()
                    ->sortable()
                    ->searchable(),
                ImageColumn::make('image')->disk('public')->square(),
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),

            ])
            ->headerActions([
                CreateAction::make(),

            ])
            ->recordActions([
                EditAction::make(),

                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([

                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(
                fn(Builder $query) => $query
                    ->withoutGlobalScopes([
                        SoftDeletingScope::class,
                    ])
            );
    }
}

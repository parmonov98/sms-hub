<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProviderResource\Pages;
use App\Filament\Resources\ProviderResource\RelationManagers;
use App\Models\Provider;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProviderResource extends Resource
{
    protected static ?string $model = Provider::class;

    protected static ?string $navigationIcon = 'heroicon-o-device-phone-mobile';
    protected static ?string $navigationLabel = 'SMS Providers';
    protected static ?string $modelLabel = 'SMS Provider';
    protected static ?string $pluralModelLabel = 'SMS Providers';
    protected static ?string $navigationGroup = 'SMS Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Provider Information')
                    ->schema([
                        Forms\Components\TextInput::make('display_name')
                            ->label('Provider Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Eskiz SMS Provider'),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->placeholder('Brief description of the SMS provider')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('priority')
                            ->label('Priority')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower numbers have higher priority (0 = highest)'),

                        Forms\Components\Toggle::make('is_enabled')
                            ->label('Enabled')
                            ->required()
                            ->default(true)
                            ->helperText('Enable or disable this provider'),
                    ])->columns(2),

                Forms\Components\Section::make('Provider Capabilities')
                    ->schema([
                        Forms\Components\CheckboxList::make('capabilities')
                            ->label('Supported Features')
                            ->options([
                                'dlr' => 'Delivery Reports (DLR)',
                                'unicode' => 'Unicode Support',
                                'concatenation' => 'Long Message Concatenation',
                                'flash' => 'Flash Messages',
                                'binary' => 'Binary Messages',
                                'wap_push' => 'WAP Push Messages',
                            ])
                            ->columns(2)
                            ->required()
                            ->helperText('Select the features this provider supports'),
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('display_name')
                    ->label('Provider Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),

                Tables\Columns\TextColumn::make('capabilities')
                    ->label('Capabilities')
                    ->badge()
                    ->separator(',')
                    ->color(fn (string $state): string => match ($state) {
                        'dlr' => 'success',
                        'unicode' => 'info',
                        'concatenation' => 'warning',
                        'flash' => 'primary',
                        'binary' => 'secondary',
                        'wap_push' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('priority')
                    ->label('Priority')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state === 0 => 'success',
                        $state <= 5 => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('is_enabled')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('messages_count')
                    ->label('Messages')
                    ->counts('messages')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_enabled')
                    ->label('Status')
                    ->placeholder('All providers')
                    ->trueLabel('Enabled only')
                    ->falseLabel('Disabled only'),

                Tables\Filters\SelectFilter::make('capabilities')
                    ->label('Capability')
                    ->options([
                        'dlr' => 'Delivery Reports',
                        'unicode' => 'Unicode Support',
                        'concatenation' => 'Concatenation',
                        'flash' => 'Flash Messages',
                        'binary' => 'Binary Messages',
                        'wap_push' => 'WAP Push',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $capability): Builder => $query->whereJsonContains('capabilities', $capability),
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('priority', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProviders::route('/'),
            'create' => Pages\CreateProvider::route('/create'),
            'view' => Pages\ViewProvider::route('/{record}'),
            'edit' => Pages\EditProvider::route('/{record}/edit'),
        ];
    }
}

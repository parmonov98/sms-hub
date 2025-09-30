<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MessageResource\Pages;
use App\Filament\Resources\MessageResource\RelationManagers;
use App\Models\Message;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MessageResource extends Resource
{
    protected static ?string $model = Message::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    
    protected static ?string $navigationLabel = 'SMS Messages';
    
    protected static ?string $modelLabel = 'SMS Message';
    
    protected static ?string $pluralModelLabel = 'SMS Messages';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Message Details')
                    ->schema([
                        Forms\Components\TextInput::make('to')
                            ->label('Recipient')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('+998901234567'),
                        
                        Forms\Components\TextInput::make('from')
                            ->label('Sender')
                            ->maxLength(255)
                            ->placeholder('SMSHub'),
                        
                        Forms\Components\Textarea::make('text')
                            ->label('Message Content')
                            ->required()
                            ->columnSpanFull()
                            ->rows(4)
                            ->placeholder('Enter your SMS message here...'),
                        
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'queued' => 'Queued',
                                'sent' => 'Sent',
                                'delivered' => 'Delivered',
                                'failed' => 'Failed',
                                'failed' => 'Failed',
                            ])
                            ->required()
                            ->default('queued'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Technical Details')
                    ->schema([
                        Forms\Components\TextInput::make('provider_message_id')
                            ->label('Provider Message ID')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('parts')
                            ->label('Message Parts')
                            ->numeric()
                            ->default(1),
                        
                        Forms\Components\TextInput::make('error_code')
                            ->label('Error Code')
                            ->maxLength(255),
                        
                        Forms\Components\Textarea::make('error_message')
                            ->label('Error Message')
                            ->columnSpanFull()
                            ->rows(2),
                        
                        Forms\Components\TextInput::make('price_decimal')
                            ->label('Price')
                            ->numeric()
                            ->step(0.01),
                        
                        Forms\Components\TextInput::make('currency')
                            ->label('Currency')
                            ->maxLength(3)
                            ->default('UZS'),
                    ])->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                
                Tables\Columns\TextColumn::make('to')
                    ->label('Recipient')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                
                Tables\Columns\TextColumn::make('from')
                    ->label('Sender')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('text')
                    ->label('Message')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'queued',
                        'success' => 'sent',
                        'success' => 'delivered',
                        'danger' => 'failed',
                    ])
                    ->icons([
                        'heroicon-o-clock' => 'queued',
                        'heroicon-o-paper-airplane' => 'sent',
                        'heroicon-o-check-circle' => 'delivered',
                        'heroicon-o-x-circle' => 'failed',
                    ]),
                
                Tables\Columns\TextColumn::make('parts')
                    ->label('Parts')
                    ->numeric()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('price_decimal')
                    ->label('Price')
                    ->formatStateUsing(function ($state, $record) {
                        if (!$state) return 'N/A';
                        $currency = $record->currency ?? 'UZS';
                        return number_format($state, 2) . ' ' . $currency;
                    })
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('provider_message_id')
                    ->label('Provider ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('error_code')
                    ->label('Error')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Sent At')
                    ->dateTime()
                    ->sortable()
                    ->since(),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'queued' => 'Queued',
                        'sent' => 'Sent',
                        'delivered' => 'Delivered',
                        'failed' => 'Failed',
                    ]),
                
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('From Date'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
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
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListMessages::route('/'),
            'view' => Pages\ViewMessage::route('/{record}'),
        ];
    }
}

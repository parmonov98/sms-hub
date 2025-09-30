<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OAuthClientResource\Pages;
use App\Models\OAuthClient;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class OAuthClientResource extends Resource
{
    protected static ?string $model = OAuthClient::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationLabel = 'OAuth Clients';

    protected static ?string $modelLabel = 'OAuth Client';

    protected static ?string $pluralModelLabel = 'OAuth Clients';

    protected static ?string $navigationGroup = 'Authentication';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Client Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Client Name')
                            ->placeholder('My API Client'),
                        
                        Forms\Components\Select::make('owner_id')
                            ->label('Owner')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        
                        Forms\Components\TextInput::make('secret')
                            ->label('Client Secret')
                            ->disabled()
                            ->dehydrated(true)
                            ->helperText('This will be generated automatically when creating a new client. The secret is automatically hashed for security.'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Client Configuration')
                    ->schema([
                        Forms\Components\TextInput::make('redirect_uris')
                            ->label('Redirect URIs')
                            ->placeholder('https://example.com/callback')
                            ->helperText('Comma-separated list of redirect URIs (optional)')
                            ->nullable(),
                        
                        Forms\Components\CheckboxList::make('grant_types')
                            ->label('Grant Types')
                            ->options([
                                'authorization_code' => 'Authorization Code',
                                'password' => 'Password',
                                'client_credentials' => 'Client Credentials',
                                'personal_access' => 'Personal Access',
                                'refresh_token' => 'Refresh Token',
                            ])
                            ->columns(2),
                        
                        Forms\Components\Toggle::make('revoked')
                            ->label('Revoked')
                            ->helperText('Revoke this client to prevent further access'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Client ID')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Client ID copied!')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Owner')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('grant_types')
                    ->label('Grant Types')
                    ->badge()
                    ->separator(',')
                    ->color(fn (string $state): string => match ($state) {
                        'authorization_code' => 'success',
                        'password' => 'warning',
                        'client_credentials' => 'info',
                        'personal_access' => 'primary',
                        'refresh_token' => 'secondary',
                        default => 'gray',
                    }),
                
                Tables\Columns\IconColumn::make('revoked')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-x-circle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('grant_types')
                    ->label('Grant Type')
                    ->options([
                        'authorization_code' => 'Authorization Code',
                        'password' => 'Password',
                        'client_credentials' => 'Client Credentials',
                        'personal_access' => 'Personal Access',
                        'refresh_token' => 'Refresh Token',
                    ]),
                
                Tables\Filters\TernaryFilter::make('revoked')
                    ->label('Revoked'),
            ])
            ->actions([
                Tables\Actions\Action::make('view_secret')
                    ->label('View Secret')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading('Client Secret')
                    ->modalContent(fn (OAuthClient $record) => view('filament.modals.client-secret', [
                        'clientId' => $record->id,
                        'clientSecret' => $record->secret,
                    ]))
                    ->modalActions([
                        Tables\Actions\Action::make('copy_secret')
                            ->label('Copy Secret')
                            ->color('primary')
                            ->action(function (OAuthClient $record) {
                                $this->js("navigator.clipboard.writeText('{$record->secret}')");
                                Notification::make()
                                    ->title('Client Secret Copied!')
                                    ->success()
                                    ->send();
                            }),
                    ]),
                
                Tables\Actions\Action::make('regenerate_secret')
                    ->label('Regenerate Secret')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (OAuthClient $record) {
                        $record->update([
                            'secret' => Str::random(40),
                        ]);
                    })
                    ->visible(fn (OAuthClient $record) => !$record->revoked),
                
                Tables\Actions\Action::make('revoke')
                    ->label('Revoke')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (OAuthClient $record) {
                        $record->update(['revoked' => true]);
                    })
                    ->visible(fn (OAuthClient $record) => !$record->revoked),
                
                Tables\Actions\Action::make('restore')
                    ->label('Restore')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (OAuthClient $record) {
                        $record->update(['revoked' => false]);
                    })
                    ->visible(fn (OAuthClient $record) => $record->revoked),
                
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOAuthClients::route('/'),
            'create' => Pages\CreateOAuthClient::route('/create'),
            'edit' => Pages\EditOAuthClient::route('/{record}/edit'),
        ];
    }
}

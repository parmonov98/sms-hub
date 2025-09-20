<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SmsTemplateResource\Pages;
use App\Filament\Resources\SmsTemplateResource\RelationManagers;
use App\Models\SmsTemplate;
use App\Models\Provider;
use App\Services\EskizTemplateService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;
use Filament\Actions\Action;

class SmsTemplateResource extends Resource
{
    protected static ?string $model = SmsTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationLabel = 'SMS Templates';
    
    protected static ?string $modelLabel = 'SMS Template';
    
    protected static ?string $pluralModelLabel = 'SMS Templates';
    
    protected static ?string $navigationGroup = 'SMS Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Template Information')
                    ->schema([
                        Forms\Components\Select::make('provider_id')
                            ->label('SMS Provider')
                            ->options(Provider::all()->pluck('display_name', 'id'))
                            ->required()
                            ->searchable(),

                        Forms\Components\TextInput::make('name')
                            ->label('Template Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Welcome Message'),

                        Forms\Components\Textarea::make('content')
                            ->label('Template Content')
                            ->required()
                            ->rows(4)
                            ->placeholder('Enter your SMS template content. Use {variable} for dynamic content.')
                            ->helperText('Use {name}, {code}, {amount} etc. for dynamic variables'),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending Approval',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->default('pending')
                            ->required(),

                        Forms\Components\TextInput::make('provider_template_id')
                            ->label('Provider Template ID')
                            ->placeholder('Will be filled automatically when submitted to provider')
                            ->disabled(),

                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->rows(2)
                            ->placeholder('Reason for rejection (if applicable)')
                            ->disabled(fn ($get) => $get('status') !== 'rejected'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Template Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('provider.display_name')
                    ->label('Provider')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('content')
                    ->label('Content')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                    }),

                Tables\Columns\TextColumn::make('provider_template_id')
                    ->label('Provider ID')
                    ->placeholder('Not submitted')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('approved_at')
                    ->label('Approved')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('provider_id')
                    ->label('Provider')
                    ->options(Provider::all()->pluck('display_name', 'id')),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('submit_to_provider')
                    ->label('Submit to Provider')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->visible(fn (SmsTemplate $record): bool => $record->status === 'pending' && $record->provider->display_name === 'eskiz')
                    ->action(function (SmsTemplate $record) {
                        $eskizService = app(EskizTemplateService::class);
                        $token = $record->provider->accessToken;
                        
                        if (!$token || !$token->isValid()) {
                            Notification::make()
                                ->title('No valid token found')
                                ->body('Please refresh the provider token first')
                                ->danger()
                                ->send();
                            return;
                        }
                        
                        $result = $eskizService->submitTemplateToEskiz($record, $token->token_value);
                        
                        if ($result['status'] === 'success') {
                            Notification::make()
                                ->title('Template submitted successfully')
                                ->body('Template has been submitted to Eskiz for approval')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Submission failed')
                                ->body($result['error'] ?? 'Unknown error')
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Tables\Actions\Action::make('sync_from_eskiz')
                    ->label('Sync from Eskiz')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->action(function () {
                        $eskizService = app(EskizTemplateService::class);
                        $result = $eskizService->syncTemplatesFromEskiz();
                        
                        if ($result['status'] === 'success') {
                            Notification::make()
                                ->title('Templates synced successfully')
                                ->body($result['message'])
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Sync failed')
                                ->body($result['error'] ?? 'Unknown error')
                                ->danger()
                                ->send();
                        }
                    }),
            ]);
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
            'index' => Pages\ListSmsTemplates::route('/'),
            'create' => Pages\CreateSmsTemplate::route('/create'),
            'edit' => Pages\EditSmsTemplate::route('/{record}/edit'),
        ];
    }
}

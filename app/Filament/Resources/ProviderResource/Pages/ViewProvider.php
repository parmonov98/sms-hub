<?php

namespace App\Filament\Resources\ProviderResource\Pages;

use App\Filament\Resources\ProviderResource;
use App\Services\EskizTemplateService;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Resources\Components\Tab;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\SmsTemplate;

class ViewProvider extends ViewRecord
{
    protected static string $resource = ProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('sync_templates')
                ->label('Sync Templates')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->visible(fn () => $this->record->display_name === 'eskiz')
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
        ];
    }

    public function getTabs(): array
    {
        return [
            'overview' => Tab::make('Overview'),
            'templates' => Tab::make('Templates')
                ->badge(fn () => $this->record->templates()->count())
                ->content(function () {
                    return view('filament.pages.provider-templates', [
                        'provider' => $this->record,
                        'templates' => $this->record->templates()->with('provider')->get(),
                    ]);
                }),
        ];
    }
}

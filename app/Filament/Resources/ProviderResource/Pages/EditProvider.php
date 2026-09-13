<?php

namespace App\Filament\Resources\ProviderResource\Pages;

use App\Filament\Resources\ProviderResource;
use App\Services\SmsService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProvider extends EditRecord
{
    protected static string $resource = ProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Persist the MobSMS API key (a non-Provider column) into the database as
     * a non-expiring ProviderToken. Only updates when a new key is entered.
     */
    protected function afterSave(): void
    {
        $apiKey = data_get($this->data, 'api_key');

        if (filled($apiKey)) {
            app(SmsService::class)->storeProviderToken(
                $this->record->id,
                'access',
                $apiKey
            );
        }
    }
}

<?php

namespace App\Filament\Resources\ProviderResource\Pages;

use App\Filament\Resources\ProviderResource;
use App\Services\SmsService;
use Filament\Resources\Pages\CreateRecord;

class CreateProvider extends CreateRecord
{
    protected static string $resource = ProviderResource::class;

    /**
     * Persist the MobSMS API key (a non-Provider column) into the database as
     * a non-expiring ProviderToken once the provider record exists.
     */
    protected function afterCreate(): void
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

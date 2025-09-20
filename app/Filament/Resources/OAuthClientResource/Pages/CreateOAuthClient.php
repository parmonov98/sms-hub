<?php

namespace App\Filament\Resources\OAuthClientResource\Pages;

use App\Filament\Resources\OAuthClientResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateOAuthClient extends CreateRecord
{
    protected static string $resource = OAuthClientResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generate a random client secret
        $data['secret'] = Str::random(40);
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

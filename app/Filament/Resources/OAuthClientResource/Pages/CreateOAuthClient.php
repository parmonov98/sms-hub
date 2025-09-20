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
        
        // Set default values
        $data['owner_type'] = 'App\\Models\\User';
        $data['provider'] = 'users';
        
        // Convert redirect_uris string to array if provided
        if (isset($data['redirect_uris']) && is_string($data['redirect_uris'])) {
            $data['redirect_uris'] = array_filter(array_map('trim', explode(',', $data['redirect_uris'])));
        }
        
        // Ensure grant_types is an array
        if (!isset($data['grant_types']) || !is_array($data['grant_types'])) {
            $data['grant_types'] = ['client_credentials'];
        }
        
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

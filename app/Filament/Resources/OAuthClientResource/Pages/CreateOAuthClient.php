<?php

namespace App\Filament\Resources\OAuthClientResource\Pages;

use App\Filament\Resources\OAuthClientResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateOAuthClient extends CreateRecord
{
    protected static string $resource = OAuthClientResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generate a UUID for the client ID
        $data['id'] = Str::uuid();
        
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

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'OAuth Client Created Successfully';
    }

    protected function getCreatedNotification(): ?Notification
    {
        $client = $this->record;
        
        return Notification::make()
            ->title('OAuth Client Created Successfully')
            ->body("Client ID: {$client->id}\nClient Secret: {$client->secret}")
            ->success()
            ->persistent()
            ->actions([
                Action::make('copy_secret')
                    ->label('Copy Secret')
                    ->action(function () use ($client) {
                        // This will copy the secret to clipboard via JavaScript
                        $this->js("navigator.clipboard.writeText('{$client->secret}')");
                        Notification::make()
                            ->title('Client Secret Copied!')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}

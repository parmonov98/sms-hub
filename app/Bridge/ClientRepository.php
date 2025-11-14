<?php

namespace App\Bridge;

use Laravel\Passport\Bridge\ClientRepository as BaseClientRepository;
use Laravel\Passport\ClientRepository as ClientModelRepository;
use League\OAuth2\Server\Entities\ClientEntityInterface;

class ClientRepository extends BaseClientRepository
{
    /**
     * Create a new client repository.
     */
    public function __construct(ClientModelRepository $clients, \Illuminate\Contracts\Hashing\Hasher $hasher)
    {
        parent::__construct($clients, $hasher);
    }

    /**
     * {@inheritdoc}
     */
    public function validateClient(string $clientIdentifier, ?string $clientSecret, ?string $grantType): bool
    {
        $record = $this->clients->findActive($clientIdentifier);

        if (!$record || empty($clientSecret)) {
            return false;
        }

        // Check if secret matches (plain text comparison for Filament-created clients)
        if ($clientSecret !== $record->secret) {
            return false;
        }

        // Validate that the client supports the requested grant type
        $grantTypes = $record->grant_types ?? [];
        if (!is_array($grantTypes)) {
            $grantTypes = json_decode($grantTypes, true) ?? [];
        }

        // For client_credentials grant, check if it's in the grant_types array
        if ($grantType === 'client_credentials') {
            return in_array('client_credentials', $grantTypes);
        }

        // For other grant types, check if they're supported
        return in_array($grantType, $grantTypes);
    }
}

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

        // For OAuth clients, compare secrets as plain text (not hashed)
        // This allows Filament-created clients to work with OAuth authentication
        return $clientSecret === $record->secret;
    }
}

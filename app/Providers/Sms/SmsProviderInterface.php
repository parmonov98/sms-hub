<?php

namespace App\Providers\Sms;

interface SmsProviderInterface
{
    /**
     * Send an SMS message.
     *
     * @param string $to Recipient phone number
     * @param string $from Sender ID
     * @param string $text Message content
     * @param array $options Additional options
     * @return array Response with status, message_id, and cost information
     */
    public function send(string $to, string $from, string $text, array $options = []): array;

    /**
     * Check the status of a message.
     *
     * @param string $messageId Provider's message ID
     * @return array Status information
     */
    public function checkStatus(string $messageId): array;

    /**
     * Get the provider's capabilities.
     *
     * @return array List of capabilities (dlr, unicode, concat, etc.)
     */
    public function getCapabilities(): array;

    /**
     * Get the provider's name.
     *
     * @return string Provider name
     */
    public function getName(): string;

    /**
     * Validate the provider configuration.
     *
     * @param array $config Provider configuration
     * @return bool True if valid
     */
    public function validateConfig(array $config): bool;
}

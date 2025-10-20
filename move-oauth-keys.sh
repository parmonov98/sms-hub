#!/bin/bash

# Script to move OAuth keys from storage to config directory
# Run this on your production server

echo "Moving OAuth keys to config directory..."

# Create config directory if it doesn't exist
mkdir -p /var/www/smshub.devdata.uz/public_html/config

# Generate new OAuth keys in config directory
cd /var/www/smshub.devdata.uz/public_html

# Generate private key
openssl genrsa -out config/oauth-private.key 2048

# Generate public key from private key
openssl rsa -in config/oauth-private.key -pubout -out config/oauth-public.key

# Set proper permissions
chmod 600 config/oauth-private.key
chmod 644 config/oauth-public.key

# Update .env file with new key paths
echo "" >> .env
echo "# OAuth Keys (moved to config directory for better security)" >> .env
echo "PASSPORT_PRIVATE_KEY=/var/www/smshub.devdata.uz/public_html/config/oauth-private.key" >> .env
echo "PASSPORT_PUBLIC_KEY=/var/www/smshub.devdata.uz/public_html/config/oauth-public.key" >> .env

# Clear config cache
php artisan config:clear

echo "OAuth keys moved successfully!"
echo "Private key: config/oauth-private.key (600 permissions)"
echo "Public key: config/oauth-public.key (644 permissions)"
echo "Environment variables updated in .env file"

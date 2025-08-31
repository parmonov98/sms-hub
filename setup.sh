#!/bin/bash

# Create Laravel project with latest version (Laravel 12)
composer create-project laravel/laravel:^12.0 . --prefer-dist --no-dev

# Install additional packages
composer require laravel/passport
composer require laravel/telescope
composer require guzzlehttp/guzzle
composer require predis/predis

# Set proper permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Install Passport
php artisan passport:install

# Publish Telescope assets
php artisan telescope:install

# Run migrations
php artisan migrate

echo "Laravel project setup completed!"

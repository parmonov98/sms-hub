#!/bin/bash

# SMS Hub Production Deployment Script
set -e

echo "🚀 Starting SMS Hub production deployment..."

# Check if .env.production exists
if [ ! -f .env.production ]; then
    echo "❌ .env.production file not found. Please create it from env.production.example"
    exit 1
fi

# Build and start production containers
echo "📦 Building production containers..."
docker compose -f docker-compose.prod.yml build

echo "🔄 Starting production services..."
docker compose -f docker-compose.prod.yml up -d

# Wait for services to be ready
echo "⏳ Waiting for services to be ready..."
sleep 30

# Run production setup
echo "🔧 Running production setup..."
docker compose -f docker-compose.prod.yml exec app php artisan config:cache
docker compose -f docker-compose.prod.yml exec app php artisan route:cache
docker compose -f docker-compose.prod.yml exec app php artisan view:cache

# Run migrations
echo "🗄️ Running database migrations..."
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force

# Install Passport
echo "🔐 Installing Laravel Passport..."
docker compose -f docker-compose.prod.yml exec app php artisan passport:install --force

# Publish Telescope assets
echo "🔍 Publishing Telescope assets..."
docker compose -f docker-compose.prod.yml exec app php artisan telescope:publish

# Start queue workers
echo "📬 Starting queue workers..."
docker compose -f docker-compose.prod.yml exec -d app php artisan queue:work --daemon

echo "✅ SMS Hub production deployment completed!"
echo "🌐 Application: https://your-domain.com"
echo "🔍 Telescope: https://your-domain.com/telescope"

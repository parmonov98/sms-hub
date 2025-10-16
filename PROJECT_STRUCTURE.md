# SMS Hub Project Structure

## Overview

The SMS Hub is a Laravel-based API service that provides a unified interface for multiple projects to send SMS through various providers. This document outlines the project structure and key components.

## Directory Structure

```
sms-hub/
├── docker/                          # Docker configuration files
│   ├── mysql/                       # MySQL configuration
│   │   ├── data/                    # MySQL data directory
│   │   └── my.cnf                   # MySQL configuration
│   ├── nginx/                       # Nginx configuration
│   │   └── conf.d/                  # Nginx site configurations
│   │       ├── app.conf             # Development configuration
│   │       └── prod.conf            # Production configuration
│   ├── php/                         # PHP configuration
│   │   └── local.ini                # PHP settings
│   └── redis/                       # Redis configuration
│       └── data/                    # Redis data directory
├── app/                             # Laravel application code
│   ├── Console/                     # Artisan commands
│   ├── Http/                        # HTTP layer
│   │   ├── Controllers/             # Controllers
│   │   ├── Middleware/              # Middleware
│   │   └── Requests/                # Form requests
│   ├── Jobs/                        # Queue jobs
│   ├── Models/                      # Eloquent models
│   ├── Providers/                   # SMS provider implementations
│   └── Services/                    # Business logic services
├── config/                          # Configuration files
├── database/                        # Database files
│   ├── factories/                   # Model factories
│   ├── migrations/                  # Database migrations
│   └── seeders/                     # Database seeders
├── public/                          # Public assets
├── resources/                       # Frontend resources
├── routes/                          # Route definitions
├── storage/                         # Application storage
└── tests/                           # Test files
```

## Key Components

### 1. Docker Setup

#### Development Environment
- `docker-compose.yml` - Development services configuration
- `Dockerfile` - Development PHP container
- `docker/nginx/conf.d/app.conf` - Development Nginx configuration

#### Production Environment
- `docker-compose.prod.yml` - Production services configuration
- `Dockerfile.prod` - Production-optimized PHP container
- `docker/nginx/conf.d/prod.conf` - Production Nginx configuration with SSL

### 2. Laravel Application

#### Core Features
- **Authentication**: Laravel Passport (OAuth2)
- **Monitoring**: Laravel Telescope
- **Queue System**: Redis-based job processing
- **Caching**: Redis for session and cache storage

#### SMS Provider System
- **Provider Drivers**: Modular SMS provider implementations
- **Credential Management**: Encrypted storage of provider credentials
- **Failover Logic**: Automatic provider switching on failure
- **Webhook Handling**: Delivery report processing

### 3. Database Schema

#### Core Tables
- `users` - Admin and project users
- `projects` - Client projects with isolated access
- `providers` - SMS provider definitions
- `provider_credentials` - Encrypted provider credentials per project
- `messages` - SMS message records
- `usage_daily` - Daily usage aggregation

#### Supporting Tables
- `oauth_clients` - OAuth2 client applications
- `oauth_access_tokens` - Access tokens
- `oauth_refresh_tokens` - Refresh tokens
- `telescope_entries` - Telescope monitoring data
- `telescope_monitoring` - Telescope monitoring configuration

### 4. API Endpoints

#### Authentication
- `POST /oauth/token` - OAuth2 token endpoint
- `POST /v1/auth/refresh` - Token refresh

#### Admin Endpoints
- `GET /v1/admin/providers` - List providers
- `POST /v1/admin/providers` - Create provider
- `PATCH /v1/admin/providers/{id}` - Update provider
- `DELETE /v1/admin/providers/{id}` - Delete provider

#### Project Endpoints
- `GET /v1/providers` - List available providers
- `POST /v1/messages` - Send SMS
- `GET /v1/messages/{id}` - Get message status
- `GET /v1/usage` - Get usage summary

### 5. Security Features

#### Authentication & Authorization
- OAuth2 with Laravel Passport
- Role-based access control
- API key authentication for projects
- IP allowlist per project

#### Data Protection
- Encrypted credential storage
- HMAC request signing
- Webhook signature verification
- Rate limiting per project

### 6. Monitoring & Observability

#### Laravel Telescope
- Request/response monitoring
- Database query tracking
- Job execution monitoring
- Error tracking
- Performance metrics

#### Logging
- Structured logging with Laravel's logging system
- Error tracking and alerting
- Audit logs for admin actions

### 7. Queue System

#### Job Types
- `SendSmsJob` - SMS sending jobs
- `ProcessDeliveryReportJob` - Webhook processing
- `UpdateMessageStatusJob` - Status updates
- `AggregateUsageJob` - Daily usage aggregation

#### Queue Configuration
- Redis as queue driver
- Multiple queue workers for scalability
- Failed job handling and retry logic

### 8. SMS Provider System

#### Provider Interface
- Standardized provider interface
- Support for multiple providers (Eskiz, PlayMobile, SMPP)
- Configurable capabilities (DLR, Unicode, Concatenation)
- Priority-based failover

#### Provider Management
- Admin CRUD for provider definitions
- Per-project credential management
- Provider enable/disable functionality
- Configuration validation

## Development Workflow

### Local Development
1. Start containers: `make dev` or `docker compose up -d`
2. Install dependencies: `make install`
3. Run migrations: `make migrate`
4. Access application: http://localhost:8000
5. Access Telescope: http://localhost:8000/telescope

### Production Deployment
1. Configure environment: Copy `env.production.example` to `.env.production`
2. Run deployment: `./deploy.sh`
3. Monitor with Telescope: https://your-domain.com/telescope

## Configuration Files

### Environment Files
- `env.development.example` - Development environment template
- `env.production.example` - Production environment template
- `.env` - Current environment configuration

### Docker Files
- `docker-compose.yml` - Development services
- `docker-compose.prod.yml` - Production services
- `Dockerfile` - Development container
- `Dockerfile.prod` - Production container

### Scripts
- `setup.sh` - Initial project setup
- `deploy.sh` - Production deployment
- `Makefile` - Development commands

## Next Steps

After setting up the Docker environment:

1. **Implement Core Models**: Create Eloquent models for all entities
2. **Create Migrations**: Set up database schema
3. **Build Controllers**: Implement API endpoints
4. **Add Provider Drivers**: Create SMS provider implementations
5. **Implement Jobs**: Set up queue jobs for SMS processing
6. **Add Authentication**: Configure OAuth2 with Passport
7. **Create Admin Interface**: Build admin CRUD operations
8. **Add Tests**: Write comprehensive test suite
9. **Configure Monitoring**: Set up Telescope and logging
10. **Deploy**: Deploy to production environment

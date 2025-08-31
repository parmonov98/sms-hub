# SMS Hub - Quick Start Guide

Get the SMS Hub project up and running in minutes!

## Prerequisites

- Docker Desktop installed and running
- Git

## Quick Setup (5 minutes)

### 1. Clone and Navigate
```bash
git clone <repository-url>
cd sms-hub
```

### 2. Start the Environment
```bash
# Start Docker containers
docker compose up -d

# Install Laravel and dependencies
docker compose exec app ./setup.sh

# Configure environment
docker compose exec app cp env.development.example .env
docker compose exec app php artisan key:generate

# Run initial setup
docker compose exec app php artisan migrate
docker compose exec app php artisan passport:install
docker compose exec app php artisan telescope:install
```

### 3. Access the Application
- **Main App**: http://localhost:8000
- **Telescope**: http://localhost:8000/telescope
- **API Docs**: http://localhost:8000/api/documentation

## Using Make Commands (Recommended)

For easier development, use the provided Makefile:

```bash
# Start development environment
make dev

# Run migrations
make migrate

# Access shell
make shell

# Run tests
make test

# View logs
make logs

# Stop containers
make down
```

## Development Commands

### Laravel Commands
```bash
# Run artisan commands
make artisan cmd="migrate"
make artisan cmd="make:controller Api/MessageController"
make artisan cmd="queue:work"

# Or directly with docker
docker compose exec app php artisan migrate
docker compose exec app php artisan make:controller Api/MessageController
```

### Composer Commands
```bash
# Install packages
make composer cmd="require guzzlehttp/guzzle"

# Update dependencies
make update
```

## Project Structure Overview

```
sms-hub/
├── docker/              # Docker configuration
├── app/                 # Laravel application
│   ├── Models/          # Database models
│   ├── Http/            # Controllers, Middleware
│   ├── Jobs/            # Queue jobs
│   └── Providers/       # SMS provider drivers
├── database/            # Migrations, seeders
├── routes/              # API routes
└── tests/               # Test files
```

## Key Features Ready to Use

### 1. Authentication (OAuth2)
- Laravel Passport configured
- Token-based authentication
- Refresh token support

### 2. Monitoring (Telescope)
- Request/response tracking
- Database query monitoring
- Job execution tracking
- Error tracking

### 3. Queue System
- Redis-based queues
- Job processing
- Failed job handling

### 4. Database
- MySQL 8.0 configured
- Migration system ready
- Seeding support

## Next Development Steps

1. **Create Models**: Start with User, Project, Provider, Message models
2. **Build Migrations**: Set up database schema
3. **Implement Controllers**: Create API endpoints
4. **Add Provider Drivers**: Implement SMS provider integrations
5. **Create Jobs**: Set up SMS sending and processing jobs
6. **Add Tests**: Write comprehensive test suite

## Troubleshooting

### Common Issues

**Docker not running**
```bash
# Start Docker Desktop first, then:
docker compose up -d
```

**Port conflicts**
```bash
# Check what's using port 8000
lsof -i :8000
# Stop conflicting service or change port in docker-compose.yml
```

**Permission issues**
```bash
# Fix storage permissions
docker compose exec app chmod -R 775 storage bootstrap/cache
```

**Database connection issues**
```bash
# Check if MySQL is running
docker compose ps
# Restart if needed
docker compose restart db
```

### Getting Help

- Check logs: `make logs`
- Access shell: `make shell`
- View container status: `make status`
- Clear caches: `make clear`

## Environment Configuration

### Development
- Uses `env.development.example` as template
- Debug mode enabled
- Telescope enabled
- Local database and Redis

### Production
- Uses `env.production.example` as template
- Debug mode disabled
- Optimized for performance
- SSL configuration ready

## API Testing

Once the application is running, you can test the API:

```bash
# Test health check
curl http://localhost:8000/api/health

# Test OAuth2 token (after creating a client)
curl -X POST http://localhost:8000/oauth/token \
  -H "Content-Type: application/json" \
  -d '{
    "grant_type": "client_credentials",
    "client_id": "your-client-id",
    "client_secret": "your-client-secret"
  }'
```

## Support

- **Documentation**: Check `README.md` and `PROJECT_STRUCTURE.md`
- **Issues**: Create an issue in the repository
- **Development**: Use `make help` for available commands

Happy coding! 🚀

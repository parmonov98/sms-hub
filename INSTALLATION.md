# Installation Guide for SMS Hub

## Prerequisites

### 1. Install Docker Desktop

#### For macOS:
1. Download Docker Desktop from [https://www.docker.com/products/docker-desktop](https://www.docker.com/products/docker-desktop)
2. Install the downloaded `.dmg` file
3. Start Docker Desktop from Applications
4. Wait for Docker to start (you'll see the Docker icon in the menu bar)

#### For Windows:
1. Download Docker Desktop from [https://www.docker.com/products/docker-desktop](https://www.docker.com/products/docker-desktop)
2. Install the downloaded `.exe` file
3. Start Docker Desktop
4. Enable WSL 2 if prompted

#### For Linux (Ubuntu/Debian):
```bash
# Update package index
sudo apt-get update

# Install prerequisites
sudo apt-get install apt-transport-https ca-certificates curl gnupg lsb-release

# Add Docker's official GPG key
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg

# Set up stable repository
echo "deb [arch=amd64 signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Install Docker Engine
sudo apt-get update
sudo apt-get install docker-ce docker-ce-cli containerd.io

# Add user to docker group
sudo usermod -aG docker $USER

# Start Docker service
sudo systemctl start docker
sudo systemctl enable docker
```

### 2. Verify Docker Installation

```bash
# Check Docker version
docker --version

# Check Docker Compose version
docker compose version

# Test Docker installation
docker run hello-world
```

## Project Setup

Once Docker is installed and running:

### 1. Start the containers
```bash
docker compose up -d
```

### 2. Install Laravel and dependencies
```bash
docker compose exec app ./setup.sh
```

### 3. Configure environment
```bash
docker compose exec app cp .env.example .env
docker compose exec app php artisan key:generate
```

### 4. Run migrations and setup
```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan passport:install
docker compose exec app php artisan telescope:install
```

### 5. Create admin user
```bash
docker compose exec app php artisan make:admin
```

## Alternative Setup (Without Docker)

If you prefer to run Laravel directly on your system:

### 1. Install PHP 8.4
```bash
# macOS with Homebrew
brew install php@8.4

# Ubuntu/Debian
sudo apt-get install php8.4 php8.4-fpm php8.4-mysql php8.4-redis php8.4-xml php8.4-mbstring php8.4-curl php8.4-zip php8.4-gd php8.4-bcmath
```

### 2. Install Composer
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 3. Install MySQL and Redis
```bash
# macOS
brew install mysql redis

# Ubuntu/Debian
sudo apt-get install mysql-server redis-server
```

### 4. Create Laravel project
```bash
composer create-project laravel/laravel:^12.0 . --prefer-dist
composer require laravel/passport laravel/telescope guzzlehttp/guzzle predis/predis
```

### 5. Configure environment
```bash
cp .env.example .env
php artisan key:generate
```

### 6. Run setup
```bash
php artisan migrate
php artisan passport:install
php artisan telescope:install
```

## Troubleshooting

### Docker Issues
- Make sure Docker Desktop is running
- Check if ports 8000, 3306, 6379 are available
- Restart Docker Desktop if containers fail to start

### Laravel Issues
- Check file permissions: `chmod -R 775 storage bootstrap/cache`
- Clear cache: `php artisan config:clear && php artisan cache:clear`
- Check logs: `tail -f storage/logs/laravel.log`

### Database Issues
- Ensure MySQL is running and accessible
- Check database credentials in `.env`
- Verify database exists: `mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS sms_hub;"`

## Next Steps

After successful installation:

1. Access the application at http://localhost:8000
2. Access Telescope at http://localhost:8000/telescope
3. Review the API documentation
4. Start implementing the SMS Hub features according to the PRD

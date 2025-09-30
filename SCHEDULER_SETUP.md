# SMS Hub Scheduler Setup

This document explains how to set up the SMS delivery status checking scheduler.

## Overview

The SMS Hub includes an automated system to check the delivery status of sent SMS messages. This helps track which messages were successfully delivered to recipients.

## Components

### 1. Artisan Command
- **Command**: `php artisan sms:check-delivery`
- **Purpose**: Checks delivery status of sent SMS messages
- **Options**: `--limit=N` (default: 50, max: 100)

### 2. Scheduled Tasks
- **Frequency**: Every 5 minutes
- **Command**: `sms:check-delivery --limit=100`
- **Logs**: `storage/logs/scheduler.log`

### 3. Scheduler Script
- **File**: `start-scheduler.sh`
- **Purpose**: Runs the Laravel scheduler every minute
- **Usage**: `./start-scheduler.sh`

## Setup Options

### Option 1: Manual Scheduler (Recommended for Development)
```bash
# Run the scheduler script
./start-scheduler.sh
```

### Option 2: System Cron Job (Recommended for Production)
Add this to your system crontab:
```bash
# Run Laravel scheduler every minute
* * * * * cd /path/to/sms-hub && docker exec sms-hub-app php artisan schedule:run >> storage/logs/scheduler.log 2>&1
```

### Option 3: Docker Cron (Alternative)
You can also set up a separate Docker container with cron, but the above options are simpler.

## How It Works

1. **Every 5 minutes**, the scheduler runs `sms:check-delivery`
2. **Finds sent messages** that haven't been checked recently
3. **Queries SMS providers** (like Eskiz) for delivery status
4. **Updates message status** from "sent" to "delivered" or "failed"
5. **Logs all activity** to `storage/logs/scheduler.log`

## Monitoring

### Check Scheduler Logs
```bash
docker exec sms-hub-app tail -f /var/www/storage/logs/scheduler.log
```

### Manual Status Check
```bash
docker exec sms-hub-app php artisan sms:check-delivery --limit=10
```

### View SMS Status in Admin Panel
- Go to `http://localhost:8000/admin/messages`
- Check the "Status" column for delivery updates

## Troubleshooting

### Scheduler Not Running
1. Check if the scheduler script is running: `ps aux | grep scheduler`
2. Check logs: `tail -f storage/logs/scheduler.log`
3. Test manually: `docker exec sms-hub-app php artisan schedule:run`

### No Status Updates
1. Check if SMS provider supports status checking
2. Verify provider tokens are valid
3. Check provider API documentation

### High CPU Usage
1. Reduce the `--limit` parameter
2. Increase the schedule frequency (e.g., every 10 minutes instead of 5)
3. Check for stuck processes

## Configuration

### Change Check Frequency
Edit `routes/console.php`:
```php
// Change from every 5 minutes to every 10 minutes
Schedule::command('sms:check-delivery --limit=100')
    ->everyTenMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/scheduler.log'));
```

### Change Message Limit
Edit the `--limit` parameter in `routes/console.php`:
```php
Schedule::command('sms:check-delivery --limit=50') // Reduced from 100
```

## Status Meanings

- **queued**: Message is waiting to be sent
- **sent**: Message was sent to provider successfully
- **delivered**: Message was delivered to recipient
- **failed**: Message delivery failed
- **unknown**: Status could not be determined

## Notes

- The scheduler only checks messages from the last 7 days
- Messages are checked in batches to avoid overwhelming the provider API
- Failed status checks are logged but don't stop the scheduler
- The scheduler runs in the background to avoid blocking other operations

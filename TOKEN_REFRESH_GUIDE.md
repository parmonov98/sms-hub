# Provider Token Refresh System

This document describes the automated token refresh system for SMS provider authentication tokens.

## Overview

The system automatically refreshes authentication tokens for SMS providers every 10 days to ensure continuous service availability. Tokens are refreshed when they are:
- Expired
- Expiring within 2 days

## Components

### 1. RefreshProviderTokensJob
- **Location**: `app/Jobs/RefreshProviderTokensJob.php`
- **Purpose**: Handles the token refresh process for all providers
- **Features**:
  - Retry logic (3 attempts with exponential backoff)
  - Comprehensive logging
  - Error handling and reporting
  - Automatic cleanup of expired tokens

### 2. RefreshProviderTokensCommand
- **Location**: `app/Console/Commands/RefreshProviderTokensCommand.php`
- **Command**: `sms:refresh-provider-tokens`
- **Options**:
  - `--sync`: Run synchronously instead of dispatching to queue
  - `--force`: Force refresh even if tokens are not expiring soon

### 3. ProviderTokenService
- **Location**: `app/Services/ProviderTokenService.php`
- **Purpose**: Manages provider token lifecycle
- **Key Methods**:
  - `getEskizToken()`: Get or refresh Eskiz token
  - `getPlayMobileToken()`: Get or refresh PlayMobile token
  - `getTokensNeedingRefresh()`: Get list of tokens that need refresh
  - `refreshTokensNeedingRefresh()`: Refresh only tokens that need it
  - `cleanupExpiredTokens()`: Remove expired tokens

## Scheduling

The token refresh is scheduled to run every 10 days at 2:00 AM using Laravel's scheduler:

```php
Schedule::command('sms:refresh-provider-tokens')
    ->cron('0 2 */10 * *') // Run at 2 AM every 10 days
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/scheduler.log'));
```

## Manual Execution

### Run Synchronously
```bash
php artisan sms:refresh-provider-tokens --sync
```

### Dispatch to Queue
```bash
php artisan sms:refresh-provider-tokens
```

### Check Scheduled Tasks
```bash
php artisan schedule:list
```

## Provider Support

### Currently Supported
- **Eskiz**: Uses email/password authentication to obtain JWT tokens
- **PlayMobile**: Placeholder implementation (needs API integration)

### Adding New Providers

1. Add token refresh logic to `ProviderTokenService`
2. Update `RefreshProviderTokensJob` to handle the new provider
3. Add provider-specific methods following the existing pattern

## Configuration

### Environment Variables
```env
ESKIZ_EMAIL=your-email@example.com
ESKIZ_PASSWORD=your-password
```

### Token Storage
Tokens are stored in the `provider_tokens` table with the following structure:
- `provider_id`: Reference to the provider
- `token_type`: Type of token (access, refresh, etc.)
- `token_value`: The actual token
- `expires_at`: Token expiration timestamp
- `metadata`: Additional token information
- `is_active`: Whether the token is currently active

## Monitoring

### Logs
- Job execution logs: `storage/logs/laravel.log`
- Scheduler logs: `storage/logs/scheduler.log`

### Database
Check token status:
```sql
SELECT p.display_name, pt.token_type, pt.expires_at, pt.is_active
FROM provider_tokens pt
JOIN providers p ON pt.provider_id = p.id
WHERE pt.is_active = 1;
```

## Troubleshooting

### Common Issues

1. **Token Refresh Fails**
   - Check provider credentials in `.env`
   - Verify provider API availability
   - Check logs for specific error messages

2. **Scheduler Not Running**
   - Ensure cron is set up: `* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1`
   - Check scheduler logs

3. **Queue Not Processing**
   - Ensure queue worker is running: `php artisan queue:work`
   - Check queue configuration

### Manual Token Refresh
If automatic refresh fails, you can manually refresh tokens:

```bash
# Refresh all tokens
php artisan sms:refresh-provider-tokens --sync

# Or refresh specific provider programmatically
php artisan tinker
>>> $service = new \App\Services\ProviderTokenService();
>>> $service->refreshEskizToken(\App\Models\Provider::where('display_name', 'eskiz')->first());
```

## Security Considerations

- Tokens are stored securely in the database
- Old tokens are deactivated when new ones are created
- Expired tokens are automatically cleaned up
- All token operations are logged for audit purposes

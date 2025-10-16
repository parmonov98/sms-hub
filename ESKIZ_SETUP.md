# Eskiz SMS Provider Setup

## Overview
Eskiz is Uzbekistan's leading SMS service provider. This migration has seeded the Eskiz provider into the SMS Hub system with full configuration.

## Provider Configuration
- **Display Name**: eskiz
- **Description**: Eskiz SMS Gateway - Uzbekistan's leading SMS service provider
- **Status**: Enabled
- **Priority**: 1 (highest priority for failover)

## Capabilities
The Eskiz provider supports the following features:
- ✅ **DLR (Delivery Reports)**: Real-time delivery status updates
- ✅ **Unicode Support**: Full Unicode character support for international messages
- ✅ **Message Concatenation**: Automatic splitting of long messages
- ✅ **Scheduled SMS**: Send messages at specific times
- ✅ **Bulk SMS**: Send to multiple recipients efficiently
- ✅ **Template Support**: Pre-approved message templates
- ❌ **Flash SMS**: Not supported by Eskiz

## Environment Configuration

### Required Environment Variables
Add these to your `.env` file:

```env
# Eskiz SMS Provider Configuration
ESKIZ_EMAIL=your_eskiz_email@example.com
ESKIZ_PASSWORD=your_eskiz_password
```

### Getting Eskiz Credentials
1. Register at [Eskiz.uz](https://eskiz.uz)
2. Verify your account and activate SMS functionality
3. Get your email and password from your Eskiz dashboard
4. Add them to your environment configuration

## API Endpoints
- **Base URL**: `https://notify.eskiz.uz/api`
- **Authentication**: `https://notify.eskiz.uz/api/auth/login`
- **Send SMS**: `https://notify.eskiz.uz/api/message/sms/send`
- **Templates**: `https://notify.eskiz.uz/api/user/templates`

## Token Management
- Tokens are automatically managed by the system
- Tokens expire and are refreshed automatically
- No manual token management required

## Usage
Once configured with valid credentials, the Eskiz provider will be automatically used for SMS sending when:
- `SMS_DEFAULT_PROVIDER=eskiz` is set in your environment
- The provider is enabled in the database
- Valid Eskiz credentials are provided

## Migration Details
- **Migration File**: `2025_10_16_212250_seed_eskiz_provider.php`
- **Provider ID**: 1
- **Created**: Automatically via migration
- **Rollback**: Available via `php artisan migrate:rollback`

## Troubleshooting
1. **Authentication Errors**: Check your Eskiz email and password
2. **Permission Errors**: Ensure your Eskiz account has SMS sending permissions
3. **Token Issues**: Tokens are refreshed automatically, check logs for details
4. **API Errors**: Check Eskiz API status and your account balance

## Support
- Eskiz Documentation: [Eskiz API Docs](https://eskiz.uz/api)
- SMS Hub Logs: Check `storage/logs/laravel.log` for detailed error messages


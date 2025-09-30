# SMS Hub API - Quick Reference

## 🔑 Authentication

### Get Access Token
```bash
curl -X POST http://localhost:8000/oauth/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=client_credentials&client_id=YOUR_CLIENT_ID&client_secret=YOUR_CLIENT_SECRET"
```

### Response
```json
{
  "token_type": "Bearer",
  "expires_in": 31536000,
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."
}
```

## 📱 Send SMS

### Basic SMS
```bash
curl -X POST http://localhost:8000/api/v1/messages \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "to": "+998942638523",
    "message": "Your message here"
  }'
```

### With Custom Sender
```bash
curl -X POST http://localhost:8000/api/v1/messages \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "to": "+998942638523",
    "message": "Your message here",
    "sender_id": "4546"
  }'
```

### Using Template
```bash
curl -X POST http://localhost:8000/api/v1/messages \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "to": "+998942638523",
    "message": "Your message here",
    "template_id": 63
  }'
```

## 📊 Response Format

### Success Response
```json
{
  "data": {
    "id": 20,
    "to": "+998942638523",
    "from": "4546",
    "text": "Your message here",
    "status": "queued",
    "price_decimal": null,
    "currency": "UZS",
    "created_at": "2025-09-30T08:56:46.000000Z"
  },
  "message": "Message queued for sending"
}
```

## 🚨 Common Errors

### Invalid Credentials
```json
{
  "error": "invalid_client",
  "error_description": "Client authentication failed"
}
```

### Missing Authorization
```json
{
  "message": "Unauthenticated."
}
```

### Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "to": ["The to field is required."]
  }
}
```

## 💰 Pricing

- **Currency**: UZS (Uzbekistani Som)
- **Price per SMS**: 160 UZS
- **Provider**: Eskiz.uz

## 🔧 Complete Example

```bash
#!/bin/bash

# 1. Set your credentials
CLIENT_ID="cc5754f0-b094-47c2-a4b9-4df81a9d6344"
CLIENT_SECRET="KZc0yaWbybbWAy3FdOrPM6PeU93dQzyIlNmORd9V"

# 2. Get access token
ACCESS_TOKEN=$(curl -s -X POST http://localhost:8000/oauth/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=client_credentials&client_id=$CLIENT_ID&client_secret=$CLIENT_SECRET" \
  | jq -r '.access_token')

# 3. Send SMS
curl -X POST http://localhost:8000/api/v1/messages \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "to": "+998942638523",
    "message": "Hello from SMS Hub!"
  }'
```

## 📋 Required Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `to` | string | ✅ | Phone number (+country code) |
| `message` | string | ✅ | SMS text content |

## 🔧 Optional Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `sender_id` | string | ❌ | Custom sender ID (default: "4546") |
| `template_id` | integer | ❌ | Pre-approved template ID |

## 📱 Phone Number Format

- **Format**: `+998XXXXXXXXX`
- **Example**: `+998942638523`
- **Country**: Uzbekistan (+998)

## 🔍 Status Values

- `queued`: Message is waiting to be sent
- `sent`: Message has been sent to provider
- `delivered`: Message was delivered to recipient
- `failed`: Message failed to send

## 🛠️ Tools

- **Admin Panel**: Monitor messages at `/admin`
- **API Documentation**: Swagger UI available
- **Logs**: Check Laravel logs for debugging

---

**Need Help?** Check the full documentation in `SMS_API_USAGE_GUIDE.md`

# SMS Hub API Usage Guide

This guide explains how to use the SMS Hub API to send SMS messages using OAuth2 client credentials authentication.

## Table of Contents
1. [Authentication](#authentication)
2. [Getting Client Credentials](#getting-client-credentials)
3. [Sending SMS Messages](#sending-sms-messages)
4. [API Endpoints](#api-endpoints)
5. [Response Examples](#response-examples)
6. [Error Handling](#error-handling)
7. [Rate Limits](#rate-limits)

## Authentication

The SMS Hub API uses OAuth2 Client Credentials flow for authentication. You need to obtain a client ID and client secret to access the API.

### Authentication Flow
1. **Get Client Credentials**: Obtain client ID and secret from the admin panel
2. **Request Access Token**: Exchange credentials for an access token
3. **Use Access Token**: Include the token in API requests

## Getting Client Credentials

### Step 1: Access Admin Panel
1. Navigate to the SMS Hub admin panel
2. Go to **OAuth Clients** section
3. Click **Create OAuth Client**

### Step 2: Create OAuth Client
1. **Name**: Enter a descriptive name for your application
2. **Redirect URIs**: Leave empty (not required for client credentials)
3. **Grant Types**: Select "Client Credentials"
4. Click **Create**

### Step 3: Save Credentials
After creation, you'll receive:
- **Client ID**: `cc5754f0-b094-47c2-a4b9-4df81a9d6344`
- **Client Secret**: `KZc0yaWbybbWAy3FdOrPM6PeU93dQzyIlNmORd9V`

⚠️ **Important**: Save these credentials securely. The client secret is only shown once in plain text!

## Sending SMS Messages

### Step 1: Get Access Token

**Endpoint**: `POST /oauth/token`

**Request**:
```bash
curl -X POST http://localhost:8000/oauth/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=client_credentials&client_id=YOUR_CLIENT_ID&client_secret=YOUR_CLIENT_SECRET"
```

**Response**:
```json
{
  "token_type": "Bearer",
  "expires_in": 31536000,
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."
}
```

### Step 2: Send SMS Message

**Endpoint**: `POST /api/v1/messages`

**Request**:
```bash
curl -X POST http://localhost:8000/api/v1/messages \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "to": "+998942638523",
    "message": "Your SMS message text here"
  }'
```

**Optional Parameters**:
```json
{
  "to": "+998942638523",
  "message": "Your SMS message text here",
  "sender_id": "4546",
  "template_id": 63
}
```

## API Endpoints

### Authentication Endpoints

#### Get Access Token
- **URL**: `/oauth/token`
- **Method**: `POST`
- **Content-Type**: `application/x-www-form-urlencoded`

**Parameters**:
- `grant_type`: `client_credentials`
- `client_id`: Your client ID
- `client_secret`: Your client secret

### SMS Endpoints

#### Send SMS Message
- **URL**: `/api/v1/messages`
- **Method**: `POST`
- **Authentication**: Required (Bearer token)

**Request Body**:
```json
{
  "to": "string (required)",
  "message": "string (required)",
  "sender_id": "string (optional)",
  "template_id": "integer (optional)"
}
```

**Parameters**:
- `to`: Recipient phone number (e.g., "+998942638523")
- `message`: SMS text content
- `sender_id`: Custom sender ID (default: "4546")
- `template_id`: Pre-approved template ID

## Response Examples

### Successful Token Request
```json
{
  "token_type": "Bearer",
  "expires_in": 31536000,
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."
}
```

### Successful SMS Send
```json
{
  "data": {
    "id": 20,
    "project_id": null,
    "provider_id": null,
    "provider_message_id": null,
    "to": "+998942638523",
    "from": "4546",
    "text": "Your SMS message text here",
    "parts": 1,
    "status": "queued",
    "error_code": null,
    "error_message": null,
    "price_decimal": null,
    "currency": "UZS",
    "idempotency_key": "msg_68db9b4e07b562.54634864",
    "created_at": "2025-09-30T08:56:46.000000Z",
    "updated_at": "2025-09-30T08:56:46.000000Z"
  },
  "message": "Message queued for sending"
}
```

### Error Responses

#### Invalid Credentials
```json
{
  "error": "invalid_client",
  "error_description": "Client authentication failed"
}
```

#### Missing Authorization
```json
{
  "message": "Unauthenticated."
}
```

#### Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "to": ["The to field is required."],
    "message": ["The message field is required."]
  }
}
```

## Error Handling

### Common HTTP Status Codes
- `200`: Success
- `400`: Bad Request (validation errors)
- `401`: Unauthorized (invalid/missing token)
- `422`: Unprocessable Entity (validation errors)
- `500`: Internal Server Error

### Error Response Format
```json
{
  "message": "Error description",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

## Rate Limits

- **Token Requests**: No specific limit
- **SMS Sending**: Depends on provider limits
- **Concurrent Requests**: Limited by server capacity

## Complete Example

### 1. Get Access Token
```bash
# Replace with your actual credentials
CLIENT_ID="cc5754f0-b094-47c2-a4b9-4df81a9d6344"
CLIENT_SECRET="$2y$12$8xilJ4MjBH2viHFra9mn8eN7QZiZ0if05Q8t80zZ4JmLGOz3bgOnC"

ACCESS_TOKEN=$(curl -s -X POST http://localhost:8000/oauth/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=client_credentials&client_id=$CLIENT_ID&client_secret=$CLIENT_SECRET" \
  | jq -r '.access_token')

echo "Access Token: $ACCESS_TOKEN"
```

### 2. Send SMS Message
```bash
curl -X POST http://localhost:8000/api/v1/messages \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "to": "+998942638523",
    "message": "Hello from SMS Hub API!"
  }'
```

### 3. Check Message Status
You can check the message status in the admin panel under **SMS Messages** section.

## Best Practices

1. **Store Credentials Securely**: Never hardcode credentials in your application
2. **Handle Token Expiration**: Implement token refresh logic
3. **Validate Phone Numbers**: Ensure proper format (+country code)
4. **Use Templates**: For better deliverability, use pre-approved templates
5. **Monitor Status**: Check message delivery status regularly
6. **Error Handling**: Implement proper error handling for all API calls

## Support

For technical support or questions:
- Check the admin panel for message status
- Review Laravel logs for detailed error information
- Contact system administrator for API access issues

---

**Note**: This API is designed for SMS delivery through the Eskiz provider in Uzbekistan. All prices are in UZS (Uzbekistani Som).

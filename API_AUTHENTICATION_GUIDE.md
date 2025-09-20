# SMS Hub API Authentication Guide

This guide explains how to authenticate with the SMS Hub API using Laravel Passport OAuth2.

## 🔐 Authentication Overview

The SMS Hub API uses **OAuth2** with Laravel Passport for authentication. You need to obtain an access token before making API requests.

## 📋 Prerequisites

1. **OAuth2 Client**: You need a client ID and client secret
2. **API Access**: Ensure you have access to the SMS Hub API

## 🚀 Getting Started

### Step 1: Create an OAuth2 Client

First, you need to create an OAuth2 client. This can be done by an administrator or through the API.

```bash
# Create a client credentials client (for server-to-server)
docker compose exec app php artisan passport:client --client --name="My API Client"

# Create a password grant client (for user authentication)
docker compose exec app php artisan passport:client --password --name="My Web App"
```

### Step 2: Get an Access Token

#### Option A: Client Credentials Grant (Server-to-Server)

Use this for server-to-server communication where no user interaction is required.

```bash
curl -X POST http://localhost:8000/oauth/token \
  -H "Content-Type: application/json" \
  -d '{
    "grant_type": "client_credentials",
    "client_id": "your-client-id",
    "client_secret": "your-client-secret"
  }'
```

**Response:**
```json
{
  "token_type": "Bearer",
  "expires_in": 31536000,
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
  "refresh_token": "def50200..."
}
```

#### Option B: Password Grant (User Authentication)

Use this for applications where users log in with their credentials.

```bash
curl -X POST http://localhost:8000/oauth/token \
  -H "Content-Type: application/json" \
  -d '{
    "grant_type": "password",
    "client_id": "your-client-id",
    "client_secret": "your-client-secret",
    "username": "user@example.com",
    "password": "user-password"
  }'
```

### Step 3: Use the Access Token

Include the access token in the `Authorization` header of your API requests:

```bash
curl -X GET http://localhost:8000/api/v1/providers \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."
```

## 🔄 Token Refresh

When your access token expires, use the refresh token to get a new one:

```bash
curl -X POST http://localhost:8000/v1/auth/refresh \
  -H "Content-Type: application/json" \
  -d '{
    "grant_type": "refresh_token",
    "refresh_token": "your-refresh-token",
    "client_id": "your-client-id",
    "client_secret": "your-client-secret"
  }'
```

## 📚 Available Endpoints

### Authentication Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/oauth/token` | Get access token |
| POST | `/v1/auth/refresh` | Refresh access token |
| GET | `/v1/auth/me` | Get current user info |

### API Endpoints (Require Authentication)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/providers` | List SMS providers |
| POST | `/api/v1/messages` | Send SMS message |
| GET | `/api/v1/messages/{id}` | Get message details |
| GET | `/api/v1/usage` | Get usage statistics |

## 🛠️ Testing with Swagger UI

1. **Access Swagger UI**: http://localhost:8000/api/documentation
2. **Get Token**: Use the `/oauth/token` endpoint to get an access token
3. **Authorize**: Click the "Authorize" button and enter your Bearer token
4. **Test APIs**: Use the "Try it out" buttons to test endpoints

## 🔧 Example Implementation

### JavaScript/Node.js

```javascript
const axios = require('axios');

// Get access token
async function getAccessToken() {
  const response = await axios.post('http://localhost:8000/oauth/token', {
    grant_type: 'client_credentials',
    client_id: 'your-client-id',
    client_secret: 'your-client-secret'
  });
  
  return response.data.access_token;
}

// Make API request
async function sendSMS() {
  const token = await getAccessToken();
  
  const response = await axios.post('http://localhost:8000/api/v1/messages', {
    to: '+998901234567',
    message: 'Hello from SMS Hub!'
  }, {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    }
  });
  
  return response.data;
}
```

### PHP

```php
<?php

// Get access token
function getAccessToken() {
    $response = file_get_contents('http://localhost:8000/oauth/token', false, stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => json_encode([
                'grant_type' => 'client_credentials',
                'client_id' => 'your-client-id',
                'client_secret' => 'your-client-secret'
            ])
        ]
    ]));
    
    $data = json_decode($response, true);
    return $data['access_token'];
}

// Make API request
function sendSMS() {
    $token = getAccessToken();
    
    $response = file_get_contents('http://localhost:8000/api/v1/messages', false, stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json'
            ],
            'content' => json_encode([
                'to' => '+998901234567',
                'message' => 'Hello from SMS Hub!'
            ])
        ]
    ]));
    
    return json_decode($response, true);
}
```

### Python

```python
import requests

# Get access token
def get_access_token():
    response = requests.post('http://localhost:8000/oauth/token', json={
        'grant_type': 'client_credentials',
        'client_id': 'your-client-id',
        'client_secret': 'your-client-secret'
    })
    
    return response.json()['access_token']

# Make API request
def send_sms():
    token = get_access_token()
    
    response = requests.post('http://localhost:8000/api/v1/messages', 
        json={
            'to': '+998901234567',
            'message': 'Hello from SMS Hub!'
        },
        headers={
            'Authorization': f'Bearer {token}',
            'Content-Type': 'application/json'
        }
    )
    
    return response.json()
```

## ⚠️ Important Notes

1. **Token Expiration**: Access tokens expire after 1 year (31536000 seconds)
2. **Refresh Tokens**: Use refresh tokens to get new access tokens without re-authenticating
3. **Security**: Keep your client secret secure and never expose it in client-side code
4. **Rate Limiting**: Be aware of API rate limits
5. **HTTPS**: Always use HTTPS in production environments

## 🆘 Troubleshooting

### Common Issues

1. **401 Unauthorized**: Check your client ID and secret
2. **400 Bad Request**: Verify the grant type and required parameters
3. **Token Expired**: Use the refresh token to get a new access token

### Error Responses

```json
{
  "error": "invalid_client",
  "error_description": "Client authentication failed"
}
```

## 📞 Support

For additional help:
- Check the Swagger documentation: http://localhost:8000/api/documentation
- Review the API logs in Laravel Telescope: http://localhost:8000/telescope
- Contact support: support@smshub.com

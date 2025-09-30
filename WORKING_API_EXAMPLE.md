# SMS Hub API - Working Example

## 🚨 Important Note
The OAuth client secrets are automatically hashed when stored in the database. You need to use the **plain text secret** that was shown during client creation, not the hashed version.

## ✅ Working Example

### Step 1: Get Access Token
```bash
curl -X POST http://localhost:8000/oauth/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=client_credentials&client_id=cc5754f0-b094-47c2-a4b9-4df81a9d6344&client_secret=KZc0yaWbybbWAy3FdOrPM6PeU93dQzyIlNmORd9V"
```

**Response:**
```json
{
  "token_type": "Bearer",
  "expires_in": 31536000,
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."
}
```

### Step 2: Send SMS
```bash
curl -X POST http://localhost:8000/api/v1/messages \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiJjYzU3NTRmMC1iMDk0LTQ3YzItYTRiOS00ZGY4MWE5ZDYzNDQiLCJqdGkiOiJhNTc2N2I2NTc2NzhhZDVjZGM0NGE1ZWY4MWI4OWUxMThiOGFjMzYwNmNkOWM0NjFlMjkzMzc5ZTc4ODNiNWY0NjJhNDZiYzJiY2FkNWY3YSIsImlhdCI6MTc1OTIxNjkxNy40NjE4NDYsIm5iZiI6MTc1OTIxNjkxNy40NjE4NDcsImV4cCI6MTc5MDc1MjkxNy40NTU1MTMsInN1YiI6ImNjNTc1NGYwLWIwOTQtNDdjMi1hNGI5LTRkZjgxYTlkNjM0NCIsInNjb3BlcyI6W119.Re8CIoc2ynQfInRYNiGMQlPuZbiWO8-g9N9BtBWkWQ_8BhfTRHTOOjgO9a_U3OoXEwtxoYLXpItgFtuwt5Jk8brMxbPRaUBYkWOOsdTHqKXVPUxBpvCs5M7jW7ZgNV50-amTJppkOvi55MifjUDZUT8WCRts3ImQUBF9mJLHOkMZMmv0zpfkS78CXQrHkg0wPo6UL2wKcdR2OdpJSdYcg-yy_HvoyztB9OaVWyOAy-jwkk8gPBG9FFMjvwe6t7tYwhppRSrkguqSb8MBQSh7DXPJO6K_fZ8IgimS6pJeD7lz6racFs0283K2mt-cbL96FfxK17OGbFpv43WKX8TsJwkSj4tSc6ps_4SwJcQsSpGIoooPO12gm10Zo1-3K8bm4TK_3NezkCag-Y61rKfF5AqjzQQOrR7MePxjtpim0Rmg06vXdZLBRVYFy0Z0hm6mdpdodLci7eEh5J0RXXCJ764b_TNyNhE0LDrSBWrYjhW1EjxSGgLjTp88XzSwsPuDZLq7ChWhLjfrypPtiEqbrNPOrqh6Zu_WK45tzI_KiRQlmcwMi5wyN0tT_qQv5azHbKGFItiPig9tyQmTeYtYn0OVOzo4Io_qpsLHA6RXxHJC9TWkFFF5--gyIRpvC5VfJ9tG2Vk9wfh5JoaYONfvhUJHeYZmwkKVL0YXWQc45z0" \
  -H "Content-Type: application/json" \
  -d '{
    "to": "+998942638523",
    "message": "Test message from API"
  }'
```

**Response:**
```json
{
  "data": {
    "id": 20,
    "project_id": null,
    "provider_id": null,
    "provider_message_id": null,
    "to": "+998942638523",
    "from": "4546",
    "text": "Test message from API",
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

## 🔧 Complete Working Script

```bash
#!/bin/bash

# Working credentials (use the plain text secret, not the hashed one)
CLIENT_ID="cc5754f0-b094-47c2-a4b9-4df81a9d6344"
CLIENT_SECRET="KZc0yaWbybbWAy3FdOrPM6PeU93dQzyIlNmORd9V"

# Get access token
ACCESS_TOKEN=$(curl -s -X POST http://localhost:8000/oauth/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=client_credentials&client_id=$CLIENT_ID&client_secret=$CLIENT_SECRET" \
  | jq -r '.access_token')

echo "Access Token: $ACCESS_TOKEN"

# Send SMS
curl -X POST http://localhost:8000/api/v1/messages \
  -H "Authorization: Bearer $ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "to": "+998942638523",
    "message": "Hello from SMS Hub API!"
  }'
```

## 📝 Key Points

1. **Use Plain Text Secret**: The client secret must be the original plain text version, not the hashed version from the database
2. **Token Expiration**: Access tokens expire after 1 year (31536000 seconds)
3. **Message Status**: Messages start as "queued" and progress to "sent" → "delivered"
4. **Price Tracking**: Price information (160 UZS) is captured during delivery status checks
5. **Admin Panel**: Monitor messages at `/admin` under "SMS Messages"

## 🚨 Troubleshooting

### "invalid_client" Error
- Make sure you're using the **plain text** client secret, not the hashed version
- Verify the client ID is correct
- Check that the OAuth client exists and is not revoked

### "Unauthenticated" Error
- Make sure you're including the `Authorization: Bearer TOKEN` header
- Verify the access token is valid and not expired
- Check that the token was obtained correctly

### Message Stays "Queued"
- Check Laravel logs for processing errors
- Verify the SMS provider (Eskiz) is configured correctly
- Ensure the message text is approved (not requiring moderation)

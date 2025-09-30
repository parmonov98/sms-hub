#!/bin/bash

# SMS Hub API Test Script
# This script demonstrates how to use the SMS Hub API

# Configuration
BASE_URL="http://localhost:8000"
CLIENT_ID="cc5754f0-b094-47c2-a4b9-4df81a9d6344"
CLIENT_SECRET="KZc0yaWbybbWAy3FdOrPM6PeU93dQzyIlNmORd9V"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}🚀 SMS Hub API Test Script${NC}"
echo "=================================="

# Function to print colored output
print_status() {
    if [ $1 -eq 0 ]; then
        echo -e "${GREEN}✅ $2${NC}"
    else
        echo -e "${RED}❌ $2${NC}"
    fi
}

# Function to make API calls
api_call() {
    local method=$1
    local url=$2
    local data=$3
    local headers=$4
    
    if [ -n "$data" ]; then
        curl -s -X $method "$url" -H "$headers" -d "$data"
    else
        curl -s -X $method "$url" -H "$headers"
    fi
}

echo -e "${YELLOW}Step 1: Getting Access Token...${NC}"
echo "----------------------------------------"

# Get access token
TOKEN_RESPONSE=$(api_call "POST" "$BASE_URL/oauth/token" \
    "grant_type=client_credentials&client_id=$CLIENT_ID&client_secret=$CLIENT_SECRET" \
    "Content-Type: application/x-www-form-urlencoded")

echo "Token Response:"
echo "$TOKEN_RESPONSE" | jq '.' 2>/dev/null || echo "$TOKEN_RESPONSE"

# Extract access token
ACCESS_TOKEN=$(echo "$TOKEN_RESPONSE" | jq -r '.access_token' 2>/dev/null)

if [ "$ACCESS_TOKEN" != "null" ] && [ -n "$ACCESS_TOKEN" ]; then
    print_status 0 "Access token obtained successfully"
    echo "Access Token: ${ACCESS_TOKEN:0:50}..."
else
    print_status 1 "Failed to get access token"
    echo "Response: $TOKEN_RESPONSE"
    exit 1
fi

echo ""
echo -e "${YELLOW}Step 2: Sending SMS Message...${NC}"
echo "----------------------------------------"

# Send SMS message
SMS_DATA='{
    "to": "+998942638523",
    "message": "Test message from SMS Hub API - '$(date)'"
}'

echo "SMS Data:"
echo "$SMS_DATA" | jq '.' 2>/dev/null || echo "$SMS_DATA"

SMS_RESPONSE=$(api_call "POST" "$BASE_URL/api/v1/messages" \
    "$SMS_DATA" \
    "Authorization: Bearer $ACCESS_TOKEN" \
    "Content-Type: application/json")

echo ""
echo "SMS Response:"
echo "$SMS_RESPONSE" | jq '.' 2>/dev/null || echo "$SMS_RESPONSE"

# Extract message ID
MESSAGE_ID=$(echo "$SMS_RESPONSE" | jq -r '.data.id' 2>/dev/null)

if [ "$MESSAGE_ID" != "null" ] && [ -n "$MESSAGE_ID" ]; then
    print_status 0 "SMS message sent successfully"
    echo "Message ID: $MESSAGE_ID"
else
    print_status 1 "Failed to send SMS message"
    echo "Response: $SMS_RESPONSE"
    exit 1
fi

echo ""
echo -e "${YELLOW}Step 3: Checking Message Status...${NC}"
echo "----------------------------------------"

# Wait a moment for processing
echo "Waiting 3 seconds for message processing..."
sleep 3

# Check message status in database (this would require a database query endpoint)
echo "To check message status, visit the admin panel at: $BASE_URL/admin"
echo "Look for message ID: $MESSAGE_ID"

echo ""
echo -e "${GREEN}🎉 Test completed successfully!${NC}"
echo "=================================="
echo ""
echo "Next steps:"
echo "1. Check the admin panel for message status"
echo "2. Monitor the Laravel logs for processing details"
echo "3. Use the delivery status check command: php artisan sms:check-delivery"
echo ""
echo "For more information, see:"
echo "- SMS_API_USAGE_GUIDE.md (full documentation)"
echo "- QUICK_API_REFERENCE.md (quick reference)"

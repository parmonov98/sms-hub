<?php

namespace App\Http\Controllers\Api;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="SMS Hub API",
 *     version="1.0.0",
 *     description="A comprehensive SMS gateway service that provides a unified API for sending SMS messages through multiple providers. Perfect for developers who need reliable SMS delivery with failover support and detailed usage tracking.",
 *     @OA\Contact(
 *         email="support@smshub.com",
 *         name="SMS Hub Support Team",
 *         url="https://smshub.com/support"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     ),
 *     @OA\ExternalDocumentation(
 *         description="SMS Hub Documentation",
 *         url="https://docs.smshub.com"
 *     )
 * )
 *
 * @OA\Server(
 *     url="https://smshub.devdata.uz",
 *     description="Production Server"
 * )
 *
 * @OA\Server(
 *     url="https://staging.smshub.devdata.uz",
 *     description="Staging Server"
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Development Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Enter your access token in the format: Bearer {token}"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="oauth2",
 *     type="oauth2",
 *     description="OAuth2 authentication using Laravel Passport",
 *     @OA\Flow(
 *         flow="clientCredentials",
 *         tokenUrl="/oauth/token",
 *         scopes={
 *             "read": "Read access to SMS data",
 *             "write": "Write access to send SMS messages",
 *             "admin": "Admin access to manage providers and projects"
 *         }
 *     ),
 *     @OA\Flow(
 *         flow="password",
 *         tokenUrl="/oauth/token",
 *         scopes={
 *             "read": "Read access to SMS data",
 *             "write": "Write access to send SMS messages",
 *             "admin": "Admin access to manage providers and projects"
 *         }
 *     )
 * )
 *
 * @OA\Tag(
 *     name="Authentication",
 *     description="OAuth2 authentication endpoints for obtaining access tokens"
 * )
 *
 * @OA\Tag(
 *     name="Messages",
 *     description="SMS message operations - send messages and check delivery status"
 * )
 *
 * @OA\Tag(
 *     name="Providers",
 *     description="SMS provider information and management"
 * )
 *
 * @OA\Tag(
 *     name="Usage",
 *     description="Usage statistics and billing information"
 * )
 *
 * @OA\Schema(
 *     schema="TokenResponse",
 *     type="object",
 *     title="Token Response",
 *     description="OAuth2 token response",
 *     @OA\Property(property="token_type", type="string", example="Bearer", description="Token type"),
 *     @OA\Property(property="expires_in", type="integer", example=3600, description="Token expiration time in seconds"),
 *     @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...", description="Access token"),
 *     @OA\Property(property="refresh_token", type="string", example="def50200...", description="Refresh token (for password grant)")
 * )
 *
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     title="User",
 *     description="User information",
 *     @OA\Property(property="id", type="integer", example=1, description="User ID"),
 *     @OA\Property(property="name", type="string", example="John Doe", description="User name"),
 *     @OA\Property(property="email", type="string", example="john@example.com", description="User email"),
 *     @OA\Property(property="is_admin", type="boolean", example=false, description="Admin status"),
 *     @OA\Property(property="created_at", type="string", format="datetime", example="2024-01-15T10:30:00Z", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="datetime", example="2024-01-15T10:30:00Z", description="Last update timestamp")
 * )
 *
 * @OA\Schema(
 *     schema="Message",
 *     type="object",
 *     title="SMS Message",
 *     description="SMS message object",
 *     @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000", description="Message ID"),
 *     @OA\Property(property="to", type="string", example="+998901234567", description="Recipient phone number"),
 *     @OA\Property(property="from", type="string", example="4546", description="Sender ID"),
 *     @OA\Property(property="text", type="string", example="Hello, this is a test message!", description="Message content"),
 *     @OA\Property(property="status", type="string", enum={"queued", "sent", "delivered", "failed"}, example="queued", description="Message status"),
 *     @OA\Property(property="provider_id", type="integer", example=1, description="Provider ID"),
 *     @OA\Property(property="cost", type="number", format="float", example=0.05, description="Message cost"),
 *     @OA\Property(property="parts", type="integer", example=1, description="Number of SMS parts"),
 *     @OA\Property(property="idempotency_key", type="string", example="msg_1234567890", description="Idempotency key"),
 *     @OA\Property(property="created_at", type="string", format="datetime", example="2024-01-15T10:30:00Z", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="datetime", example="2024-01-15T10:30:00Z", description="Last update timestamp")
 * )
 *
 * @OA\Schema(
 *     schema="Provider",
 *     type="object",
 *     title="SMS Provider",
 *     description="SMS provider information",
 *     @OA\Property(property="id", type="integer", example=1, description="Provider ID"),
 *     @OA\Property(property="display_name", type="string", example="Eskiz", description="Provider display name"),
 *     @OA\Property(property="description", type="string", example="Eskiz SMS provider for Uzbekistan", description="Provider description"),
 *     @OA\Property(property="is_active", type="boolean", example=true, description="Provider status"),
 *     @OA\Property(property="priority", type="integer", example=1, description="Provider priority"),
 *     @OA\Property(property="capabilities", type="object", description="Provider capabilities", example={"dlr": true, "unicode": true, "concatenation": true}),
 *     @OA\Property(property="created_at", type="string", format="datetime", example="2024-01-15T10:30:00Z", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="datetime", example="2024-01-15T10:30:00Z", description="Last update timestamp")
 * )
 *
 * @OA\Schema(
 *     schema="UsageStats",
 *     type="object",
 *     title="Usage Statistics",
 *     description="SMS usage statistics",
 *     @OA\Property(property="total_messages", type="integer", example=1250, description="Total messages sent"),
 *     @OA\Property(property="successful_messages", type="integer", example=1200, description="Successfully delivered messages"),
 *     @OA\Property(property="failed_messages", type="integer", example=50, description="Failed messages"),
 *     @OA\Property(property="total_cost", type="number", format="float", example=62.50, description="Total cost"),
 *     @OA\Property(property="success_rate", type="number", format="float", example=96.0, description="Success rate percentage"),
 *     @OA\Property(property="period", type="string", example="month", description="Statistics period"),
 *     @OA\Property(property="start_date", type="string", format="date", example="2024-01-01", description="Period start date"),
 *     @OA\Property(property="end_date", type="string", format="date", example="2024-01-31", description="Period end date")
 * )
 *
 * @OA\Schema(
 *     schema="DailyUsage",
 *     type="object",
 *     title="Daily Usage",
 *     description="Daily SMS usage statistics",
 *     @OA\Property(property="date", type="string", format="date", example="2024-01-15", description="Date"),
 *     @OA\Property(property="messages_sent", type="integer", example=45, description="Messages sent on this date"),
 *     @OA\Property(property="messages_delivered", type="integer", example=43, description="Messages delivered on this date"),
 *     @OA\Property(property="messages_failed", type="integer", example=2, description="Messages failed on this date"),
 *     @OA\Property(property="total_cost", type="number", format="float", example=2.25, description="Total cost for this date"),
 *     @OA\Property(property="success_rate", type="number", format="float", example=95.56, description="Success rate for this date")
 * )
 *
 * @OA\Schema(
 *     schema="UsageSummary",
 *     type="object",
 *     title="Usage Summary",
 *     description="SMS usage summary",
 *     @OA\Property(property="current_month", ref="#/components/schemas/UsageStats"),
 *     @OA\Property(property="last_month", ref="#/components/schemas/UsageStats"),
 *     @OA\Property(property="total_lifetime", ref="#/components/schemas/UsageStats")
 * )
 *
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     type="object",
 *     title="Error Response",
 *     description="Standard error response",
 *     @OA\Property(property="error", type="string", example="Unauthorized", description="Error type"),
 *     @OA\Property(property="message", type="string", example="Invalid or missing authentication token", description="Error message"),
 *     @OA\Property(property="code", type="integer", example=401, description="HTTP status code")
 * )
 *
 * @OA\Schema(
 *     schema="ValidationErrorResponse",
 *     type="object",
 *     title="Validation Error Response",
 *     description="Validation error response",
 *     @OA\Property(property="message", type="string", example="The given data was invalid.", description="Error message"),
 *     @OA\Property(property="errors", type="object", description="Validation errors", example={"to": {"The to field is required."}, "message": {"The message field is required."}})
 * )
 *
 * @OA\Schema(
 *     schema="PaginationLinks",
 *     type="object",
 *     title="Pagination Links",
 *     description="Pagination links",
 *     @OA\Property(property="first", type="string", example="http://api.smshub.com/v1/messages?page=1", description="First page URL"),
 *     @OA\Property(property="last", type="string", example="http://api.smshub.com/v1/messages?page=10", description="Last page URL"),
 *     @OA\Property(property="prev", type="string", example="http://api.smshub.com/v1/messages?page=2", description="Previous page URL"),
 *     @OA\Property(property="next", type="string", example="http://api.smshub.com/v1/messages?page=4", description="Next page URL")
 * )
 *
 * @OA\Schema(
 *     schema="PaginationMeta",
 *     type="object",
 *     title="Pagination Meta",
 *     description="Pagination metadata",
 *     @OA\Property(property="current_page", type="integer", example=3, description="Current page number"),
 *     @OA\Property(property="from", type="integer", example=31, description="First item number on current page"),
 *     @OA\Property(property="last_page", type="integer", example=10, description="Last page number"),
 *     @OA\Property(property="per_page", type="integer", example=15, description="Items per page"),
 *     @OA\Property(property="to", type="integer", example=45, description="Last item number on current page"),
 *     @OA\Property(property="total", type="integer", example=150, description="Total number of items")
 * )
 */
class ApiDocumentation
{
    // This class exists only to hold OpenAPI annotations
    // It will not be instantiated
}

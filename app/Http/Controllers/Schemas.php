<?php

namespace App\Http\Controllers;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Message",
 *     type="object",
 *     title="SMS Message",
 *     description="SMS message object",
 *     @OA\Property(property="id", type="string", format="uuid", description="Message ID", example="123e4567-e89b-12d3-a456-426614174000"),
 *     @OA\Property(property="to", type="string", description="Recipient phone number", example="+998901234567"),
 *     @OA\Property(property="message", type="string", description="SMS message content", example="Hello, this is a test message!"),
 *     @OA\Property(property="provider", type="string", description="SMS provider used", example="eskiz"),
 *     @OA\Property(property="sender_id", type="string", description="Sender ID", example="SMSHub"),
 *     @OA\Property(property="status", type="string", enum={"pending", "sent", "delivered", "failed"}, description="Message status", example="sent"),
 *     @OA\Property(property="priority", type="integer", description="Message priority (1-5)", example=1),
 *     @OA\Property(property="cost", type="number", format="float", description="Message cost", example=0.05),
 *     @OA\Property(property="provider_message_id", type="string", description="Provider's message ID", example="provider_12345"),
 *     @OA\Property(property="callback_url", type="string", description="Webhook URL for delivery reports", example="https://example.com/webhook"),
 *     @OA\Property(property="sent_at", type="string", format="datetime", description="Sent timestamp", example="2024-01-15T10:30:00Z"),
 *     @OA\Property(property="delivered_at", type="string", format="datetime", description="Delivery timestamp", example="2024-01-15T10:35:00Z"),
 *     @OA\Property(property="created_at", type="string", format="datetime", description="Creation timestamp", example="2024-01-15T10:30:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="datetime", description="Last update timestamp", example="2024-01-15T10:35:00Z")
 * )
 * 
 * @OA\Schema(
 *     schema="Provider",
 *     type="object",
 *     title="SMS Provider",
 *     description="SMS provider configuration",
 *     @OA\Property(property="id", type="string", format="uuid", description="Provider ID", example="123e4567-e89b-12d3-a456-426614174000"),
 *     @OA\Property(property="name", type="string", description="Provider name", example="Eskiz"),
 *     @OA\Property(property="type", type="string", description="Provider type", example="eskiz"),
 *     @OA\Property(property="description", type="string", description="Provider description", example="Eskiz SMS provider for Uzbekistan"),
 *     @OA\Property(property="is_active", type="boolean", description="Provider status", example=true),
 *     @OA\Property(property="priority", type="integer", description="Provider priority", example=1),
 *     @OA\Property(property="capabilities", type="object", description="Provider capabilities", example={"dlr": true, "unicode": true, "concatenation": true}),
 *     @OA\Property(property="created_at", type="string", format="datetime", description="Creation timestamp", example="2024-01-15T10:30:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="datetime", description="Last update timestamp", example="2024-01-15T10:30:00Z")
 * )
 * 
 * @OA\Schema(
 *     schema="UsageStats",
 *     type="object",
 *     title="Usage Statistics",
 *     description="SMS usage statistics",
 *     @OA\Property(property="period", type="string", description="Statistics period", example="month"),
 *     @OA\Property(property="total_messages", type="integer", description="Total messages sent", example=1500),
 *     @OA\Property(property="successful_messages", type="integer", description="Successfully sent messages", example=1450),
 *     @OA\Property(property="failed_messages", type="integer", description="Failed messages", example=50),
 *     @OA\Property(property="delivered_messages", type="integer", description="Delivered messages", example=1400),
 *     @OA\Property(property="total_cost", type="number", format="float", description="Total cost", example=75.50),
 *     @OA\Property(property="average_cost", type="number", format="float", description="Average cost per message", example=0.05),
 *     @OA\Property(property="by_provider", type="array", @OA\Items(ref="#/components/schemas/ProviderUsage"), description="Usage by provider")
 * )
 * 
 * @OA\Schema(
 *     schema="ProviderUsage",
 *     type="object",
 *     title="Provider Usage",
 *     description="Usage statistics for a specific provider",
 *     @OA\Property(property="provider", type="string", description="Provider name", example="eskiz"),
 *     @OA\Property(property="messages", type="integer", description="Messages sent", example=800),
 *     @OA\Property(property="cost", type="number", format="float", description="Total cost", example=40.00),
 *     @OA\Property(property="success_rate", type="number", format="float", description="Success rate percentage", example=96.5)
 * )
 * 
 * @OA\Schema(
 *     schema="DailyUsage",
 *     type="object",
 *     title="Daily Usage",
 *     description="Daily usage statistics",
 *     @OA\Property(property="date", type="string", format="date", description="Date", example="2024-01-15"),
 *     @OA\Property(property="messages", type="integer", description="Messages sent", example=50),
 *     @OA\Property(property="cost", type="number", format="float", description="Daily cost", example=2.50),
 *     @OA\Property(property="success_rate", type="number", format="float", description="Success rate percentage", example=98.0)
 * )
 * 
 * @OA\Schema(
 *     schema="UsageSummary",
 *     type="object",
 *     title="Usage Summary",
 *     description="Summary of SMS usage",
 *     @OA\Property(property="total_messages", type="integer", description="Total messages sent", example=15000),
 *     @OA\Property(property="total_cost", type="number", format="float", description="Total cost", example=750.00),
 *     @OA\Property(property="active_providers", type="integer", description="Number of active providers", example=3),
 *     @OA\Property(property="last_30_days", ref="#/components/schemas/UsageStats", description="Last 30 days statistics"),
 *     @OA\Property(property="top_providers", type="array", @OA\Items(ref="#/components/schemas/ProviderUsage"), description="Top providers by usage")
 * )
 * 
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     type="object",
 *     title="Error Response",
 *     description="Standard error response",
 *     @OA\Property(property="error", type="string", description="Error type", example="Unauthorized"),
 *     @OA\Property(property="message", type="string", description="Error message", example="Invalid or missing authentication token"),
 *     @OA\Property(property="code", type="integer", description="HTTP status code", example=401)
 * )
 * 
 * @OA\Schema(
 *     schema="ValidationErrorResponse",
 *     type="object",
 *     title="Validation Error Response",
 *     description="Validation error response",
 *     @OA\Property(property="message", type="string", description="Error message", example="The given data was invalid."),
 *     @OA\Property(property="errors", type="object", description="Validation errors", example={"to": "The to field is required.", "message": "The message field is required."})
 * )
 * 
 * @OA\Schema(
 *     schema="PaginationLinks",
 *     type="object",
 *     title="Pagination Links",
 *     description="Pagination links",
 *     @OA\Property(property="first", type="string", description="First page URL", example="http://localhost:8000/api/v1/messages?page=1"),
 *     @OA\Property(property="last", type="string", description="Last page URL", example="http://localhost:8000/api/v1/messages?page=10"),
 *     @OA\Property(property="prev", type="string", nullable=true, description="Previous page URL", example="http://localhost:8000/api/v1/messages?page=2"),
 *     @OA\Property(property="next", type="string", nullable=true, description="Next page URL", example="http://localhost:8000/api/v1/messages?page=4")
 * )
 * 
 * @OA\Schema(
 *     schema="PaginationMeta",
 *     type="object",
 *     title="Pagination Meta",
 *     description="Pagination metadata",
 *     @OA\Property(property="current_page", type="integer", description="Current page number", example=3),
 *     @OA\Property(property="from", type="integer", description="First item number on current page", example=31),
 *     @OA\Property(property="last_page", type="integer", description="Last page number", example=10),
 *     @OA\Property(property="per_page", type="integer", description="Items per page", example=15),
 *     @OA\Property(property="to", type="integer", description="Last item number on current page", example=45),
 *     @OA\Property(property="total", type="integer", description="Total number of items", example=150)
 * )
 * 
 * @OA\Schema(
 *     schema="TokenResponse",
 *     type="object",
 *     title="Token Response",
 *     description="OAuth2 token response",
 *     @OA\Property(property="token_type", type="string", description="Token type", example="Bearer"),
 *     @OA\Property(property="expires_in", type="integer", description="Token expiration in seconds", example=31536000),
 *     @OA\Property(property="access_token", type="string", description="Access token", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."),
 *     @OA\Property(property="refresh_token", type="string", description="Refresh token", example="def50200...")
 * )
 * 
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     title="User",
 *     description="User information",
 *     @OA\Property(property="id", type="integer", description="User ID", example=1),
 *     @OA\Property(property="name", type="string", description="User name", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", description="User email", example="john@example.com"),
 *     @OA\Property(property="is_admin", type="boolean", description="Admin status", example=false),
 *     @OA\Property(property="email_verified_at", type="string", format="datetime", nullable=true, description="Email verification timestamp", example="2024-01-15T10:30:00Z"),
 *     @OA\Property(property="created_at", type="string", format="datetime", description="Creation timestamp", example="2024-01-15T10:30:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="datetime", description="Last update timestamp", example="2024-01-15T10:30:00Z")
 * )
 */
class Schemas
{
    // This class is used only for Swagger schema definitions
}

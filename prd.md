📄 PRD: SMS Hub (Middleman Service for Multi-Project SMS Management)
1. Purpose

Build a centralized SMS Hub API (Laravel-based) that sits between multiple projects and SMS gateway providers.
The hub provides:

A unified API for projects to send SMS and check status/usage.

Support for multiple providers (Eskiz, PlayMobile, SMPP, etc.).

Admin CRUD for Providers (catalog) and per-project credentials.

Usage tracking, billing & delivery reports.

Secure authentication & rate limiting.

2. Goals & Non-Goals
Goals

Provide consistent endpoints for SMS actions (send, check status, usage).

Allow multiple projects to share the hub, each with their own credentials.

Support CRUD for providers and per-project credential management.

Track usage and pricing for reporting and billing.

Support delivery status updates via polling.

Non-Goals

Implement UI for end-users (only Admin backoffice is required).

Support advanced billing/payment integrations (phase 2).

Support email, push, or other non-SMS channels.

3. Key Features
3.1 Authentication

OAuth2 (Laravel Passport): email/password login → access & refresh tokens.

Admin users can create Projects and issue API credentials.

Projects authenticate via OAuth2 or issued API key.

3.2 Providers (Catalog)

Admin CRUD for providers:

id (slug), display_name, capabilities, default_config, is_enabled, priority.

Providers represent types (Eskiz, PlayMobile, SMPP).

Capabilities stored in JSON (e.g., dlr, unicode, concat).

Providers can be enabled/disabled globally.

3.3 Provider Credentials (Per Project)

Each project attaches credentials to specific providers.

Secrets stored encrypted.

Project can have multiple active providers, with one default.

Admin/owner can rotate/update credentials.

3.4 Message Sending

API: POST /v1/messages

Request includes to, from, text, optional provider, optional callback_url.

Idempotency key required to prevent duplicates.

Hub queues send job → picks provider → calls provider API → stores status.

Failover logic if provider fails (try next active by priority).

3.5 Delivery Status

Hub periodically polls provider for delivery status updates.

Status values: queued, sent, delivered, failed.

3.6 Usage & Pricing

Track per-message:

Provider, parts, cost, currency.

Aggregate daily → usage_daily table.

API: /v1/usage?from=...&to=... with filters.

3.7 Admin Portal

CRUD for Providers (catalog).

CRUD for Projects and Provider Credentials.

Dashboard: messages sent, success/failure rates, cost.

Activity logs: who created/updated/deleted provider/credentials.

3.8 Security

Encrypted storage for credentials.

HMAC request signing for projects.

IP allowlist per project.

Rate limiting per project.

4. API Endpoints (v1)
Auth

POST /oauth/token → get access/refresh token

POST /v1/auth/refresh → refresh token

Providers

GET /v1/providers → list available providers (filtered by project’s active ones)

POST /v1/admin/providers → create provider (admin only)

PATCH /v1/admin/providers/{id} → update provider

DELETE /v1/admin/providers/{id} → soft delete provider

Provider Credentials

POST /v1/admin/projects/{id}/providers → attach credentials

PATCH /v1/admin/projects/{id}/providers/{pid} → update credentials

DELETE /v1/admin/projects/{id}/providers/{pid} → detach credentials

Messages

POST /v1/messages → send SMS

GET /v1/messages/{id} → check status & price

Usage

GET /v1/usage?from=2025-08-01&to=2025-08-31 → project usage summary

5. Data Model
Tables

users: id, email, password, is_admin

projects: id, name, owner_user_id, default_provider, ip_allowlist, is_active

providers: id, display_name, description, capabilities, default_config, is_enabled, priority

provider_credentials: id, project_id, provider_id, credentials (encrypted), settings, is_active

messages: id, project_id, provider_id, provider_message_id, to, from, text, parts, status, error_code, error_message, price_decimal, currency, timestamps, idempotency_key

usage_daily: id, project_id, date, messages, parts, cost_decimal, currency

6. User Stories

Admin:

“I want to create new provider definitions so projects can use them.”

“I want to disable a provider globally if it’s unreliable.”

“I want to see all messages sent across projects.”

Project Owner:

“I want to attach my Eskiz credentials so I can send SMS from my project.”

“I want to rotate my API key without downtime.”

“I want to see how many SMS I sent last month and the total cost.”

Client App:

“I want to send SMS with a simple API call.”

"I want to check delivery status via API."

7. Non-Functional Requirements

Performance: Support 1000+ messages/minute via queue workers.

Scalability: Providers added without downtime.

Reliability: Retry failed sends with backoff; failover to next provider.

Security: OAuth2, encrypted secrets, HMAC.

Observability: Audit logs, request tracing, error monitoring.

8. Open Questions

Do we need multi-currency cost tracking now or later?

Should failover logic be global policy or configurable per project?

Do we integrate tariff tables for price calculation, or rely only on provider’s pricing API?

✅ This PRD is structured enough for a Cursor AI agent to implement step by step: migrations, models, controllers, jobs, provider drivers, and admin CRUD.

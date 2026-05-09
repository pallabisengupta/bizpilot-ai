# API Notes

The backend will be built as a Laravel 12 API consumed by the React frontend.

## API Direction

- Authentication through Laravel Sanctum.
- JSON request and response payloads.
- Tenant-aware middleware for protected routes.
- Versioned routes use `/api/v1/...`.
- Controllers should stay thin and delegate workflows to services.

## Current API Routes

| Method | Path | Purpose | Auth |
| --- | --- | --- | --- |
| GET | `/api/v1/health` | Backend health check | Public |
| POST | `/api/v1/auth/register` | Register user and issue token | Public |
| POST | `/api/v1/auth/login` | Login user and issue token | Public |
| POST | `/api/v1/auth/logout` | Revoke current Sanctum token | Bearer token |
| POST | `/api/v1/auth/forgot-password` | Send password reset link | Public |
| POST | `/api/v1/auth/reset-password` | Reset password with token | Public |
| GET | `/api/v1/auth/email/verify/{id}/{hash}` | Verify authenticated user's email | Signed URL + Bearer token |
| POST | `/api/v1/auth/email/verification-notification` | Resend verification email | Bearer token |
| GET | `/api/v1/tenants` | List authenticated user's tenant | Bearer token |
| POST | `/api/v1/tenants` | Create tenant and attach user if needed | Bearer token |
| GET | `/api/v1/tenants/{tenant}` | Show tenant | Bearer token + tenant access |
| PUT/PATCH | `/api/v1/tenants/{tenant}` | Update tenant | Bearer token + tenant access |
| DELETE | `/api/v1/tenants/{tenant}` | Delete tenant | Bearer token + tenant access |
| GET | `/api/v1/tenants/onboarding` | Show onboarding state and steps | Bearer token |
| POST | `/api/v1/tenants/onboarding` | Complete tenant onboarding | Bearer token |
| POST | `/api/v1/tenants/onboarding/step` | Save onboarding wizard step | Bearer token |
| GET | `/api/v1/schedules` | List calendar schedules | Bearer token + tenant access |
| POST | `/api/v1/schedules` | Create schedule and dispatch publish job | Bearer token + tenant access |
| GET | `/api/v1/schedules/{schedule}` | Show schedule | Bearer token + tenant access |
| PUT/PATCH | `/api/v1/schedules/{schedule}` | Update queued or failed schedule | Bearer token + tenant access |
| DELETE | `/api/v1/schedules/{schedule}` | Delete schedule | Bearer token + tenant access |
| POST | `/api/v1/schedules/{schedule}/retry` | Retry failed schedule | Bearer token + tenant access |
| GET | `/api/v1/leads/stats` | Lead dashboard cards | Bearer token + tenant access |
| GET | `/api/v1/leads` | List and filter leads | Bearer token + tenant access |
| POST | `/api/v1/leads` | Create lead | Bearer token + tenant access |
| GET | `/api/v1/leads/{lead}` | Show lead with timeline | Bearer token + tenant access |
| PUT/PATCH | `/api/v1/leads/{lead}` | Update lead | Bearer token + tenant access |
| DELETE | `/api/v1/leads/{lead}` | Delete lead | Bearer token + tenant access |
| POST | `/api/v1/leads/{lead}/assign` | Assign lead to user | Bearer token + tenant access |
| POST | `/api/v1/leads/{lead}/notes` | Add lead note and activity | Bearer token + tenant access |
| GET | `/api/v1/leads/{lead}/activities` | Lead activity timeline | Bearer token + tenant access |

## Authentication

The MVP authentication module uses Laravel Sanctum token authentication. Successful registration and login return a bearer token that the frontend must send in the `Authorization` header.

Header format:

```http
Authorization: Bearer {token}
Accept: application/json
```

Frontend API base URL is configured with `VITE_API_BASE_URL`, defaulting to `http://localhost:8000/api/v1` in the scaffold.

### Register

`POST /api/v1/auth/register`

Request:

```json
{
  "name": "Ava Founder",
  "email": "ava@example.com",
  "password": "Password123!",
  "password_confirmation": "Password123!",
  "device_name": "Chrome on Windows"
}
```

Response `201`:

```json
{
  "message": "Registration successful.",
  "data": {
    "user": {
      "id": 1,
      "name": "Ava Founder",
      "email": "ava@example.com",
      "email_verified_at": null,
      "role": "owner",
      "status": "active"
    },
    "token": "plain-text-sanctum-token",
    "token_type": "Bearer"
  }
}
```

### Login

`POST /api/v1/auth/login`

Request:

```json
{
  "email": "ava@example.com",
  "password": "Password123!",
  "device_name": "Chrome on Windows"
}
```

Response `200`:

```json
{
  "message": "Login successful.",
  "data": {
    "user": {
      "id": 1,
      "name": "Ava Founder",
      "email": "ava@example.com",
      "role": "owner",
      "status": "active"
    },
    "token": "plain-text-sanctum-token",
    "token_type": "Bearer"
  }
}
```

Invalid credentials return `422` with validation errors.

### Logout

`POST /api/v1/auth/logout`

Requires bearer token authentication.

Response `200`:

```json
{
  "message": "Logout successful."
}
```

The current Sanctum token is revoked.

### Forgot Password

`POST /api/v1/auth/forgot-password`

Request:

```json
{
  "email": "ava@example.com"
}
```

Response `200`:

```json
{
  "message": "We have emailed your password reset link."
}
```

### Reset Password

`POST /api/v1/auth/reset-password`

Request:

```json
{
  "token": "password-reset-token",
  "email": "ava@example.com",
  "password": "NewPassword123!",
  "password_confirmation": "NewPassword123!"
}
```

Response `200`:

```json
{
  "message": "Your password has been reset."
}
```

All existing Sanctum tokens for the user are revoked after a successful password reset.

### Verify Email

`GET /api/v1/auth/email/verify/{id}/{hash}`

Requires bearer token authentication and a valid signed URL.

Response `200`:

```json
{
  "message": "Email verified successfully."
}
```

### Resend Verification Email

`POST /api/v1/auth/email/verification-notification`

Requires bearer token authentication.

Response `200`:

```json
{
  "message": "Verification link sent."
}
```

## Validation And Error Shape

Validation errors return `422`:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "The email field is required."
    ]
  }
}
```

Unauthenticated requests return `401`:

```json
{
  "message": "Unauthenticated."
}
```

## Tenant Management

Tenant routes use Sanctum bearer token authentication. Routes that operate on an existing tenant also use tenant isolation middleware. The authenticated user must belong to the tenant through `users.tenant_id`.

Tenant isolation can resolve tenant context from:

- The `{tenant}` route parameter.
- `X-Tenant-ID` header.
- `X-Tenant-Slug` header.
- The authenticated user's assigned tenant.

### Create Tenant

`POST /api/v1/tenants`

Request:

```json
{
  "company_name": "BizPilot Labs",
  "slug": "bizpilot-labs",
  "industry": "software",
  "logo": "https://example.com/logo.png",
  "phone": "+15551234567",
  "email": "hello@bizpilot.test",
  "website": "https://bizpilot.test",
  "timezone": "UTC",
  "language": "en",
  "plan_id": 1,
  "status": "active"
}
```

Response `201`:

```json
{
  "message": "Tenant created successfully.",
  "data": {
    "tenant": {
      "id": 1,
      "company_name": "BizPilot Labs",
      "slug": "bizpilot-labs",
      "industry": "software",
      "logo": "https://example.com/logo.png",
      "phone": "+15551234567",
      "email": "hello@bizpilot.test",
      "website": "https://bizpilot.test",
      "timezone": "UTC",
      "language": "en",
      "plan_id": 1,
      "status": "active",
      "onboarding": {}
    }
  }
}
```

### List Tenants

`GET /api/v1/tenants`

Returns a paginated list scoped to the authenticated user's tenant.

### Show Tenant

`GET /api/v1/tenants/{tenant}`

Requires access to the requested tenant.

### Update Tenant

`PATCH /api/v1/tenants/{tenant}`

Accepts the same fields as create, all optional except fields included with `sometimes|required` validation.

Response `200`:

```json
{
  "message": "Tenant updated successfully.",
  "data": {
    "tenant": {
      "id": 1,
      "company_name": "Updated Company",
      "slug": "bizpilot-labs",
      "timezone": "Asia/Kolkata",
      "language": "en",
      "status": "active"
    }
  }
}
```

### Delete Tenant

`DELETE /api/v1/tenants/{tenant}`

Soft deletes the tenant.

Response `200`:

```json
{
  "message": "Tenant deleted successfully."
}
```

## Tenant Onboarding

The onboarding wizard lets a newly registered user create or update their business workspace before using product modules.

### Show Onboarding State

`GET /api/v1/tenants/onboarding`

Response `200`:

```json
{
  "data": {
    "tenant": null,
    "steps": [
      "company_profile",
      "business_contact",
      "plan_selection",
      "complete"
    ]
  }
}
```

### Save Onboarding Step

`POST /api/v1/tenants/onboarding/step`

Request:

```json
{
  "step": "company_profile",
  "company_name": "Pilot Works",
  "timezone": "UTC",
  "language": "en",
  "payload": {
    "industry": "services"
  }
}
```

Response `200`:

```json
{
  "message": "Tenant onboarding step saved successfully."
}
```

### Complete Onboarding

`POST /api/v1/tenants/onboarding`

Request:

```json
{
  "company_name": "Pilot Works",
  "industry": "services",
  "phone": "+15551234567",
  "email": "hello@pilot.test",
  "website": "https://pilot.test",
  "timezone": "UTC",
  "language": "en",
  "plan_id": 1
}
```

Response `200`:

```json
{
  "message": "Tenant onboarding completed successfully."
}
```

## Scheduler

Schedules are tenant-scoped and use the statuses `queued`, `processing`, `published`, and `failed`.

Creating or retrying a schedule dispatches `PublishScheduledPostJob` onto the queue. The current MVP job marks the schedule as published with a mock provider post ID; provider-specific publishing services can replace that behavior later.

### List Schedules

`GET /api/v1/schedules`

Optional query parameters:

- `status`: `queued`, `processing`, `published`, or `failed`
- `platform`: `facebook`, `instagram`, or `whatsapp`
- `date`: `YYYY-MM-DD`
- `per_page`: `1` to `100`

### Create Schedule

`POST /api/v1/schedules`

Request:

```json
{
  "title": "Friday campaign",
  "content": "Launch post for the weekend campaign.",
  "platform": "facebook",
  "scheduled_at": "2026-05-08T10:30:00Z",
  "timezone": "Asia/Kolkata",
  "media_urls": []
}
```

## Lead CRM

Lead CRM is tenant-scoped and supports statuses `new`, `contacted`, `qualified`, `won`, and `lost`.

### Lead Dashboard Stats

`GET /api/v1/leads/stats`

Response `200`:

```json
{
  "data": {
    "total_leads": 42,
    "new_leads": 8,
    "conversion_rate": 21.43
  }
}
```

### List Leads

`GET /api/v1/leads`

Optional filters:

- `status`
- `source`
- `assigned_user_id`
- `search`
- `per_page`

### Create Lead

`POST /api/v1/leads`

Request:

```json
{
  "name": "Maya Client",
  "email": "maya@example.com",
  "phone": "+15551234567",
  "company_name": "Maya Co",
  "source": "whatsapp",
  "status": "new",
  "priority": "normal",
  "notes": "Interested in a product demo."
}
```

### Assign Lead

`POST /api/v1/leads/{lead}/assign`

Request:

```json
{
  "assigned_user_id": 2
}
```

The assigned user must belong to the same tenant.

### Add Note

`POST /api/v1/leads/{lead}/notes`

Request:

```json
{
  "note": "Called the lead and confirmed interest."
}
```

Adding a note appends to the lead notes field and records a timeline activity.

Response `201`:

```json
{
  "message": "Schedule queued successfully.",
  "data": {
    "schedule": {
      "id": 1,
      "platform": "facebook",
      "status": "queued",
      "scheduled_at": "2026-05-08T10:30:00.000000Z"
    }
  }
}
```

### Retry Failed Schedule

`POST /api/v1/schedules/{schedule}/retry`

Only schedules with `failed` status can be retried.

Response `200`:

```json
{
  "message": "Failed schedule queued for retry."
}
```

## Planned API Areas

- Authentication
- AI post generation
- Social account connections
- WhatsApp lead intake and webhooks

Authentication, tenant onboarding, tenant management, scheduler, and lead CRM endpoints are implemented.

# AGENTS.md

## Project Name

BizPilot AI

## Project Goal

Build a multi-tenant SaaS platform that helps businesses manage growth, communication, and social presence from one dashboard.

The platform must support:

- AI content generation
- Social media post scheduling
- Facebook post scheduling
- Instagram post scheduling
- WhatsApp automation and lead capture
- CRM lead management
- Business dashboard analytics
- Tenant-aware team and user access

## Tech Stack

- Backend: Laravel 12 API
- Frontend: React with Vite
- Database: MySQL
- Queue: Redis
- Authentication: Laravel Sanctum
- Styling: Tailwind CSS
- Deployment: Docker

## Core Architecture Rules

- Use clean architecture principles.
- Keep the system modular and feature-oriented.
- Keep controllers thin and focused on HTTP concerns only.
- Put business workflows in service classes.
- Put database access and complex queries in repository classes.
- Avoid duplicate logic across controllers, services, jobs, and frontend modules.
- Prefer reusable abstractions only when they remove real duplication or clarify boundaries.
- Keep all code tenant-aware from the beginning.
- Use scalable naming conventions that clearly describe business intent.
- Keep API-first design in mind for every backend feature.
- Use API versioning for public and frontend-facing backend routes.
- Validate all inbound request data.
- Treat security as a default requirement, not a later hardening step.
- Use queues for slow, external, retryable, or asynchronous work.

## Development Order Rules

When creating backend features, follow this order:

1. Create migrations first.
2. Create seeders and factories where useful.
3. Create models after the database structure exists.
4. Create repositories for persistence and query logic.
5. Create services for business workflows.
6. Create jobs, events, and listeners for asynchronous or event-driven behavior.
7. Create requests, resources, and controllers.
8. Register API routes.
9. Add tests.
10. Update documentation in `docs/`.

Do not create controllers before the underlying data model and business workflow are clear.

## Backend Rules

Follow Laravel best practices.

- Use Laravel 12 conventions unless the project has a clear reason to differ.
- Use Form Request classes for validation.
- Use API Resources for structured JSON responses.
- Use policies or gates for authorization.
- Use middleware for authentication, tenant resolution, and route-level access control.
- Use service classes for business logic such as AI generation, scheduling, CRM workflows, and WhatsApp automation.
- Use repository classes for reusable persistence operations and complex queries.
- Use jobs for background processing such as publishing posts, sending WhatsApp messages, syncing external accounts, retrying failed integrations, and calling AI APIs.
- Use events and listeners when workflows need decoupled side effects.
- Never place business logic directly in routes.
- Never place large business workflows directly in controllers.
- Keep configuration in Laravel config files and environment variables.
- Do not hardcode secrets, tokens, URLs, tenant IDs, or credentials.
- Use transactions for multi-step database writes that must succeed or fail together.
- Log integration failures with useful context, but never log secrets.
- Handle external API failures with retries, backoff, and clear error states.

## Frontend Rules

Build React code with reusable components and predictable state flow.

- Use React with Vite.
- Use Tailwind CSS for styling.
- Build responsive layouts for desktop, tablet, and mobile.
- Keep reusable UI in `frontend/src/components`.
- Keep route-level screens in `frontend/src/pages`.
- Keep shared page shells in `frontend/src/layouts`.
- Keep API access in `frontend/src/services`.
- Keep reusable hooks in `frontend/src/hooks`.
- Keep state management in `frontend/src/store`.
- Keep route definitions in `frontend/src/routes`.
- Avoid duplicate UI logic across pages.
- Prefer composition over large page components.
- Use accessible controls, labels, focus states, and semantic markup.
- Keep loading, empty, error, and success states explicit.
- Do not call backend APIs directly from deeply nested UI components if a service or hook layer is more appropriate.
- Keep Tailwind class usage readable and consistent.
- Use reusable components for buttons, inputs, modals, tables, cards, tabs, filters, and dashboard widgets.

## API Rules

- Use versioned API routes, such as `/api/v1/...`.
- Return consistent JSON response shapes.
- Use proper HTTP status codes.
- Validate every request.
- Use pagination for list endpoints.
- Use filters and sorting through explicit query parameters.
- Protect tenant-owned resources from cross-tenant access.
- Keep API documentation updated in `docs/api.md`.
- Document request payloads, response payloads, authentication requirements, and error states.

## Database Rules

Use MySQL carefully and intentionally.

- Use foreign keys for relationships.
- Add indexes for foreign keys, lookup columns, filtering columns, and scheduling columns.
- Use unique constraints where business rules require uniqueness.
- Use JSON columns only where flexible structured metadata is genuinely needed.
- Prefer normal relational columns for data that is frequently filtered, joined, sorted, or reported.
- Use timestamps consistently.
- Use soft deletes only when the product needs restore or audit behavior.
- Scope tenant-owned tables with a tenant or business foreign key.
- Avoid nullable columns unless the domain really allows missing values.
- Keep migrations reversible where practical.
- Keep seeders useful for local development and testing.
- Keep factories realistic enough for tests.
- Update `docs/database.md` whenever database structure changes.

## Multi-Tenant Rules

- Every tenant-owned model must be scoped to a business or tenant.
- Never trust tenant IDs from user input without authorization checks.
- Resolve the active tenant through authenticated context, membership, or trusted middleware.
- Prevent cross-tenant data leaks in queries, policies, resources, jobs, and events.
- Queue jobs that operate on tenant data must include enough tenant context to run safely.
- External integration tokens must belong to one tenant and must not be shared across tenants.

## Security Rules

- Use Laravel Sanctum for authentication.
- Hash passwords with Laravel defaults.
- Store secrets only in environment variables or secure secret storage.
- Encrypt sensitive integration tokens where appropriate.
- Validate and authorize every protected action.
- Sanitize and validate webhook payloads.
- Verify webhook signatures when providers support them.
- Do not expose stack traces or sensitive errors to API consumers.
- Rate-limit sensitive endpoints such as login, AI generation, webhook intake, and message sending.
- Avoid mass assignment risks by defining fillable or guarded model properties intentionally.

## Queue And Async Rules

Use Redis-backed Laravel queues for:

- AI content generation
- Social post publishing
- Scheduled post execution
- WhatsApp message processing
- Webhook processing
- External API syncs
- Retryable integration calls
- Email or notification workflows

Jobs should:

- Be idempotent where possible.
- Include retry and failure behavior.
- Store useful status changes in the database.
- Avoid storing secrets directly in serialized payloads.
- Re-resolve models safely inside `handle()`.

## Testing Rules

Write both unit tests and feature tests.

- Unit test services, repositories, helpers, and isolated business rules.
- Feature test API endpoints, authentication, authorization, validation, and tenant isolation.
- Test queue dispatching for async workflows.
- Test job behavior for critical publishing and integration flows.
- Use factories for test data.
- Keep tests readable and focused on behavior.
- Add regression tests when fixing bugs.

## Documentation Rules

Always update documentation when architecture, API behavior, database structure, setup steps, or roadmap assumptions change.

Maintain:

- `docs/architecture.md`
- `docs/database.md`
- `docs/api.md`
- `docs/roadmap.md`
- `AGENTS.md`

Documentation should be practical, accurate, and useful for future development.

## Naming Rules

- Use clear business-focused names.
- Prefer names like `SocialPostService`, `LeadRepository`, `SchedulePostJob`, and `TenantMiddleware`.
- Avoid vague names like `Manager`, `Handler`, `DataHelper`, or `CommonService` unless the role is genuinely precise.
- Name database tables in Laravel conventions.
- Name React components in PascalCase.
- Name hooks with the `use` prefix.
- Name service modules by the API area they wrap.

## Code Quality Rules

- Keep files focused.
- Keep functions short enough to understand.
- Avoid hidden side effects.
- Avoid copy-paste workflows.
- Prefer explicit dependencies.
- Use meaningful exceptions and error responses.
- Keep formatting consistent with the project tooling.
- Do not add unrelated refactors while implementing a feature.
- Do not introduce feature code before the relevant scaffold and documentation are ready.

## Current Repository Stage

The repository currently contains the planned folder structure and documentation placeholders. Feature implementation should begin only after the Laravel backend and React frontend projects are scaffolded.

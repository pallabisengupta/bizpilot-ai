# BizPilot AI Architecture

BizPilot AI is planned as a modular, API-first, multi-tenant SaaS platform for business social media automation, WhatsApp lead handling, and CRM workflows.

## High-Level Stack

- Backend: Laravel 12 API
- Frontend: React with Vite
- Database: MySQL
- Queue: Redis with Laravel queue jobs
- Authentication: Laravel Sanctum
- Styling: Tailwind CSS
- Deployment: Docker

## Current Backend Foundation

The backend folder now contains a Laravel 12-ready API application scaffold.

- Composer dependencies are declared in `backend/composer.json`.
- API routes are loaded from `backend/routes/api.php`.
- API versioning starts at `/api/v1`.
- A base health route exists at `/api/v1/health`.
- MySQL is the default database connection.
- Redis is the default queue and cache backend.
- Sanctum configuration is included for API authentication.
- CORS configuration is included for the React frontend.
- API exceptions render JSON responses by default.
- Docker services are defined for PHP-FPM, queue worker, Nginx, MySQL, and Redis.

Dependencies are not installed yet because the local PHP CLI is below Laravel 12's required PHP version. The Docker configuration uses PHP 8.3 for the backend runtime.

## Core Principles

- Multi-tenant by design: every business account should have isolated data access.
- API-first backend: frontend and future integrations consume versioned API endpoints.
- Modular structure: features should be grouped by business capability as the product grows.
- Service layer: business logic should live in services, not controllers.
- Repository pattern: database access should be isolated behind repository classes where useful.
- Queue-driven processing: external posting, scheduling, webhooks, and AI generation should use jobs.
- Clean boundaries: controllers handle HTTP, services handle workflows, repositories handle persistence.

## Planned Product Modules

- Authentication and tenant onboarding
- Business workspace management
- AI social post generation
- Facebook publishing and scheduling
- Instagram publishing and scheduling
- WhatsApp lead intake
- CRM lead dashboard
- Notifications, events, and audit logging

## Backend Folder Intent

- `app/Models`: Eloquent models for tenants, users, posts, schedules, leads, and integrations.
- `app/Http`: controllers, middleware, requests, and API resources.
- `app/Services`: business workflows such as post generation, scheduling, and lead processing.
- `app/Repositories`: persistence abstractions for complex query or storage operations.
- `app/Jobs`: Redis-backed background tasks.
- `app/Events`: domain events such as lead received or post scheduled.
- `app/Listeners`: event side effects such as notifications and analytics updates.
- `app/Helpers`: small framework-agnostic helper functions.

## Frontend Folder Intent

- `src/components`: reusable UI components.
- `src/pages`: route-level screens.
- `src/layouts`: shared shell layouts for auth and dashboard views.
- `src/hooks`: reusable React hooks.
- `src/services`: API client modules.
- `src/store`: client state management.
- `src/utils`: formatting and utility helpers.
- `src/routes`: route configuration.

## Current Frontend Foundation

The frontend folder now contains a React and Vite SaaS admin application scaffold.

- Vite configuration lives in `frontend/vite.config.js`.
- Tailwind configuration lives in `frontend/tailwind.config.js`.
- React Router routes are defined in `frontend/src/routes/AppRoutes.jsx`.
- Auth and guest route guards are defined in `frontend/src/routes/AuthGuard.jsx`.
- Axios API access is centralized in `frontend/src/services`.
- Application state, auth session state, tenant state, and theme state are handled in `frontend/src/store/AppContext.jsx`.
- Reusable layout components live in `frontend/src/layouts`.
- Reusable UI components live in `frontend/src/components`.
- The first pages are login, register, dashboard, and onboarding.
- The scheduler page provides a calendar-style queue UI for platform, date/time, status, and retry workflows.
- The lead CRM page provides lead filters, assignment, notes, timeline, and dashboard cards.
- Theme support uses a `dark` class on the root HTML element and Tailwind design tokens.

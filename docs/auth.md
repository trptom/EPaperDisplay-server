Google OAuth (server as auth proxy)

Overview

This server exposes simple endpoints to use Google OAuth and act as an authentication proxy for a separate web app. The endpoints:

- GET /auth/google/redirect?origin={url}
  - Redirects the user to Google's OAuth consent page. Optionally provide `origin` to be redirected back after successful sign-in.
- GET /auth/google/callback
  - OAuth callback handler. Signs-in or creates a user and sets the session cookie.
- POST /auth/logout
  - Logs the current user out (requires session cookie).

Configuration

1. Create a Google OAuth client in Google Cloud and set the Authorized redirect URI to:
   {APP_URL}/auth/google/callback

2. Set environment variables (see `.env.example`):
   - GOOGLE_CLIENT_ID
   - GOOGLE_CLIENT_SECRET
   - GOOGLE_REDIRECT (optional, defaults to APP_URL + /auth/google/callback)

How it behaves

- After successful login the server calls `User::findOrCreateFromGoogle(...)` which links by provider/provider_id or email.
- The server performs `Auth::login($user)` which creates a Laravel session cookie. If `origin` was provided in `/auth/google/redirect`, the server will redirect the browser to that URL (the cookie will be included by the browser if the origin domain matches or is same-site—ensure CORS and cookie domain are configured appropriately).

Notes and next steps

- If the separate web app is on a different domain, consider issuing a short-lived token (JWT) or use a dedicated API token endpoint and CORS-safe flows.
- For API-style auth, consider installing Laravel Sanctum or Passport and returning tokens instead of relying on session cookies.

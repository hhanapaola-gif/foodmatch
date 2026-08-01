// EXPO_PUBLIC_API_BASE_URL comes from a ".env" file at the project root
// (see .env.example). Falls back to the Laravel dev server default so the
// app still boots without one configured.
export const API_BASE_URL = process.env.EXPO_PUBLIC_API_BASE_URL || 'http://localhost:8000/api/v1';

// Same host as API_BASE_URL but without the "/api/v1" suffix — needed to
// turn web-relative paths the API returns (e.g. plan.menu_pdf =
// "storage/menus/xxx.pdf") into absolute URLs the app can open/render.
export const SERVER_BASE_URL = API_BASE_URL.replace(/\/api\/v1\/?$/, '');

import axios from 'axios';
import * as SecureStore from 'expo-secure-store';
import { API_BASE_URL } from '../config/env';

const TOKEN_KEY = 'foodmatch_auth_token';

export const tokenStorage = {
  get: () => SecureStore.getItemAsync(TOKEN_KEY),
  set: (token) => SecureStore.setItemAsync(TOKEN_KEY, token),
  clear: () => SecureStore.deleteItemAsync(TOKEN_KEY),
};

const client = axios.create({
  baseURL: API_BASE_URL,
  timeout: 15000,
  headers: { Accept: 'application/json' },
});

client.interceptors.request.use(async (config) => {
  const token = await tokenStorage.get();
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Called by AuthContext to react to a 401 (expired/invalid token) from
// anywhere in the app without every screen having to check for it.
let onUnauthorized = null;
export const setUnauthorizedHandler = (handler) => {
  onUnauthorized = handler;
};

client.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401 && onUnauthorized) {
      onUnauthorized();
    }
    return Promise.reject(error);
  }
);

// The Laravel validators used across this API return either
// {errors: [{code, message}]} or {errors: {field: [msg]}} depending on the
// endpoint. Normalize both into a single readable string for UI display.
export function extractErrorMessage(error, fallback = 'Ocurrió un error. Intenta de nuevo.') {
  const data = error?.response?.data;
  if (!data) return error?.message || fallback;

  const { errors, message } = data;
  if (Array.isArray(errors) && errors.length) {
    return errors.map((e) => e.message || e).join('\n');
  }
  if (errors && typeof errors === 'object') {
    const first = Object.values(errors)[0];
    return Array.isArray(first) ? first[0] : String(first);
  }
  if (message) return message;
  return fallback;
}

export default client;

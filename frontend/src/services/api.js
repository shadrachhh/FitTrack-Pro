import { clearAuth, store } from './store.js';

const trimTrailingSlash = (value) => value.replace(/\/+$/, '');

const baseUrl = trimTrailingSlash(import.meta.env.VITE_API_BASE_URL || '');

export const apiFetch = async (path, options = {}) => {
    const headers = {
        ...(options.body !== undefined ? { 'Content-Type': 'application/json' } : {}),
        ...(options.headers || {})
    };

    if (store.token) {
        headers.Authorization = `Bearer ${store.token}`;
    }

    const requestUrl = path.startsWith('http://') || path.startsWith('https://')
        ? path
        : `${baseUrl}${path}`;

    const response = await fetch(requestUrl, { ...options, headers });
    const data = await response.json().catch(() => ({}));

    if (response.status === 401) {
        clearAuth();
    }

    if (!response.ok) {
        throw new Error(data.message || 'Request failed.');
    }

    return data;
};

import axios from 'axios';

export const TOKEN_STORAGE_KEY = 'token';

// Возвращает токен из постоянного или сессионного хранилища.
export function getStoredToken() {
  return localStorage.getItem(TOKEN_STORAGE_KEY) ?? sessionStorage.getItem(TOKEN_STORAGE_KEY);
}

// Сохраняет токен только в одном хранилище согласно выбору пользователя.
export function storeToken(token, remember) {
  clearStoredToken();
  const storage = remember ? localStorage : sessionStorage;
  storage.setItem(TOKEN_STORAGE_KEY, token);
}

// Удаляет токен из обоих хранилищ, чтобы исключить восстановление старой сессии.
export function clearStoredToken() {
  localStorage.removeItem(TOKEN_STORAGE_KEY);
  sessionStorage.removeItem(TOKEN_STORAGE_KEY);
}

// Единый HTTP-клиент задаёт базовый адрес и общие заголовки Laravel API.
const http = axios.create({
  baseURL: '/api',
  headers: {
    Accept: 'application/json',
  },
});

// Перед каждым запросом добавляет сохранённый Bearer-токен, если пользователь авторизован.
http.interceptors.request.use((config) => {
  const token = getStoredToken();

  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }

  return config;
});

export default http;

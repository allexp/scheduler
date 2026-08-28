import axios from 'axios';

export const TOKEN_STORAGE_KEY = 'token';

// Единый HTTP-клиент задаёт базовый адрес и общие заголовки Laravel API.
const http = axios.create({
  baseURL: '/api',
  headers: {
    Accept: 'application/json',
  },
});

// Перед каждым запросом добавляет сохранённый Bearer-токен, если пользователь авторизован.
http.interceptors.request.use((config) => {
  const token = localStorage.getItem(TOKEN_STORAGE_KEY);

  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }

  return config;
});

export default http;

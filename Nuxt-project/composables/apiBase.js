// Базовый адрес Laravel API. Берётся из runtimeConfig (nuxt.config.ts → runtimeConfig.public.apiBase),
// переопределяется переменной окружения NUXT_PUBLIC_API_BASE — так не нужно править код при деплое.
//
// Если адрес не задан (локальная разработка), API ищем на том же компьютере, с которого открыт сайт,
// на порту 8000: открыли localhost:3000 — запросы на localhost:8000, открыли с телефона
// 192.168.x.x:3000 — на 192.168.x.x:8000. Жёстко прописанный 127.0.0.1 на телефоне означал бы сам телефон.
//
// Вызывается в том числе из обработчиков событий и колбэков fetch (вне setup), где Nuxt-контекста
// уже может не быть, поэтому на клиенте есть запасной путь через сериализованный конфиг в window.__NUXT__.
const API_PORT = 8000

export function apiBase() {
  let base = ''
  try {
    base = useRuntimeConfig().public.apiBase
  } catch (e) {
    // вне Nuxt-контекста — ниже
  }
  if (!base && typeof window !== 'undefined') {
    base = window.__NUXT__?.config?.public?.apiBase
  }
  if (base) return base

  if (typeof window !== 'undefined') {
    return `${window.location.protocol}//${window.location.hostname}:${API_PORT}`
  }
  // SSR: сервер Nuxt и Laravel на одной машине
  return `http://127.0.0.1:${API_PORT}`
}

// Хост сервера websocket (laravel-websockets) — тот же компьютер, что и API
export function wsHost() {
  try {
    return new URL(apiBase()).hostname
  } catch (e) {
    return '127.0.0.1'
  }
}

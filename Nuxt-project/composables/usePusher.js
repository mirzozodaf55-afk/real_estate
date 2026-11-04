import Pusher from 'pusher-js'

// Подключение к websocket (laravel-websockets). Каналы чата и уведомлений приватные: private-chat.{a}.{b}, private-user.{id} —
// на подписку сервер спрашивает Laravel (/api/broadcasting/auth, routes/channels.php), участник ли это.
// Раньше каналы были публичными, и на чужой чат или колокольчик мог подписаться кто угодно.
export function createPusher() {
  const channelAuthorization = {
    endpoint: `${apiBase()}/api/broadcasting/auth`,
    transport: 'ajax',
    // токен берём в момент подписки: пользователь мог войти уже после создания подключения
    headersProvider: () => ({ Authorization: 'Bearer ' + getToken(), Accept: 'application/json' }),
  }

  // Облачный Pusher (публичное демо, NUXT_PUBLIC_PUSHER_KEY): шифрованное соединение — сайт там на https,
  // и браузер не пустил бы с него на незашифрованный ws://
  const { pusherKey, pusherCluster } = publicConfig()
  if (pusherKey) {
    return new Pusher(pusherKey, { cluster: pusherCluster, forceTLS: true, channelAuthorization })
  }

  return new Pusher('local', {
    wsHost: wsHost(),
    wsPort: 6001,
    forceTLS: false,
    disableStats: true,
    cluster: 'mt1',
    enabledTransports: ['ws', 'wss'],
    channelAuthorization,
  })
}

// createPusher вызывают и вне setup, где Nuxt-контекста нет, — тогда конфиг берём из страницы (как apiBase)
function publicConfig() {
  try {
    return useRuntimeConfig().public
  } catch (e) {
    return window.__NUXT__?.config?.public || {}
  }
}

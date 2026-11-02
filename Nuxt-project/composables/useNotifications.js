import Pusher from 'pusher-js'

// Общее состояние на всё приложение: колокольчик в навбаре и страница уведомлений видят одни и те же данные
const notifications = ref([])
const unread = ref(0)
const loaded = ref(false)
let listeningUserId = null
const newListeners = new Set()
let readRequest = null // незавершённый запрос «прочитать все»
let readVersion = 0 // растёт при каждом «прочитать все»: ответы, отправленные до него, устарели

// Непрочитанные сообщения чата: общий счётчик для иконки в навбаре и по каждому собеседнику для списка
const messagesUnread = ref(0)
const messagesBySender = ref({}) // { [senderId]: count }
let activeChatWith = null // id собеседника, чей чат сейчас открыт — его сообщения сразу прочитаны
const messageListeners = new Set()

function authHeaders() {
  return { Accept: 'application/json', Authorization: 'Bearer ' + getToken() }
}

export function useNotifications() {
  async function load() {
    if (!getToken()) return
    const version = readVersion
    try {
      const res = await fetch(`${apiBase()}/api/notifications`, { headers: authHeaders() })
      if (!res.ok) return
      const json = await res.json()
      // Пока шёл запрос, пользователь прочитал уведомления — ответ устарел и вернул бы старый счётчик
      if (readRequest || version !== readVersion) {
        if (readRequest) await readRequest
        return load()
      }
      notifications.value = json.data
      unread.value = json.unread
      loaded.value = true
    } catch (e) {
      // сеть недоступна — колокольчик просто останется без счётчика
    }
  }

  async function markAllRead() {
    if (!unread.value) return
    readVersion++
    const now = new Date().toISOString()
    notifications.value.forEach((n) => { if (!n.read_at) n.read_at = now })
    unread.value = 0
    readRequest = fetch(`${apiBase()}/api/notifications/read`, {
      method: 'POST',
      headers: { ...authHeaders(), 'Content-Type': 'application/json' },
      body: '{}',
    }).catch(() => { /* не критично: при следующей загрузке подтянется с сервера */ })
      .finally(() => { readRequest = null })
    await readRequest
  }

  // Подписка на новые уведомления в реальном времени (канал user.{id}, событие notify — см. App\Events\NotifyMe)
  function listen(userId) {
    if (!process.client || !userId || listeningUserId === userId) return
    listeningUserId = userId
    try {
      const pusher = new Pusher('local', {
        wsHost: wsHost(),
        wsPort: '6001',
        forceTLS: false,
        disableStats: true,
        cluster: 'mt1',
        enabledTransports: ['ws', 'wss'],
      })
      const channel = pusher.subscribe(`user.${userId}`)
      channel.bind('notify', (data) => {
        const item = data?.announ // публичное свойство события = payload уведомления
        if (!item?.id || notifications.value.some((n) => n.id === item.id)) return
        notifications.value.unshift(item)
        unread.value++
        newListeners.forEach((fn) => fn(item))
      })
      // App\Events\NewChatMessage
      channel.bind('chat-message', (data) => {
        const msg = data?.message
        const fromId = Number(msg?.from?.id)
        if (!fromId) return
        if (fromId === activeChatWith) {
          // переписка уже открыта — сообщение видно, сразу отмечаем прочитанным
          markChatRead(fromId)
          return
        }
        messagesBySender.value = { ...messagesBySender.value, [fromId]: (messagesBySender.value[fromId] || 0) + 1 }
        messagesUnread.value = typeof msg.unread_total === 'number' ? msg.unread_total : messagesUnread.value + 1
        messageListeners.forEach((fn) => fn(msg))
      })
    } catch (e) {
      listeningUserId = null
    }
  }

  function onNew(fn) {
    newListeners.add(fn)
    return () => newListeners.delete(fn)
  }

  function onNewMessage(fn) {
    messageListeners.add(fn)
    return () => messageListeners.delete(fn)
  }

  return {
    notifications, unread, loaded, load, markAllRead, listen, onNew,
    messagesUnread, messagesBySender, loadMessages, markChatRead, setActiveChat, onNewMessage,
  }
}

async function loadMessages() {
  if (!getToken()) return
  try {
    const res = await fetch(`${apiBase()}/api/messages/unread`, { headers: authHeaders() })
    if (!res.ok) return
    const json = await res.json()
    const bySender = {}
    for (const row of json.by_sender || []) {
      if (Number(row.user?.id) !== activeChatWith) bySender[row.user.id] = row.count
    }
    messagesBySender.value = bySender
    messagesUnread.value = Object.values(bySender).reduce((a, b) => a + b, 0)
  } catch (e) {
    // без сети иконка просто останется без счётчика
  }
}

async function markChatRead(friendId) {
  friendId = Number(friendId)
  if (!friendId || !getToken()) return
  const { [friendId]: count = 0, ...rest } = messagesBySender.value
  messagesBySender.value = rest
  messagesUnread.value = Math.max(0, messagesUnread.value - count)
  try {
    const res = await fetch(`${apiBase()}/api/messages/read`, {
      method: 'POST',
      headers: { ...authHeaders(), 'Content-Type': 'application/json' },
      body: JSON.stringify({ friend_id: friendId }),
    })
    if (res.ok) {
      const json = await res.json()
      if (typeof json.total === 'number') messagesUnread.value = json.total
    }
  } catch (e) {
    // не критично: при следующей загрузке подтянется с сервера
  }
}

// Чат вызывает при открытии (id собеседника) и при закрытии (null)
function setActiveChat(friendId) {
  activeChatWith = friendId ? Number(friendId) : null
  if (activeChatWith) markChatRead(activeChatWith)
}

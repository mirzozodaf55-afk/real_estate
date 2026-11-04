// ИИ-помощник: включён ли он на сервере (есть ли ключ OpenAI) — спрашиваем один раз на всё приложение
let statusRequest = null

export function useAiStatus() {
  const enabled = useState('aiEnabled', () => null)
  if (process.client && enabled.value === null && !statusRequest) {
    statusRequest = fetch(`${apiBase()}/api/ai/status`, { headers: { Accept: 'application/json' } })
      .then((r) => (r.ok ? r.json() : { enabled: false }))
      .then((d) => { enabled.value = !!d.enabled })
      .catch(() => { enabled.value = false })
  }
  return enabled
}

async function aiPost(path, body, auth = false) {
  const res = await fetch(`${apiBase()}/api${path}`, {
    method: 'POST',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      ...(auth || getToken() ? { Authorization: 'Bearer ' + getToken() } : {}),
    },
    body: JSON.stringify(body),
  })
  const json = await res.json().catch(() => ({}))
  if (!res.ok) {
    throw new Error(res.status === 429 ? 'Слишком много запросов к ИИ, подождите минуту' : (json.message || 'ИИ сейчас недоступен'))
  }
  return json
}

// Заголовок и описание объявления по характеристикам и черновику владельца
// lang — язык сайта: ИИ отвечает и пишет текст объявления на нём
export const aiDescribe = (facts, title, description) => aiPost('/ai/describe', { facts, title, description, lang: useLang().lang.value }, true)

export const aiChat = (messages) => aiPost('/ai/chat', { messages, lang: useLang().lang.value })

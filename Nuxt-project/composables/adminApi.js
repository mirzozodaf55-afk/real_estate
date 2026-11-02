// Запросы админки: токен, JSON и понятная ошибка вместо «Unexpected token <»
export async function adminApi(path, { method = 'GET', body } = {}) {
  const res = await fetch(`${apiBase()}/api/admin${path}`, {
    method,
    headers: {
      Accept: 'application/json',
      Authorization: 'Bearer ' + getToken(),
      ...(body ? { 'Content-Type': 'application/json' } : {}),
    },
    body: body ? JSON.stringify(body) : undefined,
  })
  const json = await res.json().catch(() => ({}))
  if (!res.ok) {
    const error = new Error(json.message || 'Ошибка сервера')
    error.status = res.status
    throw error
  }
  return json
}

export const isStaff = (user) => ['moderator', 'admin'].includes(user?.role)

// Счётчик «ждут проверки» — общий для меню админки и иконки в навбаре
export const usePendingCount = () => useState('adminPendingCount', () => 0)

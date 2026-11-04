// Публичное демо (NUXT_PUBLIC_DEMO_MODE=true, см. netlify.toml): вход одной кнопкой под готовым аккаунтом —
// без почты и кода. Аккаунты создаёт сервер (laravel: database/seeders/DemoSeeder.php, POST /api/demo/login).

/** Роли на кнопках и куда вести после входа */
export function demoRoles() {
  return [
    { role: 'buyer', label: 'Покупатель', hint: 'Поиск, избранное, чат с владельцем', home: '/' },
    { role: 'owner', label: 'Владелец', hint: 'Мои объявления, подача через ИИ, уведомления', home: '/personal_area/my_announ' },
    { role: 'moderator', label: 'Модератор', hint: 'Очередь проверки с оценкой риска от ИИ', home: '/admin/announs' },
    { role: 'admin', label: 'Админ', hint: 'Пользователи, продвижение, автомодерация', home: '/admin' },
  ]
}

export function isDemo() {
  try {
    return !!useRuntimeConfig().public.demoMode
  } catch (e) {
    return !!window.__NUXT__?.config?.public?.demoMode
  }
}

/** Войти под демо-аккаунтом и перейти в его раздел. Вызывается из обработчиков, где Nuxt-контекста уже нет. */
export async function demoLogin(role) {
  const res = await fetch(`${apiBase()}/api/demo/login`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
    body: JSON.stringify({ role }),
  })
  const data = await res.json().catch(() => ({}))
  if (!res.ok) throw new Error(data.message || 'Демо сейчас недоступно, попробуйте через минуту')

  // как после входа по коду (Register.vue): токен в localStorage и cookie
  localStorage.setItem('token', JSON.stringify(data.token))
  document.cookie = `token=${encodeURIComponent(data.token)}; path=/; max-age=${60 * 60 * 24 * 30}; SameSite=Lax`
  window.location.replace(demoRoles().find((r) => r.role === role)?.home || '/')
}

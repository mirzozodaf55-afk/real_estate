// Светлая / тёмная тема. Выбор хранится в localStorage 'theme'; если его нет — как в системе телефона/компьютера.
// Класс .dark на <html> ставится ещё до отрисовки скриптом в nuxt.config (app.head), здесь — переключение.
export function useTheme() {
  const theme = useState('theme', () => (process.client && document.documentElement.classList.contains('dark') ? 'dark' : 'light'))

  function apply(value) {
    theme.value = value
    if (!process.client) return
    document.documentElement.classList.toggle('dark', value === 'dark')
    try { localStorage.setItem('theme', value) } catch (e) { /* приватный режим */ }
  }

  const toggle = () => apply(theme.value === 'dark' ? 'light' : 'dark')
  return { theme, toggle, apply }
}

// Токен в localStorage должен лежать как JSON.stringify(token) (см. Register.vue),
// но если там оказалась "сырая" строка (например, токен сохранённый ещё до
// того как появился JSON.stringify, или тронутый руками в devtools) —
// JSON.parse бросает исключение прямо в теле компонента (не внутри onMounted/try),
// и компонент падает целиком, даже не успев отрендериться (именно так пропадал NavBar).
export function getToken() {
  if (typeof localStorage === 'undefined') return null
  const raw = localStorage.getItem('token')
  if (!raw) return null
  try {
    return JSON.parse(raw)
  } catch (e) {
    return raw
  }
}

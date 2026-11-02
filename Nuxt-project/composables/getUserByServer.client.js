// import { ref, onMounted } from 'vue'

const user = ref(null)
const loading = ref(false)
const loaded = ref(false) // Чтобы избежать повторных запросов

export function getUserByServer(adToken = null) {
  const token = ref(adToken)

  onMounted(async () => {
    // !loading: компоненты монтируются почти одновременно, и без этой проверки каждый слал свой
    // запрос — какой ответ приходил последним, тот и затирал пользователя.
    if (!loaded.value && !loading.value) {
      // Раньше здесь брался сырой localStorage.getItem('token') — токен лежит там как JSON-строка
      // с кавычками, и запрос уходил с "Bearer \"5|...\"" → 401.
      if (process.client && !token.value) {
        token.value = getToken()
      }

      if (!token.value) {
        return
      }

      loading.value = true

      try {
        const res = await fetch(`${apiBase()}/api/get_user`, {
          headers: {
            Authorization: 'Bearer ' + token.value,
            Accept: 'application/json',
          },
        })

        // Ответ об ошибке ({"message":"Unauthenticated."}) не должен становиться "пользователем"
        if (res.ok) {
          user.value = await res.json()
        } else if (res.status === 401) {
          // токен просрочен (30 дней) или отозван — убираем, чтобы не слать его с каждым запросом
          localStorage.removeItem('token')
          useCookie('token').value = null
        }
      } catch (err) {
        // console.error('Ошибка при получении user:', err)
      } finally {
        loading.value = false
        loaded.value = true
      }
    }
  })

  return { user, loading, loaded }
}

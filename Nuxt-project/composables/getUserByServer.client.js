// import { ref, onMounted } from 'vue'

const user = ref(null)
const loading = ref(false)
const loaded = ref(false) // Чтобы избежать повторных запросов

export function getUserByServer(adToken = null) {
  const token = ref(adToken)

  onMounted(async () => {
    if (!loaded.value) {
      if (process.client && !token.value) {
        token.value = localStorage.getItem('token')
      }

      if (!token.value) {
        console.warn('Токен не найден')
        return
      }

      loading.value = true

      try {
        const res = await fetch('http://127.0.0.1:8000/api/get_user', {
          headers: {
            Authorization: 'Bearer ' + token.value
          },
          // credentials: 'include',
        })

        user.value = await res.json()
      } catch (err) {
        // console.error('Ошибка при получении user:', err)
      } finally {
        loading.value = false
        loaded.value = true
      }
    }
  })

  return { user, loading }
}

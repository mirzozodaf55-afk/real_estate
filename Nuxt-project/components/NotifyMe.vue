<template>
  <div :class="compact ? 'inline-flex flex-col items-start' : 'flex flex-col items-center'">
    <button @click="subscribe" :disabled="state === 'saving' || state === 'done'"
      class="flex items-center gap-[8px] rounded-[10px] font-semibold transition-colors"
      :class="[
        compact ? 'h-[38px] px-[14px] text-[14px]' : 'h-[44px] px-[20px] text-[15px]',
        state === 'done'
          ? 'bg-[#ecfdf5] text-[#059669] cursor-default'
          : 'bg-[#fff7ed] text-[#ea580c] hover:bg-[#ffedd5] border border-[#fed7aa]',
      ]">
      <font-awesome-icon v-if="state === 'saving'" :icon="['fas', 'spinner']" spin-pulse />
      <font-awesome-icon v-else :icon="['fas', state === 'done' ? 'check' : 'bell']" />
      <span>{{ t(label) }}</span>
    </button>
    <p v-if="message" class="text-[13px] mt-[6px] mb-0" :class="state === 'error' ? 'text-[#ef4444]' : 'text-[#64748b]'">{{ t(message) }}</p>
  </div>
</template>

<script setup>
const props = defineProps({
  // компактный вариант — для шапки списка; обычный — для пустого результата
  compact: { type: Boolean, default: false },
})

const route = useRoute()
const { showMadoal } = useSwitch()
const { direction } = getData()
const { user } = getUserByServer(getToken())

const state = ref('idle') // idle | saving | done | error
const message = ref('')

const label = computed(() => {
  if (state.value === 'done') return 'Вы подписаны'
  return props.compact ? 'Уведомлять о новых' : 'Уведомить меня о новых объявлениях'
})

// Новый набор фильтров — новая подписка
watch(() => route.query, () => {
  state.value = 'idle'
  message.value = ''
}, { deep: true })

async function subscribe() {
  if (!getToken() || !user.value?.id) {
    direction.value = route.fullPath
    showMadoal.value = true
    return
  }
  state.value = 'saving'
  message.value = ''
  const { page, sort, ...filters } = route.query
  try {
    const res = await fetch(`${apiBase()}/api/notify-me`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json', Authorization: 'Bearer ' + getToken() },
      body: JSON.stringify({ filters }),
    })
    const json = await res.json().catch(() => ({}))
    if (!res.ok) {
      state.value = 'error'
      message.value = json.message || 'Не удалось сохранить подписку'
      return
    }
    state.value = 'done'
    message.value = json.existing
      ? 'Вы уже были подписаны на этот поиск'
      : 'Сообщим, когда появятся подходящие объявления'
  } catch (e) {
    state.value = 'error'
    message.value = 'Нет связи с сервером'
  }
}
</script>

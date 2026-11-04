<template>
  <div class="min-h-[calc(100vh-70px)] bg-[#f4f6fb] px-[16px] py-[20px]">
    <div class="max-w-[640px] mx-auto">
      <h1 class="m-0 text-[22px] font-bold text-[#152242]">{{ t('Сообщения') }}</h1>
      <p class="text-[14px] text-[#64748b] mt-[2px] mb-[14px]">{{ t('Выберите собеседника, чтобы открыть чат') }}</p>

      <div class="relative mb-[12px]">
        <font-awesome-icon :icon="['fas', 'magnifying-glass']" class="absolute left-[14px] top-1/2 -translate-y-1/2 text-[#94a3b8] text-[14px]" />
        <input v-model="q" type="search" :placeholder="t('Поиск по имени или почте')"
          class="w-full h-[44px] pl-[40px] pr-[14px] rounded-[12px] border border-[#e2e8f0] bg-white outline-none focus:border-[#0468ff] text-[15px] text-[#152242]" />
      </div>

      <div class="rounded-[16px] bg-white border border-[#e8edf3] overflow-hidden">
        <div v-if="loading" class="p-[10px] flex flex-col gap-[8px]">
          <div v-for="n in 4" :key="n" class="h-[56px] rounded-[12px] bg-[#f1f5f9] animate-pulse"></div>
        </div>
        <p v-else-if="!visible.length" class="text-center text-[14px] text-[#64748b] py-[28px] m-0">{{ q ? t('Никого не нашли') : t('Пока не с кем переписываться') }}</p>
        <button v-for="u of visible" v-else :key="u.id" type="button" @click="startMessae(u)" :data-test="`friend-${u.id}`"
          class="w-full flex items-center gap-[12px] px-[14px] py-[10px] text-left border-b border-[#eef2f7] last:border-0 hover:bg-[#f8fafc] transition-colors">
          <span class="w-[46px] h-[46px] rounded-full bg-[#e2e8f0] bg-cover bg-center shrink-0" :style="`background-image: url('${apiBase()}/api/avatar/${u.avatar}')`"></span>
          <span class="min-w-0 flex-1">
            <span class="flex items-center gap-[6px]">
              <span class="text-[15px] text-[#152242] truncate" :class="messagesBySender[u.id] ? 'font-bold' : 'font-semibold'">{{ displayName(u) }}</span>
              <span v-if="isStaff(u)" class="shrink-0 px-[6px] rounded-full bg-[#eef2ff] text-[#4338ca] text-[11px] font-semibold">{{ t('Администрация') }}</span>
            </span>
            <span class="block text-[12.5px] text-[#94a3b8] truncate">{{ messagesBySender[u.id] ? t('Новые сообщения') : (u.email || u.phone_number || '') }}</span>
          </span>
          <span v-if="messagesBySender[u.id]" data-test="friend-badge"
            class="min-w-[22px] h-[22px] px-[7px] rounded-full bg-[#ef4444] text-white text-[12px] leading-[22px] font-bold text-center shrink-0">
            {{ messagesBySender[u.id] > 99 ? '99+' : messagesBySender[u.id] }}
          </span>
          <font-awesome-icon v-else :icon="['fas', 'chevron-right']" class="text-[12px] text-[#cbd5e1]" />
        </button>
      </div>
    </div>
  </div>
</template>
<script setup>
useHead({ title: () => t('Сообщения') })
const router = useRouter()
const { user } = getUserByServer(getToken())
const { messagesBySender, loadMessages } = useNotifications()
const friends = ref([])
const loading = ref(true)
const q = ref('')

const displayName = (u) => (u.name || u.last_name ? `${u.name || ''} ${u.last_name || ''}`.trim() : (u.phone_number || u.email || t('Пользователь')))

// Те, кто написал и ещё не прочитан, — наверху списка; себя не показываем
const visible = computed(() => {
  const needle = q.value.trim().toLowerCase()
  return friends.value
    .filter((u) => u.id !== user.value?.id)
    .filter((u) => !needle || `${displayName(u)} ${u.email || ''}`.toLowerCase().includes(needle))
    .sort((a, b) => (messagesBySender.value[b.id] || 0) - (messagesBySender.value[a.id] || 0))
})

function startMessae(friend) {
  const me = user.value
  if (!me?.id) return
  router.push(`/chat-${me.id}-${me.uniq_id}-${friend.id}-${friend.uniq_id}`)
}

onMounted(async () => {
  loadMessages()
  try {
    const res = await fetch(`${apiBase()}/api/get_all_users`, { headers: { Accept: 'application/json', Authorization: 'Bearer ' + getToken() } })
    friends.value = res.ok ? ((await res.json())[0] || []) : []
  } catch (e) {
    friends.value = []
  } finally {
    loading.value = false
  }
})
</script>

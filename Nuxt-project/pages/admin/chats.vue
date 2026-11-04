<template>
  <AdminShell v-slot="{ user: me }">
    <div v-if="me.role !== 'admin'" class="bg-white rounded-[16px] border border-[#e8edf3] p-[28px] text-center text-[15px] text-[#475569]">
      {{ t('Переписки пользователей доступны только администратору.') }}
    </div>
    <template v-else>
      <div class="flex flex-wrap items-end justify-between gap-[12px] mb-[14px]">
        <div>
          <h1 class="text-[24px] font-bold text-[#152242] m-0">{{ t('Переписки') }}</h1>
          <p class="text-[14px] text-[#64748b] m-0 mt-[2px]">{{ t('Кто с кем общается — для разбора жалоб и мошенничества. Каждый просмотр записывается в журнал.') }}</p>
        </div>
        <form @submit.prevent="search" class="relative w-full sm:w-[280px]">
          <font-awesome-icon :icon="['fas', 'magnifying-glass']" class="absolute left-[12px] top-1/2 -translate-y-1/2 text-[#94a3b8] text-[13px]" />
          <input v-model="qInput" type="search" :placeholder="t('Имя, почта или телефон участника')" data-test="chats-search"
            class="w-full h-[40px] pl-[34px] pr-[12px] rounded-[10px] border border-[#e2e8f0] bg-white outline-none focus:border-[#0468ff] text-[14px]" />
        </form>
      </div>

      <div v-if="userFilter" class="mb-[12px] inline-flex items-center gap-[8px] h-[32px] pl-[12px] pr-[6px] rounded-full bg-[#eef4ff] text-[13px] text-[#0468ff] font-semibold">
        {{ t('Только переписки пользователя #') }}{{ userFilter }}
        <button @click="setQuery({ user_id: null, a: null, b: null })" class="w-[22px] h-[22px] rounded-full hover:bg-white grid place-items-center" :aria-label="t('Сбросить фильтр')">
          <font-awesome-icon :icon="['fas', 'xmark']" />
        </button>
      </div>

      <div class="grid grid-cols-1 xl:grid-cols-[400px_1fr] gap-[16px] items-start">
        <!-- Список пар -->
        <div class="bg-white rounded-[16px] border border-[#e8edf3] overflow-hidden" :class="{ 'hidden xl:block': pair }">
          <div v-if="listLoading" class="p-[12px] flex flex-col gap-[8px]">
            <div v-for="n in 5" :key="n" class="h-[64px] rounded-[12px] bg-[#f1f5f9] animate-pulse"></div>
          </div>
          <div v-else-if="listError" class="p-[24px] text-center text-[14px] text-[#b91c1c]">{{ t(listError) }}</div>
          <div v-else-if="!chats.length" class="p-[32px] text-center text-[14px] text-[#64748b]">{{ t('Переписок не найдено') }}</div>
          <ul v-else class="list-none m-0 p-[6px] max-h-[calc(100vh-260px)] overflow-y-auto">
            <li v-for="c of chats" :key="`${c.a.id}-${c.b.id}`">
              <button @click="setQuery({ a: c.a.id, b: c.b.id })" :data-test="`chat-${c.a.id}-${c.b.id}`"
                class="w-full text-left flex items-center gap-[10px] p-[8px] rounded-[12px] transition-colors"
                :class="isSelected(c) ? 'bg-[#eef4ff]' : 'hover:bg-[#f8fafc]'">
                <div class="relative w-[52px] h-[40px] shrink-0">
                  <div class="absolute left-0 top-0 w-[34px] h-[34px] rounded-full bg-[#e2e8f0] bg-cover bg-center border-2 border-white" :style="avatar(c.a)"></div>
                  <div class="absolute right-0 bottom-0 w-[34px] h-[34px] rounded-full bg-[#e2e8f0] bg-cover bg-center border-2 border-white" :style="avatar(c.b)"></div>
                </div>
                <div class="min-w-0 flex-1">
                  <div class="flex items-center justify-between gap-[6px]">
                    <span class="text-[14px] font-semibold text-[#152242] truncate">{{ name(c.a) }} ↔ {{ name(c.b) }}</span>
                    <span class="text-[11.5px] text-[#94a3b8] whitespace-nowrap">{{ timeAgo(c.last_at) }}</span>
                  </div>
                  <div class="text-[13px] text-[#64748b] truncate">
                    <span v-if="c.last_from" class="text-[#334155]">{{ name(c.last_from === c.a.id ? c.a : c.b).split(' ')[0] }}:</span> {{ tPreview(c.last_message) }}
                  </div>
                  <div class="text-[11.5px] text-[#94a3b8]">{{ c.messages_count }} {{ tn(c.messages_count, ['сообщение', 'сообщения', 'сообщений']) }}</div>
                </div>
              </button>
            </li>
          </ul>
          <div v-if="lastPage > 1" class="flex items-center justify-between px-[12px] py-[8px] border-t border-[#eef2f7] text-[13px]">
            <button :disabled="page <= 1" @click="loadList(page - 1)" class="px-[10px] h-[30px] rounded-[8px] disabled:opacity-40 hover:bg-[#f1f5f9]">{{ t('← Назад') }}</button>
            <span class="text-[#64748b]">{{ page }} {{ t('из') }} {{ lastPage }}</span>
            <button :disabled="page >= lastPage" @click="loadList(page + 1)" class="px-[10px] h-[30px] rounded-[8px] disabled:opacity-40 hover:bg-[#f1f5f9]">{{ t('Далее →') }}</button>
          </div>
        </div>

        <!-- Сообщения -->
        <div v-if="!pair" class="hidden xl:grid bg-white rounded-[16px] border border-dashed border-[#cbd5e1] min-h-[320px] place-items-center text-[14px] text-[#94a3b8]">
          {{ t('Выберите переписку слева') }}
        </div>
        <div v-else class="bg-white rounded-[16px] border border-[#e8edf3] overflow-hidden flex flex-col" data-test="thread">
          <div class="flex flex-wrap items-center gap-[10px] px-[14px] py-[10px] border-b border-[#eef2f7]">
            <button @click="setQuery({ a: null, b: null })" class="xl:hidden h-[32px] px-[10px] whitespace-nowrap rounded-[8px] text-[13px] font-semibold text-[#475569] hover:bg-[#f1f5f9]">{{ t('← К списку') }}</button>
            <template v-if="thread">
              <div v-for="u of participants" :key="u.id" class="flex items-center gap-[8px] min-w-0">
                <div class="w-[30px] h-[30px] rounded-full bg-[#e2e8f0] bg-cover bg-center shrink-0" :style="avatar(u)"></div>
                <div class="min-w-0">
                  <div class="text-[13.5px] font-semibold text-[#152242] truncate">
                    {{ name(u) }}
                    <span v-if="u.blocked_at" class="text-[11px] font-semibold text-[#dc2626]">{{ t('заблокирован') }}</span>
                  </div>
                  <div class="text-[11.5px] text-[#94a3b8] truncate">{{ u.email || u.phone_number }}</div>
                </div>
              </div>
              <span class="ml-auto text-[12px] text-[#94a3b8]">
                {{ thread.total > thread.messages.length ? t('последние {a} из {b}', { a: thread.messages.length, b: thread.total }) : `${thread.total} ${tn(thread.total, ['сообщение', 'сообщения', 'сообщений'])}` }}
              </span>
            </template>
          </div>

          <div v-if="threadLoading" class="p-[16px]"><div class="h-[300px] rounded-[12px] bg-[#f1f5f9] animate-pulse"></div></div>
          <div v-else-if="threadError" class="p-[24px] text-center text-[14px] text-[#b91c1c]">{{ t(threadError) }}</div>
          <div v-else-if="thread" ref="scroller" class="bg-[#f4f6fb] p-[14px] max-h-[calc(100vh-300px)] min-h-[300px] overflow-y-auto flex flex-col gap-[6px]">
            <div v-if="!thread.messages.length" class="m-auto text-[14px] text-[#94a3b8]">{{ t('Сообщений нет') }}</div>
            <div v-for="m of thread.messages" :key="m.id" class="flex flex-col max-w-[78%]" :class="m.user_id === pair.a ? 'self-start items-start' : 'self-end items-end'">
              <div class="px-[12px] py-[8px] rounded-[14px] text-[14px] leading-[1.45] break-words whitespace-pre-line"
                :class="m.user_id === pair.a ? 'bg-white text-[#152242] rounded-bl-[4px]' : 'bg-[#dbeafe] text-[#152242] rounded-br-[4px]'">
                <a v-if="m.type === 'picture'" :href="`${apiBase()}/api/filemessage/${m.message}`" target="_blank" rel="noopener">
                  <img :src="`${apiBase()}/api/filemessage/${m.message}`" class="max-w-[220px] max-h-[220px] rounded-[8px] block" :alt="t('Фото из чата')" loading="lazy" />
                </a>
                <a v-else-if="m.type === 'video'" :href="`${apiBase()}/api/filemessage/original/${m.message}`" target="_blank" rel="noopener" class="text-[#0468ff]">{{ t('🎬 Видео — открыть') }}</a>
                <template v-else>{{ m.message }}</template>
              </div>
              <span class="text-[11px] text-[#94a3b8] mt-[2px] px-[4px]">{{ name(thread.users[m.user_id] || { id: m.user_id }).split(' ')[0] }} · {{ dateTime(m.created_at) }}</span>
            </div>
          </div>
        </div>
      </div>
    </template>
  </AdminShell>
</template>

<script setup>
useHead({ title: () => t('Админка — переписки') })

const route = useRoute()
const router = useRouter()

const userFilter = computed(() => route.query.user_id || null)
const pair = computed(() => (route.query.a && route.query.b ? { a: Number(route.query.a), b: Number(route.query.b) } : null))
const qInput = ref(route.query.q || '')

const chats = ref([])
const page = ref(1)
const lastPage = ref(1)
const listLoading = ref(true)
const listError = ref('')
const thread = ref(null)
const threadLoading = ref(false)
const threadError = ref('')
const scroller = ref(null)

const participants = computed(() => (thread.value ? [pair.value.a, pair.value.b].map((id) => thread.value.users[id] || { id }) : []))

function setQuery(patch) {
  const next = { ...route.query, ...patch }
  Object.keys(next).forEach((k) => (next[k] === null || next[k] === '') && delete next[k])
  router.replace({ query: next })
}
const search = () => setQuery({ q: qInput.value.trim(), user_id: null, a: null, b: null })
const isSelected = (c) => pair.value && pair.value.a === c.a.id && pair.value.b === c.b.id

const name = (u) => [u?.name, u?.last_name].filter(Boolean).join(' ') || u?.email || u?.phone_number || `#${u?.id}`
const avatar = (u) => `background-image: url('${apiBase()}/api/avatar/${u?.avatar}')`
const dateTime = (d) => new Date(d).toLocaleString('ru-RU', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' })

async function loadList(p = 1) {
  listLoading.value = true
  listError.value = ''
  try {
    const params = new URLSearchParams({ page: p })
    if (userFilter.value) params.set('user_id', userFilter.value)
    else if (route.query.q) params.set('q', route.query.q)
    const res = await adminApi(`/chats?${params}`)
    chats.value = res.data
    page.value = res.current_page
    lastPage.value = res.last_page
  } catch (e) {
    listError.value = e.status === 403 ? 'Только для администратора' : 'Не удалось загрузить: ' + e.message
  } finally {
    listLoading.value = false
  }
}

async function loadThread() {
  thread.value = null
  if (!pair.value) return
  threadLoading.value = true
  threadError.value = ''
  try {
    thread.value = await adminApi(`/chats/${pair.value.a}/${pair.value.b}`)
    await nextTick()
    if (scroller.value) scroller.value.scrollTop = scroller.value.scrollHeight
  } catch (e) {
    threadError.value = 'Не удалось загрузить переписку: ' + e.message
  } finally {
    threadLoading.value = false
  }
}

watch([userFilter, () => route.query.q], () => loadList(1), { immediate: true })
// по строке, а не по объекту pair: иначе любая смена query заново грузила бы ту же переписку (и писала лишнюю строку в журнал)
watch(() => `${route.query.a}-${route.query.b}`, loadThread, { immediate: true })
</script>

<template>
  <section class="flex-1 min-w-0 p-[16px] md:p-[24px]">
    <div class="max-w-[760px]">
      <div class="mb-[16px]">
        <h2 class="text-[22px] font-bold text-[#152242] m-0">{{ t('Сохранённые поиски') }}</h2>
        <p class="text-[14px] text-[#64748b] m-0 mt-[2px]">{{ t('По каждому поиску мы присылаем уведомление, когда появляется подходящее объявление') }}</p>
      </div>

      <div v-if="loading" class="flex flex-col gap-[10px]">
        <div v-for="n in 2" :key="n" class="h-[76px] bg-white rounded-[14px] border border-[#e8edf3] animate-pulse"></div>
      </div>

      <div v-else-if="error" class="bg-white rounded-[16px] border border-[#e8edf3] p-[24px] text-center">
        <p class="text-[#152242] font-semibold m-0">{{ t(error) }}</p>
        <button @click="load" class="mt-[12px] h-[38px] px-[18px] rounded-[10px] bg-[#0468ff] text-white text-[14px] font-semibold">{{ t('Повторить') }}</button>
      </div>

      <div v-else-if="!items.length" class="bg-white rounded-[16px] border border-[#e8edf3] p-[32px] text-center">
        <div class="w-[64px] h-[64px] mx-auto rounded-full bg-[#eef4ff] grid place-items-center text-[#0468ff] text-[26px]">
          <font-awesome-icon :icon="['far', 'bookmark']" />
        </div>
        <p class="text-[18px] font-bold text-[#152242] mt-[14px] mb-[4px]">{{ t('Нет сохранённых поисков') }}</p>
        <p class="text-[14px] text-[#64748b] m-0">{{ t('Выберите фильтры в списке объявлений и нажмите «Уведомлять о новых».') }}</p>
        <NuxtLink to="/list-announ" class="inline-flex mt-[16px] h-[40px] px-[18px] rounded-[10px] bg-[#0468ff] text-white text-[14px] font-semibold items-center no-underline">
          {{ t('К объявлениям') }}
        </NuxtLink>
      </div>

      <ul v-else class="list-none p-0 m-0 flex flex-col gap-[10px]">
        <li v-for="s of items" :key="s.id" class="bg-white rounded-[14px] border border-[#e8edf3] p-[14px] flex flex-wrap items-center gap-[12px]">
          <div class="w-[40px] h-[40px] rounded-full bg-[#fff7ed] grid place-items-center text-[#ea580c] shrink-0">
            <font-awesome-icon :icon="['fas', 'bell']" />
          </div>
          <div class="min-w-0 flex-1">
            <div class="text-[15px] font-semibold text-[#152242] break-words">{{ describeFilters(s.filters) }}</div>
            <div class="text-[12px] text-[#94a3b8] mt-[2px]">{{ t('Сохранён') }} {{ timeAgo(s.created_at) }}</div>
          </div>
          <div class="flex items-center gap-[8px] ml-auto">
            <NuxtLink :to="{ path: '/list-announ', query: s.filters || {} }"
              class="h-[36px] px-[14px] rounded-[10px] bg-[#eef4ff] text-[#0468ff] text-[14px] font-semibold flex items-center no-underline hover:bg-[#dbe8ff]">
              {{ t('Показать') }}
            </NuxtLink>
            <button @click="remove(s)" :disabled="removing === s.id"
              class="h-[36px] w-[36px] rounded-[10px] border border-[#e2e8f0] text-[#64748b] hover:text-[#ef4444] hover:border-[#fecaca] grid place-items-center"
              :aria-label="t('Удалить подписку')">
              <font-awesome-icon v-if="removing === s.id" :icon="['fas', 'spinner']" spin-pulse />
              <font-awesome-icon v-else :icon="['far', 'trash-can']" />
            </button>
          </div>
        </li>
      </ul>
    </div>
  </section>
</template>

<script setup>
useHead({ title: () => t('Сохранённые поиски') })

const items = ref([])
const loading = ref(true)
const error = ref('')
const removing = ref(null)

const headers = () => ({ Accept: 'application/json', Authorization: 'Bearer ' + getToken() })

async function load() {
  loading.value = true
  error.value = ''
  try {
    const res = await fetch(`${apiBase()}/api/notify-me`, { headers: headers() })
    if (!res.ok) throw new Error()
    items.value = await res.json()
  } catch (e) {
    error.value = 'Не удалось загрузить сохранённые поиски'
  } finally {
    loading.value = false
  }
}

async function remove(s) {
  removing.value = s.id
  try {
    const res = await fetch(`${apiBase()}/api/notify-me/${s.id}`, { method: 'DELETE', headers: headers() })
    if (res.ok || res.status === 404) items.value = items.value.filter((i) => i.id !== s.id)
  } finally {
    removing.value = null
  }
}

onMounted(load)
</script>

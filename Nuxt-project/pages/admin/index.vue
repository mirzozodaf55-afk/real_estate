<template>
  <AdminShell v-slot="{ user }">
    <div class="flex flex-wrap items-end justify-between gap-[12px] mb-[16px]">
      <div>
        <h1 class="text-[24px] font-bold text-[#152242] m-0">{{ t('Обзор') }}</h1>
        <p class="text-[14px] text-[#64748b] m-0 mt-[2px]">{{ t('Состояние объявлений и очереди модерации') }}</p>
      </div>
      <NuxtLink to="/admin/announs" class="h-[40px] px-[16px] rounded-[10px] bg-[#0468ff] hover:bg-[#0353cc] text-white text-[14px] font-semibold flex items-center gap-[8px] no-underline">
        <font-awesome-icon :icon="['fas', 'list-check']" /> {{ t('К очереди') }}
      </NuxtLink>
    </div>

    <div v-if="error" class="bg-white rounded-[16px] border border-[#fecaca] p-[20px] text-[#b91c1c]">{{ t(error) }}</div>

    <div v-else class="grid grid-cols-2 xl:grid-cols-4 gap-[12px]">
      <NuxtLink v-for="card of cards" :key="card.label" :to="card.to"
        class="bg-white rounded-[16px] border border-[#e8edf3] p-[16px] no-underline hover:shadow-[0_6px_24px_rgba(15,23,42,0.08)] transition-shadow">
        <div class="flex items-center justify-between">
          <span class="text-[13px] font-semibold text-[#64748b]">{{ t(card.label) }}</span>
          <span class="w-[34px] h-[34px] rounded-[10px] grid place-items-center" :class="card.tone">
            <font-awesome-icon :icon="card.icon" />
          </span>
        </div>
        <div class="text-[30px] font-bold text-[#152242] mt-[6px] leading-none">
          <span v-if="stats">{{ formatNumber(card.value) }}</span>
          <span v-else class="inline-block w-[60px] h-[28px] rounded-[6px] bg-[#eef2f7] animate-pulse"></span>
        </div>
        <div class="text-[12.5px] text-[#94a3b8] mt-[6px] min-h-[18px]">{{ stats ? card.hint : '' }}</div>
      </NuxtLink>
    </div>

    <!-- Автомодерация: режим меняет только админ, модератор видит, что включено -->
    <div v-if="stats" class="mt-[16px] bg-white rounded-[16px] border border-[#e8edf3] p-[18px]" data-test="auto-moderation">
      <div class="flex flex-wrap items-center justify-between gap-[8px]">
        <h2 class="text-[16px] font-bold text-[#152242] m-0">
          <font-awesome-icon :icon="['fas', 'wand-magic-sparkles']" class="text-[#7c3aed]" /> {{ t('Автомодерация') }}
        </h2>
        <span class="text-[13px] text-[#64748b]">{{ t('решено автоматически сегодня: {n}', { n: stats.auto_moderated_today ?? 0 }) }}</span>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-[8px] mt-[12px]">
        <button v-for="m of AUTO_MODES" :key="m.value" @click="setAutoMode(m.value)" :disabled="user.role !== 'admin' || autoSaving"
          :data-test="`auto-${m.value}`"
          class="text-left rounded-[12px] border p-[12px] transition-colors disabled:cursor-default"
          :class="stats.auto_moderation === m.value ? 'border-[#0468ff] bg-[#f0f6ff]' : 'border-[#e2e8f0] bg-white enabled:hover:border-[#94a3b8]'">
          <div class="text-[14px] font-semibold" :class="stats.auto_moderation === m.value ? 'text-[#0468ff]' : 'text-[#152242]'">
            <font-awesome-icon :icon="['fas', stats.auto_moderation === m.value ? 'circle-dot' : 'circle']" class="mr-[4px]" /> {{ t(m.label) }}
          </div>
          <div class="text-[12.5px] text-[#64748b] mt-[4px] leading-[1.45]">{{ t(m.hint) }}</div>
        </button>
      </div>
      <p v-if="stats.auto_moderation !== 'off' && !stats.ai_enabled" class="text-[13px] text-[#b45309] mt-[10px] mb-0">
        {{ t('ИИ не настроен (OPENAI_API_KEY) — автомодерации не на что опереться, всё остаётся в очереди.') }}
      </p>
      <p v-else-if="stats.auto_moderation !== 'off' && !stats.vision_enabled" class="text-[13px] text-[#b45309] mt-[10px] mb-0">
        {{ t('Проверка фото выключена (OPENAI_VISION_MODEL) — объявления с фото будут ждать модератора.') }}
      </p>
      <p v-if="autoError" class="text-[13px] text-[#b91c1c] mt-[10px] mb-0">{{ autoError }}</p>
    </div>

    <div class="mt-[16px] bg-white rounded-[16px] border border-[#e8edf3] p-[18px]">
      <h2 class="text-[16px] font-bold text-[#152242] m-0">{{ t('Как работает модерация') }}</h2>
      <ol class="text-[14px] text-[#475569] leading-[1.7] mt-[8px] mb-0 pl-[20px]">
        <li>{{ t('Новое или изменённое объявление попадает в очередь и скрыто из поиска.') }}</li>
        <li>{{ t('Модератор одобряет его — объявление появляется в поиске, владелец и подписчики «Уведомить меня» получают уведомление и письмо.') }}</li>
        <li>{{ t('Или отклоняет с причиной — владелец видит её в «Моих объявлениях», исправляет, и объявление снова уходит на проверку.') }}</li>
      </ol>
      <p v-if="user.role === 'admin'" class="text-[13px] text-[#64748b] mt-[10px] mb-0">
        {{ t('Модераторов назначают в разделе') }} <NuxtLink to="/admin/users" class="text-[#0468ff]">{{ t('Пользователи') }}</NuxtLink>.
      </p>
    </div>
  </AdminShell>
</template>

<script setup>
useHead({ title: () => t('Админка — обзор') })

const stats = ref(null)
const error = ref('')

const cards = computed(() => {
  const s = stats.value || {}
  return [
    { label: 'Ждут проверки', value: s.pending, icon: ['fas', 'hourglass-half'], tone: 'bg-[#fffbeb] text-[#d97706]', to: '/admin/announs',
      hint: s.photo_alerts ? t('⚠ с подозрительными фото: {n}', { n: s.photo_alerts }) : s.oldest_pending_at ? t('самое старое — {x}', { x: timeAgo(s.oldest_pending_at) }) : t('очередь пуста') },
    { label: 'Опубликовано', value: s.approved, icon: ['fas', 'circle-check'], tone: 'bg-[#ecfdf5] text-[#047857]', to: '/admin/announs?status=approved',
      hint: t('проверено сегодня: {n} · продано/сдано: {s}', { n: s.moderated_today ?? 0, s: s.sold ?? 0 }) },
    { label: 'Отклонено', value: s.rejected, icon: ['fas', 'circle-xmark'], tone: 'bg-[#fef2f2] text-[#dc2626]', to: '/admin/announs?status=rejected', hint: t('ждут исправления владельцем') },
    { label: 'Пользователи', value: s.users, icon: ['fas', 'users'], tone: 'bg-[#eef2ff] text-[#4338ca]', to: '/admin/users', hint: t('в команде: {n} · заблокировано: {b}', { n: s.staff ?? 0, b: s.blocked ?? 0 }) },
  ]
})

const AUTO_MODES = [
  { value: 'off', label: 'Выключена', hint: 'Каждое объявление проверяет модератор.' },
  { value: 'approve', label: 'Одобрять чистые', hint: 'ИИ не нашёл проблем в тексте, цене и фото, нет дубликатов, владельцу раньше не отказывали — публикуется сразу. Остальное ждёт модератора.' },
  { value: 'full', label: 'Полная — без модератора', hint: 'ИИ проверяет каждое объявление. Высокий риск — отклоняется с причиной, похожие на другие объявления ждут модератора, остальное публикуется сразу.' },
]
const autoSaving = ref(false)
const autoError = ref('')
async function setAutoMode(mode) {
  if (mode === stats.value.auto_moderation) return
  autoSaving.value = true
  autoError.value = ''
  try {
    const res = await adminApi('/settings/auto-moderation', { method: 'POST', body: { mode } })
    stats.value = { ...stats.value, auto_moderation: res.auto_moderation }
  } catch (e) {
    autoError.value = t('Не удалось сохранить: {x}', { x: t(e.message) })
  } finally {
    autoSaving.value = false
  }
}

onMounted(async () => {
  try {
    stats.value = await adminApi('/stats')
  } catch (e) {
    error.value = e.status === 403 ? 'Недостаточно прав' : 'Не удалось загрузить данные: ' + e.message
  }
})
</script>

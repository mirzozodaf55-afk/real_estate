<template>
  <AdminShell v-slot="{ user }">
    <div class="flex flex-wrap items-end justify-between gap-[12px] mb-[16px]">
      <div>
        <h1 class="text-[24px] font-bold text-[#152242] m-0">Обзор</h1>
        <p class="text-[14px] text-[#64748b] m-0 mt-[2px]">Состояние объявлений и очереди модерации</p>
      </div>
      <NuxtLink to="/admin/announs" class="h-[40px] px-[16px] rounded-[10px] bg-[#0468ff] hover:bg-[#0353cc] text-white text-[14px] font-semibold flex items-center gap-[8px] no-underline">
        <font-awesome-icon :icon="['fas', 'list-check']" /> К очереди
      </NuxtLink>
    </div>

    <div v-if="error" class="bg-white rounded-[16px] border border-[#fecaca] p-[20px] text-[#b91c1c]">{{ error }}</div>

    <div v-else class="grid grid-cols-2 xl:grid-cols-4 gap-[12px]">
      <NuxtLink v-for="card of cards" :key="card.label" :to="card.to"
        class="bg-white rounded-[16px] border border-[#e8edf3] p-[16px] no-underline hover:shadow-[0_6px_24px_rgba(15,23,42,0.08)] transition-shadow">
        <div class="flex items-center justify-between">
          <span class="text-[13px] font-semibold text-[#64748b]">{{ card.label }}</span>
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

    <div class="mt-[16px] bg-white rounded-[16px] border border-[#e8edf3] p-[18px]">
      <h2 class="text-[16px] font-bold text-[#152242] m-0">Как работает модерация</h2>
      <ol class="text-[14px] text-[#475569] leading-[1.7] mt-[8px] mb-0 pl-[20px]">
        <li>Новое или изменённое объявление попадает в очередь и скрыто из поиска.</li>
        <li>Модератор одобряет его — объявление появляется в поиске, владелец и подписчики «Уведомить меня» получают уведомление и письмо.</li>
        <li>Или отклоняет с причиной — владелец видит её в «Моих объявлениях», исправляет, и объявление снова уходит на проверку.</li>
      </ol>
      <p v-if="user.role === 'admin'" class="text-[13px] text-[#64748b] mt-[10px] mb-0">
        Модераторов назначают в разделе <NuxtLink to="/admin/users" class="text-[#0468ff]">Пользователи</NuxtLink>.
      </p>
    </div>
  </AdminShell>
</template>

<script setup>
useHead({ title: 'Админка — обзор' })

const stats = ref(null)
const error = ref('')

const cards = computed(() => {
  const s = stats.value || {}
  return [
    { label: 'Ждут проверки', value: s.pending, icon: ['fas', 'hourglass-half'], tone: 'bg-[#fffbeb] text-[#d97706]', to: '/admin/announs',
      hint: s.photo_alerts ? `⚠ с подозрительными фото: ${s.photo_alerts}` : s.oldest_pending_at ? `самое старое — ${timeAgo(s.oldest_pending_at)}` : 'очередь пуста' },
    { label: 'Опубликовано', value: s.approved, icon: ['fas', 'circle-check'], tone: 'bg-[#ecfdf5] text-[#047857]', to: '/admin/announs?status=approved',
      hint: `проверено сегодня: ${s.moderated_today ?? 0} · продано/сдано: ${s.sold ?? 0}` },
    { label: 'Отклонено', value: s.rejected, icon: ['fas', 'circle-xmark'], tone: 'bg-[#fef2f2] text-[#dc2626]', to: '/admin/announs?status=rejected', hint: 'ждут исправления владельцем' },
    { label: 'Пользователи', value: s.users, icon: ['fas', 'users'], tone: 'bg-[#eef2ff] text-[#4338ca]', to: '/admin/users', hint: `в команде: ${s.staff ?? 0} · заблокировано: ${s.blocked ?? 0}` },
  ]
})

onMounted(async () => {
  try {
    stats.value = await adminApi('/stats')
  } catch (e) {
    error.value = e.status === 403 ? 'Недостаточно прав' : 'Не удалось загрузить данные: ' + e.message
  }
})
</script>

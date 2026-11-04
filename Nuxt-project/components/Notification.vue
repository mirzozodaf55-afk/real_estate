<template>
  <section class="flex-1 min-w-0 p-[16px] md:p-[24px]">
    <div class="max-w-[760px]">
      <div class="flex flex-wrap items-center justify-between gap-[10px] mb-[16px]">
        <div>
          <h2 class="text-[22px] font-bold text-[#152242] m-0">{{ t('Уведомления') }}</h2>
          <p class="text-[14px] text-[#64748b] m-0 mt-[2px]">{{ t('Новые объявления по вашим поискам, решения модератора и сигналы для модерации') }}</p>
        </div>
        <NuxtLink to="/personal_area/store_search"
          class="h-[38px] px-[14px] rounded-[10px] bg-white border border-[#e2e8f0] text-[14px] font-semibold text-[#152242] flex items-center gap-[8px] no-underline hover:border-[#0468ff]">
          <font-awesome-icon :icon="['far', 'bookmark']" class="text-[#0468ff]" /> {{ t('Мои подписки') }}
        </NuxtLink>
      </div>

      <div v-if="!loaded" class="flex flex-col gap-[10px]">
        <div v-for="n in 3" :key="n" class="h-[96px] bg-white rounded-[14px] border border-[#e8edf3] animate-pulse"></div>
      </div>

      <div v-else-if="!notifications.length" class="bg-white rounded-[16px] border border-[#e8edf3] p-[32px] text-center">
        <div class="w-[64px] h-[64px] mx-auto rounded-full bg-[#fff7ed] grid place-items-center text-[#ea580c] text-[26px]">
          <font-awesome-icon :icon="['far', 'bell']" />
        </div>
        <p class="text-[18px] font-bold text-[#152242] mt-[14px] mb-[4px]">{{ t('Пока нет уведомлений') }}</p>
        <p class="text-[14px] text-[#64748b] m-0">
          {{ t('Настройте фильтры в списке объявлений и нажмите «Уведомлять о новых» —') }}<br class="hidden md:inline" />
          {{ t('мы сообщим, как только появится подходящее объявление.') }}
        </p>
        <NuxtLink to="/list-announ" class="inline-flex mt-[16px] h-[40px] px-[18px] rounded-[10px] bg-[#0468ff] text-white text-[14px] font-semibold items-center no-underline">
          {{ t('К объявлениям') }}
        </NuxtLink>
      </div>

      <ul v-else class="list-none p-0 m-0 flex flex-col gap-[10px]">
        <li v-for="n of notifications" :key="n.id">
          <button @click="open(n)"
            class="w-full text-left bg-white rounded-[14px] border p-[12px] flex gap-[12px] items-center transition-shadow hover:shadow-[0_6px_24px_rgba(15,23,42,0.08)]"
            :class="unreadIds.has(n.id) ? 'border-[#bfdbfe] bg-[#f8fbff]' : 'border-[#e8edf3]'">
            <div class="w-[88px] h-[66px] md:w-[110px] md:h-[78px] rounded-[10px] overflow-hidden bg-[#e2e8f0] shrink-0">
              <img v-if="n.announ.picture" :src="`${apiBase()}/api/image/small/${n.announ.picture}`" class="w-full h-full object-cover" alt="" loading="lazy" />
              <div v-else class="w-full h-full grid place-items-center text-[#94a3b8]"><font-awesome-icon :icon="['far', 'image']" /></div>
            </div>
            <div class="min-w-0 flex-1">
              <div v-if="n.kind === 'approved'" class="text-[13px] font-semibold text-[#047857] mb-[2px]">
                <font-awesome-icon :icon="['fas', 'circle-check']" /> {{ t('Ваше объявление опубликовано') }}
              </div>
              <div v-else-if="n.kind === 'rejected'" class="text-[13px] font-semibold text-[#dc2626] mb-[2px] truncate">
                <font-awesome-icon :icon="['fas', 'circle-exclamation']" /> {{ t('Отклонено:') }} {{ tNotice(n) }}
              </div>
              <div v-else-if="n.kind === 'needs_changes'" class="text-[13px] font-semibold text-[#ea580c] mb-[2px] truncate">
                <font-awesome-icon :icon="['fas', 'pen']" /> {{ t('Нужна доработка:') }} {{ tNotice(n) }}
              </div>
              <div v-else-if="n.kind === 'promoted'" class="text-[13px] font-semibold text-[#b45309] mb-[2px]">
                <font-awesome-icon :icon="['fas', 'crown']" /> {{ t('Продвижение включено:') }} {{ tNotice(n) }}
              </div>
              <div v-else-if="n.kind === 'promo_rejected'" class="text-[13px] font-semibold text-[#dc2626] mb-[2px] truncate">
                <font-awesome-icon :icon="['fas', 'circle-exclamation']" /> {{ t('Заявка на продвижение отклонена:') }} {{ tNotice(n) }}
              </div>
              <div v-else-if="n.kind === 'photo_alert'" class="text-[13px] font-semibold text-[#b45309] mb-[2px] line-clamp-2" data-test="photo-alert">
                <font-awesome-icon :icon="['fas', 'wand-magic-sparkles']" /> {{ t('ИИ, объявление №') }}{{ n.announ.id }}: {{ tNotice(n) }}
              </div>
              <div class="flex items-center gap-[8px]">
                <span v-if="unreadIds.has(n.id)" class="w-[8px] h-[8px] rounded-full bg-[#0468ff] shrink-0" :aria-label="t('Новое')"></span>
                <span class="text-[16px] font-bold text-[#152242]">{{ formatNumber(n.announ.price) }} {{ t('сомони') }}<span class="text-[13px] font-normal text-[#64748b]">{{ priceSuffix(n.announ) }}</span></span>
              </div>
              <div class="text-[14px] text-[#0468ff] font-semibold truncate mt-[2px]">{{ n.announ.title || t(n.announ.type_object) }}</div>
              <div class="text-[13px] text-[#64748b] truncate mt-[2px]">
                {{ [t(n.announ.type_object), announFacts(n.announ)].filter(Boolean).join(' · ') }}
              </div>
            </div>
            <span class="text-[12px] text-[#94a3b8] whitespace-nowrap self-start">{{ timeAgo(n.created_at) }}</span>
          </button>
        </li>
      </ul>
    </div>
  </section>
</template>

<script setup>
useHead({ title: () => t('Уведомления') })
const router = useRouter()
const { notifications, loaded, load, markAllRead } = useNotifications()

// Подсвечиваем те, что были непрочитанными на момент открытия, а на сервере сразу отмечаем прочитанными
const unreadIds = ref(new Set())

onMounted(async () => {
  await load()
  unreadIds.value = new Set(notifications.value.filter((n) => !n.read_at).map((n) => n.id))
  markAllRead()
})

function open(n) {
  // решение модератора — про своё объявление: ведём туда, где его можно исправить
  if (['approved', 'rejected', 'needs_changes', 'promoted', 'promo_rejected'].includes(n.kind)) return router.push('/personal_area/my_announ')
  // модератору — сразу в карточку модерации этого объявления
  if (n.kind === 'photo_alert') return router.push(`/admin/announs?status=${n.announ.moderation_status || 'pending'}&id=${n.announ.id}`)
  router.push(`/show_announ-${n.announ.id}`)
}
</script>

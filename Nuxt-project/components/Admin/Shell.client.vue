<template>
  <div class="min-h-[calc(100vh-70px)] bg-[#f4f6fb]">
    <div v-if="loading" class="grid place-items-center py-[120px] text-[#94a3b8]">
      <font-awesome-icon :icon="['fas', 'spinner']" spin-pulse class="text-[28px]" />
    </div>

    <div v-else-if="!isStaff(user)" class="max-w-[440px] mx-auto px-[16px] py-[80px] text-center">
      <div class="w-[64px] h-[64px] mx-auto rounded-full bg-[#fef2f2] grid place-items-center text-[#dc2626] text-[26px]">
        <font-awesome-icon :icon="['fas', 'lock']" />
      </div>
      <h1 class="text-[22px] font-bold text-[#152242] mt-[16px] mb-[6px]">{{ t('Нет доступа') }}</h1>
      <p class="text-[15px] text-[#64748b] m-0">
        {{ user ? t('Раздел доступен только модераторам и администраторам.') : t('Войдите в аккаунт модератора или администратора.') }}
      </p>
      <NuxtLink to="/" class="inline-flex mt-[18px] h-[40px] px-[18px] rounded-[10px] bg-[#0468ff] text-white text-[14px] font-semibold items-center no-underline">{{ t('На главную') }}</NuxtLink>
    </div>

    <div v-else class="max-w-[1320px] mx-auto px-[16px] py-[20px] flex flex-col lg:flex-row gap-[20px]">
      <aside class="lg:w-[230px] shrink-0">
        <div class="bg-white rounded-[16px] border border-[#e8edf3] p-[10px] lg:sticky lg:top-[16px]">
          <div class="px-[10px] pt-[6px] pb-[12px] border-b border-[#eef2f7] mb-[8px] hidden lg:block">
            <div class="text-[12px] uppercase tracking-[0.08em] text-[#94a3b8] font-bold">{{ t('Админка') }}</div>
            <div class="text-[14px] font-semibold text-[#152242] mt-[4px] truncate">{{ user.name || user.email }}</div>
            <span class="inline-block mt-[4px] px-[8px] py-[1px] rounded-full text-[12px] font-semibold"
              :class="user.role === 'admin' ? 'bg-[#eef2ff] text-[#4338ca]' : 'bg-[#ecfeff] text-[#0e7490]'">
              {{ user.role === 'admin' ? t('Администратор') : t('Модератор') }}
            </span>
          </div>
          <nav class="flex lg:flex-col gap-[4px] overflow-x-auto">
            <NuxtLink v-for="item of menu" :key="item.to" :to="item.to"
              class="flex items-center gap-[10px] px-[12px] h-[42px] rounded-[10px] text-[14.5px] font-semibold no-underline whitespace-nowrap transition-colors"
              :class="isActive(item) ? 'bg-[#eef4ff] text-[#0468ff]' : 'text-[#334155] hover:bg-[#f1f5f9]'">
              <font-awesome-icon :icon="item.icon" class="w-[16px]" />
              <span class="flex-1">{{ t(item.label) }}</span>
              <span v-if="item.badge" class="min-w-[22px] h-[20px] px-[6px] rounded-full bg-[#f59e0b] text-white text-[12px] leading-[20px] text-center">{{ item.badge }}</span>
            </NuxtLink>
            <NuxtLink to="/" class="flex items-center gap-[10px] px-[12px] h-[42px] rounded-[10px] text-[14.5px] font-semibold no-underline whitespace-nowrap text-[#64748b] hover:bg-[#f1f5f9] lg:mt-[8px] lg:border-t lg:border-[#eef2f7] lg:rounded-t-none lg:pt-[4px]">
              <font-awesome-icon :icon="['fas', 'arrow-left']" class="w-[16px]" /> {{ t('На сайт') }}
            </NuxtLink>
          </nav>
        </div>
      </aside>

      <main class="flex-1 min-w-0">
        <slot :user="user" />
      </main>
    </div>
  </div>
</template>

<script setup>
const route = useRoute()
const { user, loaded } = getUserByServer(getToken())
const pending = usePendingCount()
// пока пользователь грузится — крутилка, а не мелькающее «Нет доступа»
const loading = computed(() => !user.value && !!getToken() && !loaded.value)

const menu = computed(() => [
  { to: '/admin', label: 'Обзор', icon: ['fas', 'chart-simple'], exact: true },
  { to: '/admin/announs', label: 'Модерация', icon: ['fas', 'list-check'], badge: pending.value || null },
  ...(user.value?.role === 'admin' ? [
    { to: '/admin/users', label: 'Пользователи', icon: ['fas', 'users'] },
    { to: '/admin/reasons', label: 'Причины отказа', icon: ['fas', 'list-ul'] },
    { to: '/admin/promotions', label: 'Продвижение', icon: ['fas', 'crown'] },
    { to: '/admin/chats', label: 'Переписки', icon: ['far', 'comments'] },
    { to: '/admin/appearance', label: 'Оформление', icon: ['far', 'image'] },
  ] : []),
])
const isActive = (item) => (item.exact ? route.path === item.to : route.path.startsWith(item.to))

// счётчик в меню обновляется при каждом переходе по разделам
watch([() => isStaff(user.value), () => route.fullPath], async ([staff]) => {
  if (!staff) return
  try {
    pending.value = (await adminApi('/stats')).pending
  } catch (e) { /* не критично */ }
}, { immediate: true })
</script>

<template>
  <div class="bg-[#f4f6fb] min-h-[calc(100vh-56px)] pb-[28px]">
    <!-- Поиск -->
    <section class="bg-gradient-to-br from-[#0b1f4d] via-[#0a3a9e] to-[#0468ff] px-[16px] pt-[20px] pb-[26px] rounded-b-[24px]">
      <h1 class="m-0 text-white text-[23px] font-bold leading-tight">Найдите жильё<br />в Таджикистане</h1>
      <p class="text-white/75 text-[14px] mt-[6px] mb-[14px]">Квартиры, дома и коммерческая недвижимость</p>
      <form @submit.prevent="search" class="flex items-center gap-[8px] h-[50px] pl-[14px] pr-[6px] rounded-[14px] bg-white shadow-lg">
        <font-awesome-icon :icon="['fas', 'magnifying-glass']" class="text-[#94a3b8]" />
        <input v-model="q" type="search" enterkeyhint="search" placeholder="Район, улица или ключевое слово" data-test="mobile-search"
          class="flex-1 min-w-0 h-full bg-transparent outline-none text-[15px] text-[#152242] placeholder:text-[#94a3b8]" />
        <button type="submit" class="h-[38px] px-[14px] rounded-[10px] bg-[#0468ff] text-white text-[14px] font-bold">Найти</button>
      </form>
    </section>

    <!-- Разделы -->
    <section class="px-[16px] -mt-[2px] pt-[18px]">
      <div class="grid grid-cols-3 gap-[10px]">
        <NuxtLink v-for="c of CATEGORY_LINKS" :key="c.label" :to="categoryRoute(c)" :data-test="`cat-${c.label}`"
          class="flex flex-col items-center justify-center gap-[8px] h-[88px] rounded-[16px] bg-white border border-[#e8edf3] no-underline active:scale-[0.97] transition-transform">
          <span class="w-[38px] h-[38px] rounded-[12px] bg-[#eef4ff] text-[#0468ff] grid place-items-center text-[16px]">
            <font-awesome-icon :icon="['fas', c.icon]" />
          </span>
          <span class="text-[13px] font-semibold text-[#152242] text-center leading-tight px-[4px]">{{ c.label }}</span>
        </NuxtLink>
      </div>

      <button type="button" @click="postAnnoun" data-test="mobile-post"
        class="mt-[12px] w-full h-[50px] rounded-[14px] bg-[#0468ff] text-white text-[15px] font-bold flex items-center justify-center gap-[8px] active:bg-[#0353cc]">
        <font-awesome-icon :icon="['fas', 'plus']" /> Разместить объявление
      </button>
      <NuxtLink to="/yandex-map" class="mt-[10px] w-full h-[46px] rounded-[14px] bg-white border border-[#e8edf3] text-[#152242] text-[14.5px] font-semibold flex items-center justify-center gap-[8px] no-underline">
        <font-awesome-icon :icon="['fas', 'map-location-dot']" class="text-[#0468ff]" /> Объявления на карте
      </NuxtLink>
    </section>

    <!-- Свежие объявления -->
    <section class="mt-[24px]">
      <div class="flex items-center justify-between px-[16px] mb-[10px]">
        <h2 class="m-0 text-[18px] font-bold text-[#152242]">Новые объявления</h2>
        <NuxtLink to="/list-announ" class="text-[14px] font-semibold text-[#0468ff] no-underline">Все →</NuxtLink>
      </div>
      <div v-if="loading" class="flex gap-[10px] overflow-hidden px-[16px]">
        <div v-for="n in 2" :key="n" class="shrink-0 w-[240px] h-[230px] rounded-[16px] bg-[#e2e8f0] animate-pulse"></div>
      </div>
      <p v-else-if="!latest.length" class="px-[16px] text-[14px] text-[#64748b] m-0">Пока нет объявлений — разместите первое!</p>
      <div v-else class="flex gap-[10px] overflow-x-auto px-[16px] pb-[4px] snap-x snap-mandatory" data-test="mobile-latest">
        <NuxtLink v-for="a of latest" :key="a.id" :to="`/show_announ-${a.id}`"
          class="snap-start shrink-0 w-[240px] rounded-[16px] bg-white border overflow-hidden no-underline"
          :class="a.promo_active === 'vip' ? 'promo-vip border-[#f5c451]' : 'border-[#e8edf3]'">
          <div class="relative h-[140px] bg-[#e2e8f0]">
            <div v-if="a.promo_active" class="absolute left-[8px] top-[8px]">
              <span v-if="a.promo_active === 'vip'" class="promo-badge-vip px-[7px] py-[2px] rounded-[8px] text-[11.5px] font-extrabold text-[#5b3a00] shadow-sm"><font-awesome-icon :icon="['fas', 'crown']" class="text-[9px] mr-[3px]" />VIP</span>
              <span v-else-if="a.promo_active === 'top'" class="px-[7px] py-[2px] rounded-[8px] bg-[#0468ff] text-[11.5px] font-extrabold text-white shadow-sm"><font-awesome-icon :icon="['fas', 'arrow-up']" class="text-[9px] mr-[3px]" />ТОП</span>
            </div>
            <img v-if="a.pictures?.[0]" :src="`${apiBase()}/api/image/small/${a.pictures[0].pictures}`" class="w-full h-full object-cover" alt="" loading="lazy" />
          </div>
          <div class="p-[10px]">
            <div class="text-[16px] font-bold text-[#152242]">{{ formatNumber(a.price) }} с.<span class="text-[12px] font-normal text-[#64748b]">{{ priceSuffix(a) }}</span></div>
            <div class="text-[13px] text-[#334155] line-clamp-2 leading-[1.3] mt-[2px] min-h-[34px]">{{ a.title || a.type_object }}</div>
            <div v-if="a.address" class="text-[12px] text-[#94a3b8] truncate mt-[4px]"><font-awesome-icon :icon="['fas', 'location-dot']" /> {{ a.address }}</div>
          </div>
        </NuxtLink>
      </div>
    </section>

    <!-- Быстрые ссылки -->
    <section class="mt-[24px] px-[16px]">
      <h2 class="m-0 mb-[10px] text-[18px] font-bold text-[#152242]">Быстрые ссылки</h2>
      <div class="rounded-[16px] bg-white border border-[#e8edf3] overflow-hidden">
        <div v-for="(g, gi) of QUICK" :key="g.title" :class="gi ? 'border-t border-[#eef2f7]' : ''">
          <button type="button" @click="openGroup = openGroup === gi ? null : gi"
            class="w-full flex items-center justify-between px-[14px] h-[52px] text-left text-[15px] font-semibold text-[#152242]">
            {{ g.title }}
            <font-awesome-icon :icon="['fas', 'chevron-down']" class="text-[12px] text-[#94a3b8] transition-transform" :class="{ 'rotate-180': openGroup === gi }" />
          </button>
          <div v-if="openGroup === gi" class="pb-[8px]">
            <NuxtLink v-for="l of g.links" :key="l.label" :to="{ path: '/list-announ', query: l.query }"
              class="flex items-center justify-between px-[14px] py-[10px] text-[14.5px] text-[#334155] no-underline active:bg-[#f8fafc]">
              {{ l.label }} <font-awesome-icon :icon="['fas', 'chevron-right']" class="text-[11px] text-[#cbd5e1]" />
            </NuxtLink>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
defineProps({ pictures: { type: Array, default: () => [] } })
useHead({ title: 'Аренда и продажа недвижимости' })

const router = useRouter()
const { showMadoal } = useSwitch()
const { direction } = getData()
const { user } = getUserByServer(getToken())

const q = ref('')
const latest = ref([])
const loading = ref(true)
const openGroup = ref(0)

const QUICK = QUICK_LINKS

function search() {
  router.push({ path: '/list-announ', query: q.value.trim() ? { q: q.value.trim() } : {} })
}

function postAnnoun() {
  if (user.value?.id) return navigateTo('/announ')
  direction.value = '/announ'
  showMadoal.value = true
}

onMounted(async () => {
  try {
    const res = await fetch(`${apiBase()}/api/announs/search`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ perPage: 8 }),
    })
    latest.value = res.ok ? (await res.json()).data : []
  } catch (e) {
    latest.value = []
  } finally {
    loading.value = false
  }
})
</script>

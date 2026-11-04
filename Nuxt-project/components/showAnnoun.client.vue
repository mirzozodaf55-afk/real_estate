<template>
  <div class="min-h-screen bg-[#f4f6fb] pb-[96px] md:pb-[40px]">
    <div v-if="!show" class="max-w-[600px] mx-auto px-[16px] pt-[60px] text-center">
      <p class="text-[20px] font-bold text-[#152242]">{{ t('Объявление не найдено') }}</p>
      <p class="text-[14px] text-[#64748b] mt-[4px]">{{ error && error !== 'Объявление не найдено или снято с публикации' ? t(error) : t('Возможно, его сняли с публикации или уже продали.') }}</p>
      <NuxtLink to="/list-announ" class="inline-flex mt-[12px] h-[40px] px-[18px] rounded-[10px] bg-[#0468ff] text-white text-[14px] font-semibold items-center no-underline">{{ t('К объявлениям') }}</NuxtLink>
    </div>

    <div v-else class="max-w-[1100px] mx-auto px-[16px] pt-[16px]">
      <button @click="goBack" class="inline-flex items-center gap-[8px] text-[14px] font-semibold text-[#475569] hover:text-[#0468ff] mb-[12px]">
        <font-awesome-icon :icon="['fas', 'chevron-left']" class="text-[12px]" /> {{ t('Назад к объявлениям') }}
      </button>

      <!-- Владелец (или модератор) открыл объявление, которого пока нет в поиске -->
      <div v-if="show.is_public === false" class="mb-[14px] px-[14px] py-[10px] rounded-[12px] bg-[#fffbeb] border border-[#fde68a] text-[13.5px] text-[#92400e]" data-test="not-public">
        <font-awesome-icon :icon="['far', 'eye-slash']" class="mr-[6px]" />
        {{ t(hiddenReason) }} {{ t('— так объявление видите только вы и модераторы.') }}
      </div>

      <!-- Заголовок -->
      <div class="flex flex-wrap items-start justify-between gap-[12px] mb-[14px]">
        <div class="min-w-0">
          <div class="flex flex-wrap items-center gap-[8px] mb-[6px]">
            <span class="px-[10px] py-[3px] rounded-[8px] bg-[#eef4ff] text-[#0468ff] text-[12px] font-semibold">{{ t(dealLabel) }}</span>
            <span v-if="show.type_object" class="px-[10px] py-[3px] rounded-[8px] bg-white border border-[#e2e8f0] text-[#475569] text-[12px] font-semibold">{{ t(show.type_object) }}</span>
          </div>
          <h1 class="text-[22px] md:text-[28px] font-bold text-[#152242] leading-tight m-0 break-words">{{ title }}</h1>
          <div class="flex flex-wrap items-center gap-x-[14px] gap-y-[4px] mt-[8px] text-[14px] text-[#64748b]">
            <span v-if="address" class="flex items-center gap-[6px] min-w-0">
              <font-awesome-icon :icon="['fas', 'location-dot']" class="text-[#0468ff] text-[12px]" />
              <span class="break-words">{{ address }}</span>
            </span>
            <span v-if="show.created_at" class="flex items-center gap-[6px]">
              <font-awesome-icon :icon="['far', 'clock']" class="text-[12px]" /> {{ t('Размещено') }} {{ timeAgo(show.created_at) }}
            </span>
          </div>
        </div>
        <div class="flex items-center gap-[8px]">
          <button @click="toggleLike" :aria-label="liked ? t('Убрать из избранного') : t('В избранное')"
            class="h-[40px] px-[14px] rounded-[10px] bg-white border border-[#e2e8f0] text-[14px] font-semibold flex items-center gap-[8px] hover:border-[#ef4444]"
            :class="liked ? 'text-[#ef4444]' : 'text-[#152242]'">
            <font-awesome-icon :icon="[liked ? 'fas' : 'far', 'heart']" />
            <span class="hidden sm:inline">{{ liked ? t('В избранном') : t('В избранное') }}</span>
          </button>
          <button @click="share"
            class="h-[40px] px-[14px] rounded-[10px] bg-white border border-[#e2e8f0] text-[14px] font-semibold text-[#152242] flex items-center gap-[8px] hover:border-[#0468ff]">
            <font-awesome-icon :icon="['fas', copied ? 'check' : 'share-nodes']" :class="copied ? 'text-[#059669]' : ''" />
            <span class="hidden sm:inline">{{ copied ? t('Ссылка скопирована') : t('Поделиться') }}</span>
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_340px] gap-[20px] items-start">
        <!-- Основная колонка -->
        <div class="flex flex-col gap-[16px] min-w-0">

          <!-- Галерея -->
          <section class="bg-white rounded-[16px] border border-[#e8edf3] overflow-hidden">
            <div class="relative bg-[#0f172a] aspect-[4/3] md:aspect-[16/10] group">
              <!-- лента фото: сдвигается плавно, свайп на телефоне -->
              <div v-if="photos.length" class="h-full overflow-hidden" @touchstart.passive="swipeStart" @touchend="swipeEnd">
                <div class="flex h-full transition-transform duration-[450ms] ease-[cubic-bezier(.22,.61,.36,1)]" :style="`transform: translateX(-${current * 100}%)`">
                  <div v-for="(p, idx) of photos" :key="p.id || idx" class="w-full h-full shrink-0">
                    <img v-if="warm.has(idx)" :src="photoUrl(p, 'large')" :style="`transform: rotate(${p.rotation || 0}deg)`" draggable="false"
                      @click="lightbox = true" class="w-full h-full object-contain cursor-zoom-in" :alt="idx === current ? title : ''" />
                  </div>
                </div>
              </div>
              <div v-else class="w-full h-full grid place-items-center text-[#64748b] text-[40px]">
                <font-awesome-icon :icon="['far', 'image']" />
              </div>
              <template v-if="photos.length > 1">
                <button @click="shift(-1)" :aria-label="t('Предыдущее фото')"
                  class="absolute left-[12px] top-1/2 -translate-y-1/2 w-[40px] h-[40px] rounded-full bg-white/90 text-[#152242] grid place-items-center shadow hover:bg-white">
                  <font-awesome-icon :icon="['fas', 'chevron-left']" />
                </button>
                <button @click="shift(1)" :aria-label="t('Следующее фото')"
                  class="absolute right-[12px] top-1/2 -translate-y-1/2 w-[40px] h-[40px] rounded-full bg-white/90 text-[#152242] grid place-items-center shadow hover:bg-white">
                  <font-awesome-icon :icon="['fas', 'chevron-right']" />
                </button>
              </template>
              <span v-if="photos.length" class="absolute bottom-[12px] right-[12px] px-[10px] py-[3px] rounded-[10px] bg-black/55 text-white text-[12px] flex items-center gap-[6px]">
                <font-awesome-icon :icon="['fas', 'camera']" class="text-[11px]" /> {{ current + 1 }} / {{ photos.length }}
              </span>
              <button v-if="photos.length" @click="lightbox = true" :aria-label="t('Открыть на весь экран')"
                class="absolute bottom-[12px] left-[12px] w-[34px] h-[34px] rounded-[10px] bg-black/55 text-white grid place-items-center">
                <font-awesome-icon :icon="['fas', 'expand']" class="text-[13px]" />
              </button>
            </div>
            <div v-if="photos.length > 1" class="flex gap-[8px] p-[10px] overflow-x-auto">
              <button v-for="(p, idx) of photos" :key="p.id || idx" @click="current = idx"
                class="shrink-0 w-[84px] h-[60px] rounded-[8px] overflow-hidden border-2 transition-colors"
                :class="idx === current ? 'border-[#0468ff]' : 'border-transparent opacity-70 hover:opacity-100'">
                <img :src="photoUrl(p, 'xsmall')" :style="`transform: rotate(${p.rotation || 0}deg)`" class="w-full h-full object-cover" alt="" loading="lazy" />
              </button>
            </div>
          </section>

          <!-- Карточка цены/контакта на мобильном идёт сразу после фото -->
          <div class="lg:hidden">
            <OwnerCard />
          </div>

          <!-- Основные параметры -->
          <section v-if="highlights.length" class="bg-white rounded-[16px] border border-[#e8edf3] p-[16px] md:p-[20px]">
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-[10px]">
              <div v-for="h of highlights" :key="h.label" class="rounded-[12px] bg-[#f8fafc] border border-[#eef2f7] p-[12px]">
                <div class="text-[12px] text-[#64748b] flex items-center gap-[6px]">
                  <font-awesome-icon :icon="h.icon" class="text-[#0468ff] text-[11px]" /> {{ t(h.label) }}
                </div>
                <div class="text-[17px] font-bold text-[#152242] mt-[4px]">{{ t(h.value) }}</div>
              </div>
            </div>
          </section>

          <!-- Описание -->
          <section v-if="show.description" class="bg-white rounded-[16px] border border-[#e8edf3] p-[16px] md:p-[20px]">
            <h2 class="text-[18px] font-bold text-[#152242] m-0 mb-[10px]">{{ t('Описание') }}</h2>
            <p class="text-[15px] leading-[1.6] text-[#334155] m-0 whitespace-pre-line break-words"
              :class="!descriptionOpen && longDescription ? 'line-clamp-6' : ''">{{ show.description }}</p>
            <button v-if="longDescription" @click="descriptionOpen = !descriptionOpen" class="mt-[8px] text-[14px] font-semibold text-[#0468ff]">
              {{ descriptionOpen ? t('Свернуть') : t('Показать полностью') }}
            </button>
          </section>

          <!-- Характеристики -->
          <section v-if="details.length" class="bg-white rounded-[16px] border border-[#e8edf3] p-[16px] md:p-[20px]">
            <h2 class="text-[18px] font-bold text-[#152242] m-0 mb-[10px]">{{ t('Характеристики') }}</h2>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-[32px] m-0">
              <div v-for="d of details" :key="d.label" class="flex justify-between gap-[12px] py-[10px] border-b border-dashed border-[#e2e8f0]">
                <dt class="text-[14px] text-[#64748b]">{{ t(d.label) }}</dt>
                <dd class="text-[14px] text-[#152242] font-semibold text-right m-0">{{ t(d.value) }}</dd>
              </div>
            </dl>
          </section>

          <!-- Условия аренды -->
          <section v-if="rentTerms.length" class="bg-white rounded-[16px] border border-[#e8edf3] p-[16px] md:p-[20px]">
            <h2 class="text-[18px] font-bold text-[#152242] m-0 mb-[12px]">{{ t('Условия аренды') }}</h2>
            <div class="flex flex-wrap gap-[8px]">
              <span v-for="term of rentTerms" :key="term.label"
                class="flex items-center gap-[8px] px-[12px] py-[8px] rounded-[10px] text-[14px] font-semibold"
                :class="term.ok === false ? 'bg-[#fef2f2] text-[#b91c1c]' : 'bg-[#ecfdf5] text-[#047857]'">
                <font-awesome-icon :icon="['fas', term.icon]" class="text-[12px]" /> {{ t(term.label) }}
              </span>
            </div>
          </section>

          <!-- Видео -->
          <section v-if="videoEmbed || show.link_video" class="bg-white rounded-[16px] border border-[#e8edf3] p-[16px] md:p-[20px]">
            <h2 class="text-[18px] font-bold text-[#152242] m-0 mb-[12px]">{{ t('Видео') }}</h2>
            <div v-if="videoEmbed" class="relative w-full aspect-video rounded-[12px] overflow-hidden bg-black">
              <iframe :src="videoEmbed" class="absolute inset-0 w-full h-full" :title="t('Видео объявления')" frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>
            <a v-else :href="show.link_video" target="_blank" rel="noopener" class="text-[#0468ff] font-semibold break-all">{{ show.link_video }}</a>
          </section>

          <!-- Карта -->
          <section v-if="hasCoords" class="bg-white rounded-[16px] border border-[#e8edf3] p-[16px] md:p-[20px]">
            <h2 class="text-[18px] font-bold text-[#152242] m-0 mb-[12px]">{{ t('На карте') }}</h2>
            <div ref="mapEl" class="w-full h-[300px] md:h-[360px] rounded-[12px] overflow-hidden bg-[#e2e8f0]"></div>
          </section>
        </div>

        <!-- Боковая колонка (десктоп) -->
        <aside class="hidden lg:block sticky top-[16px]">
          <OwnerCard />
        </aside>
      </div>
    </div>

    <!-- Нижняя панель на мобильном -->
    <div v-if="show && !isMine" class="lg:hidden fixed bottom-0 left-0 right-0 z-[50] bg-white border-t border-[#e8edf3] px-[16px] py-[10px] flex items-center gap-[10px]">
      <div class="min-w-0 flex-1">
        <div class="text-[18px] font-bold text-[#152242] truncate">{{ formatNumber(show.price) }} {{ t('сомони') }}</div>
        <div v-if="priceSuffix(show)" class="text-[12px] text-[#64748b]">{{ t(show.rental_type === 'Посуточно' ? 'за сутки' : 'за месяц') }}</div>
      </div>
      <a v-if="phoneShown && phone" :href="`tel:+992${phone}`" class="h-[42px] w-[42px] rounded-[10px] bg-[#0468ff] text-white grid place-items-center" :aria-label="t('Позвонить')">
        <font-awesome-icon :icon="['fas', 'phone']" />
      </a>
      <button v-else @click="revealPhone" class="h-[42px] w-[42px] rounded-[10px] bg-[#0468ff] text-white grid place-items-center" :aria-label="t('Показать телефон')">
        <font-awesome-icon :icon="['fas', 'phone']" />
      </button>
      <button @click="goToChat" class="h-[42px] px-[16px] rounded-[10px] bg-[#eef4ff] text-[#0468ff] text-[14px] font-semibold flex items-center gap-[8px]">
        <font-awesome-icon :icon="['far', 'comment']" /> {{ t('Написать') }}
      </button>
    </div>

    <!-- Полноэкранный просмотр фото -->
    <div v-if="lightbox && photos.length" class="fixed inset-0 z-[400] bg-black flex flex-col" @click.self="lightbox = false">
      <div class="flex items-center justify-between px-[16px] h-[56px] text-white">
        <span class="text-[14px]">{{ current + 1 }} / {{ photos.length }}</span>
        <button @click="lightbox = false" class="w-[40px] h-[40px] rounded-full hover:bg-white/10 grid place-items-center" :aria-label="t('Закрыть')">
          <font-awesome-icon :icon="['fas', 'xmark']" class="text-[20px]" />
        </button>
      </div>
      <div class="relative flex-1 min-h-0 flex items-center justify-center px-[56px]" @click.self="lightbox = false">
        <Transition name="photo-fade" mode="out-in">
          <img :key="current" :src="photoUrl(photos[current], 'large')" :style="`transform: rotate(${photos[current].rotation || 0}deg)`" class="max-w-full max-h-full object-contain" :alt="title"
            @touchstart.passive="swipeStart" @touchend="swipeEnd" />
        </Transition>
        <template v-if="photos.length > 1">
          <button @click="shift(-1)" class="absolute left-[8px] w-[44px] h-[44px] rounded-full bg-white/15 hover:bg-white/25 text-white grid place-items-center" :aria-label="t('Предыдущее фото')">
            <font-awesome-icon :icon="['fas', 'chevron-left']" />
          </button>
          <button @click="shift(1)" class="absolute right-[8px] w-[44px] h-[44px] rounded-full bg-white/15 hover:bg-white/25 text-white grid place-items-center" :aria-label="t('Следующее фото')">
            <font-awesome-icon :icon="['fas', 'chevron-right']" />
          </button>
        </template>
      </div>
      <div v-if="photos.length > 1" class="flex gap-[8px] p-[12px] overflow-x-auto justify-center">
        <button v-for="(p, idx) of photos" :key="p.id || idx" @click="current = idx"
          class="shrink-0 w-[64px] h-[46px] rounded-[6px] overflow-hidden border-2" :class="idx === current ? 'border-white' : 'border-transparent opacity-60'">
          <img :src="photoUrl(p, 'xsmall')" class="w-full h-full object-cover" alt="" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
const route = useRoute()
const router = useRouter()
const { showMadoal } = useSwitch()
const { direction } = getData()
const { user } = getUserByServer(getToken())

// Объявление загружает страница pages/show_announ-[object].vue (по id с сервера) и передаёт сюда
const props = defineProps({
  announ: Object,
  error: String,
})
const show = props.announ || null

useHead({ title: () => show?.title || t('Объявление') })

const hiddenReason = computed(() => {
  if (!show) return ''
  if (show.deleted_at) return 'Объявление снято с публикации'
  if (show.sold_at) return show.deal_type === 'Аренда' ? 'Отмечено как сданное' : 'Отмечено как проданное'
  if (show.moderation_status === 'pending') return 'Объявление на проверке у модератора'
  if (show.moderation_status === 'rejected') return 'Объявление отклонено модератором'
  return 'Объявление не показывается в поиске'
})

const photos = computed(() => show?.image || show?.pictures || [])
const current = ref(0)
const lightbox = ref(false)
const descriptionOpen = ref(false)
const liked = ref(!!show?.is_liked)
const copied = ref(false)
const phoneShown = ref(false)
const address = ref(show?.address || '')
const mapEl = ref(null)

const owner = computed(() => show?.owner || null)
const phone = computed(() => owner.value?.phone_number || '')
const isMine = computed(() => !!user.value?.id && owner.value?.id === user.value.id)
const hasCoords = computed(() => !!(show?.coord_x && show?.coord_y))
const longDescription = computed(() => (show?.description || '').length > 420)

const dealLabel = computed(() => {
  if (!show) return ''
  if (show.deal_type === 'Аренда') return show.rental_type === 'Посуточно' ? 'Посуточная аренда' : 'Аренда'
  return show.deal_type || 'Объявление'
})

const title = computed(() => {
  if (!show) return ''
  if (show.title) return show.title
  const parts = []
  if (show.quantity_room) parts.push(t('{n}-комн.', { n: show.quantity_room }))
  parts.push(t(show.type_object || 'Объект'))
  const area = show.total_area || show.house_area
  if (area) parts.push(`${area} ${t('м²')}`)
  return parts.join(' ')
})

const pricePerMeter = computed(() => {
  if (show?.deal_type !== 'Продажа' || !show.price || !show.total_area) return ''
  return formatNumber(Math.round(show.price / show.total_area))
})

const filled = (v) => v !== null && v !== undefined && v !== '' && v !== 0 && v !== '0'

// Плитки с главными цифрами
const highlights = computed(() => {
  if (!show) return []
  const s = show
  const list = []
  if (filled(s.quantity_room)) list.push({ label: 'Комнат', value: s.quantity_room, icon: ['fas', 'door-open'] })
  if (filled(s.quantity_bedrooms)) list.push({ label: 'Спален', value: s.quantity_bedrooms, icon: ['fas', 'bed'] })
  if (filled(s.total_area)) list.push({ label: 'Общая площадь', value: `${s.total_area} ${t('м²')}`, icon: ['fas', 'ruler-combined'] })
  if (filled(s.living_area)) list.push({ label: 'Жилая', value: `${s.living_area} ${t('м²')}`, icon: ['fas', 'couch'] })
  if (filled(s.kitchen_area)) list.push({ label: 'Кухня', value: `${s.kitchen_area} ${t('м²')}`, icon: ['fas', 'kitchen-set'] })
  if (filled(s.house_area)) list.push({ label: 'Площадь дома', value: `${s.house_area} ${t('м²')}`, icon: ['fas', 'house'] })
  if (filled(s.building_area)) list.push({ label: 'Площадь здания', value: `${s.building_area} ${t('м²')}`, icon: ['fas', 'building'] })
  if (filled(s.land_area)) list.push({ label: 'Участок', value: `${s.land_area} сот.`, icon: ['fas', 'tree'] })
  if (filled(s.floor) && filled(s.floor_in_house)) list.push({ label: 'Этаж', value: t('{a} из {b}', { a: s.floor, b: s.floor_in_house }), icon: ['fas', 'stairs'] })
  else if (filled(s.floor)) list.push({ label: 'Этаж', value: s.floor, icon: ['fas', 'stairs'] })
  else if (filled(s.floor_in_house)) list.push({ label: 'Этажей', value: s.floor_in_house, icon: ['fas', 'stairs'] })
  if (filled(s.year_of_construction)) list.push({ label: 'Год постройки', value: s.year_of_construction, icon: ['far', 'calendar'] })
  if (filled(s.numberSeats)) list.push({ label: 'Рабочих мест', value: s.numberSeats, icon: ['fas', 'users'] })
  return list
})

// Остальные характеристики — только заполненные
const details = computed(() => {
  if (!show) return []
  const s = show
  const ceiling = filled(s.CeilingHeight) ? (Number(s.CeilingHeight) < 10 ? `${s.CeilingHeight} м` : `${s.CeilingHeight} см`) : null
  return [
    ['Тип недвижимости', s.type_real_estate],
    ['Тип объекта', s.type_object],
    ['Тип дома', s.house_type],
    ['Ремонт', s.repair],
    ['Балкон', s.Balcony],
    ['Лифт', s.elevator],
    ['Парковка', s.parking],
    ['Мебель', s.furniture],
    ['Высота потолков', ceiling],
    ['Планировка', s.layout],
    ['Состояние', s.state],
    ['Состояние дома', s.condition_of_the_home],
    ['Электричество', s.electricity],
    ['Категория земли', s.Land_category],
  ]
    .filter(([, value]) => filled(value))
    .map(([label, value]) => ({ label, value: String(value).trim() }))
})

const rentTerms = computed(() => {
  if (show?.deal_type !== 'Аренда') return []
  const list = []
  if (filled(show.rental_type)) list.push({ label: show.rental_type, icon: 'calendar-day' })
  if (filled(show.rental_period)) list.push({ label: t('Срок: {x}', { x: t(show.rental_period) }), icon: 'clock' })
  if (show.allowKids) {
    const ok = show.allowKids !== 'нет'
    list.push({ label: ok ? 'Можно с детьми' : 'Без детей', icon: ok ? 'child' : 'ban', ok })
  }
  if (show.allowAnimal) {
    const ok = show.allowAnimal !== 'нет'
    list.push({ label: ok ? 'Можно с животными' : 'Без животных', icon: ok ? 'paw' : 'ban', ok })
  }
  return list
})

// Ссылка на YouTube → embed
const videoEmbed = computed(() => {
  const url = (show?.link_video || '').trim()
  if (!url) return ''
  const m = url.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?(?:.*&)?v=|embed\/|shorts\/|live\/))([\w-]{11})/)
  return m ? `https://www.youtube.com/embed/${m[1]}` : ''
})

function photoUrl(p, size) {
  return `${apiBase()}/api/image/${size}/${p.pictures}`
}

function shift(step) {
  const n = photos.value.length
  current.value = (current.value + step + n) % n
}

// Какие фото уже можно грузить: текущее и соседние — чтобы при сдвиге ленты не выезжал пустой кадр
const warm = ref(new Set())
watch([current, () => photos.value.length], () => {
  const n = photos.value.length
  if (!n) return
  const next = new Set(warm.value)
  for (const d of [-1, 0, 1]) next.add((current.value + d + n) % n)
  warm.value = next
}, { immediate: true })

// свайп по фото на телефоне: сдвиг больше 40px — следующее/предыдущее фото, а не открытие на весь экран
let touchX = null
function swipeStart(e) {
  touchX = e.touches[0].clientX
}
function swipeEnd(e) {
  if (touchX === null) return
  const dx = e.changedTouches[0].clientX - touchX
  touchX = null
  if (Math.abs(dx) < 40 || photos.value.length < 2) return
  e.preventDefault()
  shift(dx < 0 ? 1 : -1)
}

function onKey(e) {
  if (!lightbox.value) return
  if (e.key === 'Escape') lightbox.value = false
  if (e.key === 'ArrowRight') shift(1)
  if (e.key === 'ArrowLeft') shift(-1)
}

function goBack() {
  if (window.history.length > 1) router.back()
  else router.push('/list-announ')
}

function requireLogin() {
  direction.value = route.fullPath
  showMadoal.value = true
}

function revealPhone() {
  if (!user.value?.id) return requireLogin()
  phoneShown.value = true
}

function goToChat() {
  if (!user.value?.id) return requireLogin()
  if (!owner.value || isMine.value) return
  router.push(`/chat-${user.value.id}-${user.value.uniq_id}-${owner.value.id}-${owner.value.uniq_id}`)
}

async function toggleLike() {
  if (!user.value?.id) return requireLogin()
  const prev = liked.value
  liked.value = !prev
  try {
    const res = await fetch(`${apiBase()}/api/likes`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json', Authorization: 'Bearer ' + getToken() },
      body: JSON.stringify({ announ_id: show.id }),
    })
    if (!res.ok) throw new Error()
    liked.value = (await res.json()).liked
  } catch (e) {
    liked.value = prev
  }
}

async function share() {
  try {
    if (navigator.share) {
      await navigator.share({ title: title.value, url: location.href })
      return
    }
    await navigator.clipboard.writeText(location.href)
    copied.value = true
    setTimeout(() => { copied.value = false }, 2000)
  } catch (e) { /* пользователь закрыл окно «поделиться» */ }
}

// Адрес и карта — через Яндекс.Карты (скрипт подключён в app.vue)
function initMap() {
  if (!hasCoords.value || !window.ymaps) return
  window.ymaps.ready(() => {
    const coords = [Number(show.coord_x), Number(show.coord_y)]
    if (mapEl.value) {
      const map = new window.ymaps.Map(mapEl.value, { center: coords, zoom: 15, controls: ['zoomControl', 'fullscreenControl'] })
      map.behaviors.disable('scrollZoom')
      map.geoObjects.add(new window.ymaps.Placemark(coords, { hintContent: title.value }, { preset: 'islands#blueHomeIcon' }))
    }
    if (!address.value) {
      window.ymaps.geocode(coords).then((res) => {
        const obj = res.geoObjects.get(0)
        if (obj) address.value = obj.getAddressLine()
      }).catch(() => {})
    }
  })
}

// Карточка с ценой и владельцем: на десктопе в боковой колонке, на мобильном — после фото
const OwnerCard = defineComponent({
  setup() {
    return () => h('div', { class: 'bg-white rounded-[16px] border border-[#e8edf3] p-[18px] flex flex-col gap-[14px]' }, [
      h('div', [
        h('div', { class: 'text-[28px] font-bold text-[#152242] leading-tight' }, [
          `${formatNumber(show.price)} `,
          h('span', { class: 'text-[16px] font-semibold' }, t('сомони')),
          h('span', { class: 'text-[15px] font-normal text-[#64748b]' }, priceSuffix(show)),
        ]),
        pricePerMeter.value ? h('div', { class: 'text-[14px] text-[#64748b] mt-[4px]' }, t('{n} сомони за м²', { n: pricePerMeter.value })) : null,
      ]),
      owner.value ? h('div', { class: 'flex items-center gap-[12px] pt-[14px] border-t border-[#f1f5f9]' }, [
        h('img', { src: `${apiBase()}/api/avatar/${owner.value.avatar || 'avatar.jpg'}`, class: 'w-[52px] h-[52px] rounded-full object-cover bg-[#e2e8f0] shrink-0', alt: '' }),
        h('div', { class: 'min-w-0' }, [
          h('div', { class: 'text-[16px] font-semibold text-[#152242] truncate' }, [owner.value.name, owner.value.last_name].filter(Boolean).join(' ') || t('Владелец')),
          h('div', { class: 'text-[13px] text-[#64748b]' }, t(isMine.value ? 'Это ваше объявление' : 'Собственник')),
        ]),
      ]) : null,
      isMine.value ? null : h('div', { class: 'flex flex-col gap-[8px]' }, [
        phoneShown.value && phone.value
          ? h('a', { href: `tel:+992${phone.value}`, class: 'h-[46px] rounded-[12px] bg-[#0468ff] text-white text-[16px] font-semibold flex items-center justify-center gap-[8px] no-underline' }, `+992 ${phone.value}`)
          : h('button', { onClick: revealPhone, class: 'h-[46px] rounded-[12px] bg-[#0468ff] hover:bg-[#0353cc] text-white text-[16px] font-semibold' },
            t(user.value?.id && !phone.value ? 'Телефон не указан' : 'Показать телефон')),
        h('button', { onClick: goToChat, class: 'h-[46px] rounded-[12px] bg-[#eef4ff] hover:bg-[#dbe8ff] text-[#0468ff] text-[16px] font-semibold' }, t('Написать сообщение')),
        owner.value?.email ? h('a', { href: `mailto:${owner.value.email}`, class: 'text-center text-[14px] text-[#475569] hover:text-[#0468ff] break-all no-underline' }, owner.value.email) : null,
      ]),
    ])
  },
})

onMounted(() => {
  window.addEventListener('keydown', onKey)
  initMap()
})
onUnmounted(() => window.removeEventListener('keydown', onKey))
</script>

<style scoped>
.photo-fade-enter-active,
.photo-fade-leave-active {
  transition: opacity .18s ease;
}
.photo-fade-enter-from,
.photo-fade-leave-to {
  opacity: 0;
}
</style>

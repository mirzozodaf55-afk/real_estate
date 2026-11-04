<template>
  <div class="map-page flex bg-[#f4f6fb]">
    <!-- Список слева (на телефоне скрыт — там вся ширина под карту) -->
    <aside class="hidden lg:flex flex-col w-[380px] shrink-0 bg-white border-r border-[#e8edf3]">
      <div class="px-[16px] pt-[14px] pb-[12px] border-b border-[#eef2f7]">
        <NuxtLink :to="{ path: '/list-announ', query: route.query }" class="inline-flex items-center gap-[6px] text-[13.5px] font-semibold text-[#0468ff] no-underline hover:underline">
          <font-awesome-icon :icon="['fas', 'list']" /> {{ t('Списком') }}
        </NuxtLink>
        <h1 class="text-[20px] font-bold text-[#152242] mt-[6px] mb-0">
          {{ loading ? t('Загружаем…') : t('{n} {w} на карте', { n: onMap.length, w: tn(onMap.length, ['объявление', 'объявления', 'объявлений']) }) }}
        </h1>
        <p v-if="filterLabel" class="text-[13px] text-[#64748b] mt-[2px] mb-0 truncate">{{ t(filterLabel) }}</p>
        <p v-if="!loading && withoutCoords" class="text-[12px] text-[#94a3b8] mt-[4px] mb-0">{{ t('Ещё') }} {{ withoutCoords }} {{ t('без адреса на карте — они есть в списке') }}</p>
      </div>

      <div class="flex-1 overflow-y-auto p-[10px] flex flex-col gap-[8px]">
        <template v-if="loading">
          <div v-for="n in 4" :key="n" class="h-[96px] rounded-[12px] bg-[#f1f5f9] animate-pulse"></div>
        </template>
        <p v-else-if="error" class="text-center text-[14px] text-[#b91c1c] p-[20px]">{{ t(error) }}</p>
        <p v-else-if="!onMap.length" class="text-center text-[14px] text-[#64748b] p-[20px]">{{ t('По этим условиям нет объявлений с адресом на карте.') }}</p>
        <button v-for="a of onMap" :key="a.id" :ref="(el) => (cardEls[a.id] = el)"
          @mouseenter="setActive(a.id)" @mouseleave="setActive(null)" @click="openAnnoun(a)" :data-test="`map-card-${a.id}`"
          class="w-full text-left flex gap-[10px] p-[8px] rounded-[12px] border transition-all"
          :class="activeId === a.id ? 'border-[#0468ff] bg-[#f5f9ff] shadow-[0_4px_14px_rgba(4,104,255,0.15)]' : 'border-[#eef2f7] hover:border-[#cbd5e1]'">
          <div class="w-[110px] h-[80px] rounded-[9px] overflow-hidden bg-[#e2e8f0] shrink-0">
            <img v-if="a.pictures?.[0]" :src="photoUrl(a, 'small')" class="w-full h-full object-cover" alt="" loading="lazy" />
          </div>
          <div class="min-w-0 flex-1">
            <div class="text-[16px] font-bold text-[#152242] leading-tight">{{ formatNumber(a.price) }} {{ t('с.') }}<span class="text-[12px] font-normal text-[#64748b]">{{ priceSuffix(a) }}</span></div>
            <div class="text-[13px] font-semibold text-[#0468ff] line-clamp-2 leading-[1.3] mt-[2px]">{{ a.title || t(a.type_object) }}</div>
            <div class="text-[12px] text-[#64748b] mt-[2px] truncate">{{ facts(a) }}</div>
          </div>
        </button>
      </div>
    </aside>

    <!-- Карта -->
    <div class="relative flex-1 min-w-0">
      <div id="map-yandex" class="absolute inset-0"></div>

      <div v-if="!mapReady" class="absolute inset-0 grid place-items-center bg-[#eef2f7] text-[#64748b] text-[14px]">
        <span><font-awesome-icon :icon="['fas', 'spinner']" spin-pulse class="mr-[6px]" /> {{ t('Загружаем карту…') }}</span>
      </div>

      <!-- Телефон: сверху — назад и счётчик -->
      <div class="lg:hidden absolute top-[12px] left-[12px] right-[12px] flex items-center gap-[8px] pointer-events-none">
        <NuxtLink :to="{ path: '/list-announ', query: route.query }" :aria-label="t('Списком')"
          class="pointer-events-auto h-[40px] px-[14px] rounded-full bg-white shadow-md text-[14px] font-semibold text-[#152242] flex items-center gap-[6px] no-underline">
          <font-awesome-icon :icon="['fas', 'list']" /> {{ t('Списком') }}
        </NuxtLink>
        <span v-if="!loading" class="pointer-events-auto h-[40px] px-[14px] rounded-full bg-white/95 shadow-md text-[13.5px] text-[#334155] flex items-center">
          {{ onMap.length }} {{ t('на карте') }}
        </span>
      </div>

      <!-- Телефон: выбранное объявление снизу (наведения на тач-экране нет) -->
      <button v-if="selected" @click="openAnnoun(selected)" data-test="map-sheet"
        class="lg:hidden absolute left-[12px] right-[12px] bottom-[44px] flex gap-[10px] p-[10px] rounded-[16px] bg-white shadow-[0_10px_30px_rgba(15,23,42,0.25)] text-left">
        <div class="w-[96px] h-[76px] rounded-[10px] overflow-hidden bg-[#e2e8f0] shrink-0">
          <img v-if="selected.pictures?.[0]" :src="photoUrl(selected, 'small')" class="w-full h-full object-cover" alt="" />
        </div>
        <div class="min-w-0 flex-1">
          <div class="text-[16px] font-bold text-[#152242]">{{ formatNumber(selected.price) }} {{ t('с.') }}<span class="text-[12px] font-normal text-[#64748b]">{{ priceSuffix(selected) }}</span></div>
          <div class="text-[13px] font-semibold text-[#0468ff] line-clamp-2 leading-[1.3]">{{ selected.title || t(selected.type_object) }}</div>
          <div class="text-[12px] text-[#64748b] truncate">{{ facts(selected) }}</div>
        </div>
        <span class="self-center text-[#0468ff]"><font-awesome-icon :icon="['fas', 'chevron-right']" /></span>
      </button>
    </div>
  </div>
</template>

<script setup>
useHead({ title: () => t('Объявления на карте') })

const route = useRoute()
const router = useRouter()
const { showNavBar } = useSwitch()
showNavBar.value = true

const items = ref([])
const loading = ref(true)
const error = ref('')
const mapReady = ref(false)
const activeId = ref(null)
const selected = ref(null)
const cardEls = {}

// Границы Таджикистана: по ним центрируем карту, чтобы одна точка с ошибочным адресом не отдаляла всё до соседних стран
const TJ = { lat: [36.6, 41.1], lon: [67.3, 75.2] }
const hasCoords = (a) => a.coord_x && a.coord_y && !Number.isNaN(+a.coord_x) && !Number.isNaN(+a.coord_y)
const onMap = computed(() => items.value.filter(hasCoords))
const withoutCoords = computed(() => items.value.length - onMap.value.length)
const filterLabel = computed(() => (Object.keys(route.query).length ? describeFilters(route.query) : ''))

const photoUrl = (a, size) => `${apiBase()}/api/image/${size}/${a.pictures[0].pictures}`
function facts(a) {
  const area = a.total_area || a.house_area || a.building_area
  return [t(a.type_object), announFacts(a)].filter(Boolean).join(' · ')
}
// Коротко для ценника на карте: 650 000 → «650 тыс.», 1 250 000 → «1,3 млн»
function shortPrice(p) {
  p = Number(p) || 0
  const locale = useLang().locale.value
  if (p >= 1e6) return `${(p / 1e6).toLocaleString(locale, { maximumFractionDigits: 1 })} ${t('млн')}`
  if (p >= 1e4) return `${Math.round(p / 1e3)} ${t('тыс.')}`
  return p.toLocaleString(locale)
}

function openAnnoun(a) {
  router.push(`/show_announ-${a.id}`)
}

// --- Данные: тот же поиск, что и в списке, с теми же фильтрами из адреса
async function load() {
  loading.value = true
  error.value = ''
  try {
    const all = []
    let page = 1
    let last = 1
    do {
      const res = await fetch(`${apiBase()}/api/announs/search`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', ...(getToken() ? { Authorization: 'Bearer ' + getToken() } : {}) },
        body: JSON.stringify({ ...route.query, page, perPage: 50 }),
      })
      if (!res.ok) throw new Error()
      const json = await res.json()
      all.push(...json.data)
      last = json.last_page
      page++
    } while (page <= last && page <= 10) // не больше 500 точек — карте этого хватает
    items.value = all
  } catch (e) {
    error.value = 'Не удалось загрузить объявления'
  } finally {
    loading.value = false
  }
}

// --- Карта
let map = null
let clusterer = null
const placemarks = {}

function waitForYmaps() {
  return new Promise((resolve) => {
    const tick = () => (window.ymaps ? window.ymaps.ready(resolve) : setTimeout(tick, 200))
    tick()
  })
}

function initMap() {
  const ymaps = window.ymaps
  map = new ymaps.Map('map-yandex', { center: [38.56, 68.79], zoom: 11, controls: ['zoomControl', 'geolocationControl', 'fullscreenControl'] }, { suppressMapOpenBlock: true })

  // рядом стоящие объявления собираются в кружок с числом; клик по нему приближает
  clusterer = new ymaps.Clusterer({
    preset: 'islands#invertedBlueClusterIcons',
    groupByCoordinates: false,
    clusterDisableClickZoom: false,
    clusterOpenBalloonOnClick: false,
    gridSize: 64,
  })
  map.geoObjects.add(clusterer)
  mapReady.value = true
  drawPlacemarks()
  // карта меряет контейнер в момент создания — после того как вёрстка устоялась, пересчитываем (иначе полоса справа)
  setTimeout(fitMap, 300)
}

function fitMap() {
  map?.container.fitToViewport()
}

function drawPlacemarks() {
  if (!map) return
  const ymaps = window.ymaps
  clusterer.removeAll()
  Object.keys(placemarks).forEach((k) => delete placemarks[k])

  // Ценник-маркер; при наведении (на карточку слева или сам маркер) — активный стиль.
  // {{ }} в шаблонах Яндекса экранирует HTML, так что заголовок из объявления безопасен
  const PriceLayout = ymaps.templateLayoutFactory.createClass(
    '<div class="ym-price{% if properties.active %} ym-price--active{% endif %}">{{ properties.priceLabel }}</div>',
  )
  // Карточка при наведении на маркер
  const HintLayout = ymaps.templateLayoutFactory.createClass(
    '<div class="ym-hint">' +
      '{% if properties.photo %}<img class="ym-hint__img" src="{{ properties.photo }}" alt="">{% endif %}' +
      '<div class="ym-hint__body">' +
        '<div class="ym-hint__price">{{ properties.priceFull }}</div>' +
        '<div class="ym-hint__title">{{ properties.title }}</div>' +
        '<div class="ym-hint__facts">{{ properties.facts }}</div>' +
        '<div class="ym-hint__more">' + t('Нажмите, чтобы открыть →') + '</div>' +
      '</div>' +
    '</div>',
  )
  const touch = window.matchMedia('(hover: none)').matches

  for (const a of onMap.value) {
    const label = shortPrice(a.price)
    const pm = new ymaps.Placemark([+a.coord_x, +a.coord_y], {
      priceLabel: label,
      priceFull: `${formatNumber(a.price)} сомони${priceSuffix(a)}`,
      title: a.title || a.type_object || 'Объявление',
      facts: facts(a),
      photo: a.pictures?.[0] ? photoUrl(a, 'small') : '',
      hintContent: a.title || ' ',
      active: false,
    }, {
      iconLayout: PriceLayout,
      // область, по которой ловятся наведение и клик: ценник стоит над точкой и растёт с длиной цены
      iconShape: { type: 'Rectangle', coordinates: [[-(label.length * 4.5 + 14), -38], [label.length * 4.5 + 14, 0]] },
      hintLayout: touch ? undefined : HintLayout,
      openHintOnHover: !touch,
      hasBalloon: false,
    })
    pm.events.add('mouseenter', () => setActive(a.id, true))
    pm.events.add('mouseleave', () => setActive(null))
    pm.events.add('click', () => {
      // на телефоне первое нажатие показывает карточку снизу, переход — по нажатию на неё
      if (touch) selected.value = a
      else openAnnoun(a)
    })
    placemarks[a.id] = pm
    clusterer.add(pm)
  }

  // центрируем по точкам в Таджикистане; если таких нет — по всем
  const inside = onMap.value.filter((a) => +a.coord_x >= TJ.lat[0] && +a.coord_x <= TJ.lat[1] && +a.coord_y >= TJ.lon[0] && +a.coord_y <= TJ.lon[1])
  const points = (inside.length ? inside : onMap.value).map((a) => [+a.coord_x, +a.coord_y])
  if (points.length === 1) map.setCenter(points[0], 14)
  else if (points.length > 1) map.setBounds(ymaps.util.bounds.fromPoints(points), { checkZoomRange: true, zoomMargin: 60 })
}

function setActive(id, fromMap = false) {
  if (activeId.value && placemarks[activeId.value]) placemarks[activeId.value].properties.set('active', false)
  activeId.value = id
  if (id && placemarks[id]) placemarks[id].properties.set('active', true)
  // навели на маркер — показываем его карточку в списке
  if (id && fromMap) cardEls[id]?.scrollIntoView({ block: 'nearest', behavior: 'smooth' })
}

watch(() => route.query, () => load(), { deep: true })
watch(onMap, () => drawPlacemarks())

onMounted(async () => {
  load()
  window.addEventListener('resize', fitMap)
  await waitForYmaps()
  initMap()
})
onUnmounted(() => {
  window.removeEventListener('resize', fitMap)
  map?.destroy()
  map = null
})
</script>

<style scoped>
.map-page {
  height: calc(100vh - 70px);
}
</style>

<!-- Не scoped: эти элементы рисует Яндекс.Карта вне компонента -->
<style>
.ym-price {
  position: absolute;
  transform: translate(-50%, calc(-100% - 6px));
  white-space: nowrap;
  padding: 5px 10px;
  border-radius: 999px;
  background: #fff;
  color: #152242;
  font: 700 13px/1.2 'Trebuchet MS', Arial, sans-serif;
  box-shadow: 0 3px 10px rgba(15, 23, 42, 0.22);
  border: 1.5px solid #fff;
  cursor: pointer;
  transition: transform .12s, background-color .12s, color .12s;
}
.ym-price::after {
  content: '';
  position: absolute;
  left: 50%;
  bottom: -6px;
  transform: translateX(-50%);
  border: 6px solid transparent;
  border-bottom: 0;
  border-top-color: #fff;
}
.ym-price--active {
  background: #0468ff;
  color: #fff;
  border-color: #0468ff;
  transform: translate(-50%, calc(-100% - 6px)) scale(1.12);
  z-index: 10;
}
.ym-price--active::after {
  border-top-color: #0468ff;
}
.ym-hint {
  width: 250px;
  background: #fff;
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 12px 32px rgba(15, 23, 42, 0.25);
  font-family: 'Trebuchet MS', Arial, sans-serif;
  transform: translate(-50%, calc(-100% - 52px));
}
.ym-hint__img {
  display: block;
  width: 100%;
  height: 140px;
  object-fit: cover;
  background: #e2e8f0;
}
.ym-hint__body {
  padding: 10px 12px 12px;
}
.ym-hint__price {
  font-size: 17px;
  font-weight: 700;
  color: #152242;
}
.ym-hint__title {
  margin-top: 3px;
  font-size: 13.5px;
  font-weight: 600;
  color: #0468ff;
  line-height: 1.3;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.ym-hint__facts {
  margin-top: 3px;
  font-size: 12px;
  color: #64748b;
}
.ym-hint__more {
  margin-top: 6px;
  font-size: 11.5px;
  color: #94a3b8;
}
</style>

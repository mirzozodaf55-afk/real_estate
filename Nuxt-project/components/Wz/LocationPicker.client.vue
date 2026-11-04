<template>
  <div>
    <span class="wz-label">{{ t('Адрес') }}</span>
    <div class="relative">
      <font-awesome-icon :icon="['fas', 'magnifying-glass']" class="absolute left-[14px] top-[15px] text-[#94a3b8] text-[14px] pointer-events-none" />
      <input v-model="query" @input="onType" @keydown.down.prevent="move(1)" @keydown.up.prevent="move(-1)" @keydown.enter.prevent="pickActive"
        @focus="open = suggestions.length > 0" @blur="closeSoon" type="text" autocomplete="off" data-test="address-input"
        :placeholder="t('Город, улица, дом — например: Душанбе, Рудаки 45')"
        class="wz-input !pl-[40px] !pr-[40px]" :class="{ 'wz-input--bad': invalid && !hasPoint }" />
      <font-awesome-icon v-if="searching" :icon="['fas', 'spinner']" spin-pulse class="absolute right-[14px] top-[15px] text-[#94a3b8]" />

      <ul v-if="open && suggestions.length" class="absolute z-[30] left-0 right-0 top-[52px] list-none m-0 p-[6px] bg-white rounded-[14px] border border-[#e8edf3] shadow-[0_14px_36px_rgba(15,23,42,0.16)] max-h-[300px] overflow-y-auto" data-test="address-suggestions">
        <li v-for="(s, i) of suggestions" :key="i">
          <button type="button" @mousedown.prevent="pick(s)"
            class="w-full text-left flex items-start gap-[10px] px-[10px] py-[9px] rounded-[10px]"
            :class="i === active ? 'bg-[#eef4ff]' : 'hover:bg-[#f8fafc]'">
            <font-awesome-icon :icon="['fas', 'location-dot']" class="text-[#94a3b8] mt-[3px] text-[13px]" />
            <span class="min-w-0">
              <span class="block text-[14.5px] text-[#152242] font-semibold truncate">{{ t(s.title) }}</span>
              <span v-if="s.subtitle" class="block text-[12.5px] text-[#64748b] truncate">{{ t(s.subtitle) }}</span>
            </span>
          </button>
        </li>
      </ul>
    </div>
    <p v-if="searchError" class="wz-err">{{ t(searchError) }}</p>

    <div class="relative mt-[12px] rounded-[14px] overflow-hidden border border-[#e2e8f0] h-[320px] sm:h-[380px] bg-[#eef2f7]">
      <div ref="mapEl" class="absolute inset-0" data-test="location-map"></div>
      <div v-if="!ready" class="absolute inset-0 grid place-items-center text-[14px] text-[#64748b]">
        <span><font-awesome-icon :icon="['fas', 'spinner']" spin-pulse class="mr-[6px]" />{{ t('Загружаем карту…') }}</span>
      </div>
      <div v-else-if="!hasPoint" class="absolute left-1/2 -translate-x-1/2 top-[12px] px-[14px] py-[8px] rounded-full bg-[#152242]/85 text-white text-[13px] pointer-events-none whitespace-nowrap">
        {{ t('Найдите адрес или нажмите на карту') }}
      </div>
      <button type="button" @click="locateMe" :disabled="locating" :title="t('Где я')"
        class="absolute right-[10px] bottom-[34px] h-[38px] px-[12px] rounded-[10px] bg-white shadow-md text-[13px] font-semibold text-[#152242] hover:text-[#0468ff] flex items-center gap-[6px]">
        <font-awesome-icon :icon="['fas', locating ? 'spinner' : 'location-crosshairs']" :spin-pulse="locating" /> {{ t('Где я') }}
      </button>
    </div>

    <div v-if="hasPoint" class="mt-[10px] flex items-start gap-[10px] px-[12px] py-[10px] rounded-[12px] bg-[#f0fdf4] border border-[#bbf7d0]" data-test="picked-address">
      <font-awesome-icon :icon="['fas', 'location-dot']" class="text-[#16a34a] mt-[3px]" />
      <div class="min-w-0">
        <div class="text-[14px] font-semibold text-[#152242] break-words">{{ draft.address || t('Точка выбрана на карте') }}</div>
        <div class="text-[12px] text-[#64748b]">{{ t('Неточно? Перетащите метку или нажмите на нужное место карты.') }}</div>
      </div>
    </div>
    <p v-else-if="invalid" class="wz-err">{{ t('Укажите, где находится объект: найдите адрес или нажмите на карту') }}</p>
  </div>
</template>

<script setup>
// Где объект: адрес + точка на карте. Пишет прямо в черновик: draft.address, draft.coord_x (широта), draft.coord_y (долгота)
const props = defineProps({
  draft: { type: Object, required: true },
  invalid: Boolean,
})

const SUGGEST_KEY = 'e0aec847-3d84-4153-8955-4d0520b28188'
const TJ_BOUNDS = [[36.6, 67.3], [41.1, 75.2]] // юг-запад, северо-восток (широта, долгота)
const DUSHANBE = [38.5598, 68.787]

const query = ref(props.draft.address || '')
const suggestions = ref([])
const active = ref(-1)
const open = ref(false)
const searching = ref(false)
const searchError = ref('')
const ready = ref(false)
const locating = ref(false)
const mapEl = ref(null)
const hasPoint = computed(() => !!(props.draft.coord_x && props.draft.coord_y))

let map = null
let placemark = null
let typeTimer = null
let requestId = 0

// --- Поиск адреса
function onType() {
  searchError.value = ''
  clearTimeout(typeTimer)
  if (query.value.trim().length < 2) {
    suggestions.value = []
    open.value = false
    return
  }
  typeTimer = setTimeout(search, 300)
}

async function search() {
  const text = query.value.trim()
  const id = ++requestId
  searching.value = true
  try {
    let list = await suggestYandex(text)
    // сервис подсказок недоступен или ничего не нашёл — ищем геокодером самой карты
    if (!list.length) list = await geocodeList(text)
    if (id !== requestId) return
    suggestions.value = list
    active.value = list.length ? 0 : -1
    open.value = true
    if (!list.length) searchError.value = 'Ничего не нашли — уточните адрес или просто нажмите на карту'
  } finally {
    if (id === requestId) searching.value = false
  }
}

async function suggestYandex(text) {
  try {
    const params = new URLSearchParams({
      apikey: SUGGEST_KEY, text, lang: 'ru_RU', results: '7', print_address: '1',
      bbox: `${TJ_BOUNDS[0][1]},${TJ_BOUNDS[0][0]}~${TJ_BOUNDS[1][1]},${TJ_BOUNDS[1][0]}`, // долгота,широта
    })
    const res = await fetch(`https://suggest-maps.yandex.ru/v1/suggest?${params}`)
    if (!res.ok) return []
    const json = await res.json()
    return (json.results || []).map((r) => ({
      title: r.title?.text || '',
      subtitle: r.subtitle?.text || '',
      full: r.address?.formatted_address || [r.subtitle?.text, r.title?.text].filter(Boolean).join(', '),
    }))
  } catch (e) {
    return []
  }
}

async function geocodeList(text) {
  if (!window.ymaps) return []
  try {
    const res = await window.ymaps.geocode(text, { results: 7, boundedBy: TJ_BOUNDS, strictBounds: false })
    const list = []
    res.geoObjects.each((o) => {
      list.push({ title: o.properties.get('name'), subtitle: o.properties.get('description'), full: cleanAddress(o.getAddressLine()), coords: o.geometry.getCoordinates() })
    })
    return list
  } catch (e) {
    return []
  }
}

function move(step) {
  if (!suggestions.value.length) return
  open.value = true
  active.value = (active.value + step + suggestions.value.length) % suggestions.value.length
}
function pickActive() {
  if (open.value && suggestions.value[active.value]) pick(suggestions.value[active.value])
  else if (query.value.trim()) search()
}
function closeSoon() {
  setTimeout(() => { open.value = false }, 150)
}

async function pick(s) {
  open.value = false
  query.value = s.full || s.title
  searchError.value = ''
  let coords = s.coords
  if (!coords && window.ymaps) {
    try {
      const res = await window.ymaps.geocode(s.full || s.title, { results: 1, boundedBy: TJ_BOUNDS })
      coords = res.geoObjects.get(0)?.geometry.getCoordinates()
    } catch (e) { /* ниже — сообщение */ }
  }
  if (!coords) {
    searchError.value = 'Не удалось найти это место на карте — нажмите на карту, чтобы указать точку'
    return
  }
  setPoint(coords, cleanAddress(s.full || s.title), 17)
}

// --- Точка на карте
function cleanAddress(a) {
  return String(a || '').replace(/^Таджикистан,\s*/, '').trim()
}

function setPoint(coords, address = null, zoom = null) {
  props.draft.coord_x = +coords[0].toFixed(6)
  props.draft.coord_y = +coords[1].toFixed(6)
  if (!map) return
  if (!placemark) {
    placemark = new window.ymaps.Placemark(coords, {}, { preset: 'islands#blueHomeCircleIcon', draggable: true })
    placemark.events.add('dragend', () => {
      const c = placemark.geometry.getCoordinates()
      setPoint(c)
    })
    map.geoObjects.add(placemark)
  } else {
    placemark.geometry.setCoordinates(coords)
  }
  if (zoom) map.setCenter(coords, zoom, { duration: 300 })
  if (address) {
    props.draft.address = address
    query.value = address
  } else {
    reverseGeocode(coords)
  }
}

// По точке на карте — подписываем адрес (клик по карте или перетаскивание метки)
async function reverseGeocode(coords) {
  try {
    const res = await window.ymaps.geocode(coords, { results: 1 })
    const address = cleanAddress(res.geoObjects.get(0)?.getAddressLine())
    if (address && props.draft.coord_x === +coords[0].toFixed(6)) {
      props.draft.address = address
      query.value = address
    }
  } catch (e) { /* адрес останется прежним — точка всё равно сохранена */ }
}

function locateMe() {
  if (!navigator.geolocation) return (searchError.value = 'Браузер не умеет определять местоположение')
  locating.value = true
  navigator.geolocation.getCurrentPosition((pos) => {
    locating.value = false
    setPoint([pos.coords.latitude, pos.coords.longitude], null, 17)
  }, () => {
    locating.value = false
    searchError.value = 'Не удалось определить местоположение — разрешите доступ к геопозиции или найдите адрес вручную'
  }, { enableHighAccuracy: true, timeout: 10000 })
}

function waitForYmaps() {
  return new Promise((resolve) => {
    const tick = () => (window.ymaps ? window.ymaps.ready(resolve) : setTimeout(tick, 200))
    tick()
  })
}

onMounted(async () => {
  await waitForYmaps()
  if (!mapEl.value) return
  const start = hasPoint.value ? [+props.draft.coord_x, +props.draft.coord_y] : DUSHANBE
  map = new window.ymaps.Map(mapEl.value, { center: start, zoom: hasPoint.value ? 16 : 12, controls: ['zoomControl', 'typeSelector'] }, { suppressMapOpenBlock: true })
  map.events.add('click', (e) => setPoint(e.get('coords')))
  if (hasPoint.value) setPoint(start, props.draft.address || null)
  ready.value = true
  setTimeout(() => map?.container.fitToViewport(), 300)
})
onUnmounted(() => {
  clearTimeout(typeTimer)
  map?.destroy()
  map = null
})
</script>

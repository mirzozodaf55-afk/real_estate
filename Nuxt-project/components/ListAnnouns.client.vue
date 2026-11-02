<template>
  <div class="min-h-screen bg-[#f4f6fb] pb-[40px]">
    <div class="max-w-[1100px] mx-auto px-[16px] pt-[16px]">

      <!-- Фильтры -->
      <section class="bg-white rounded-[16px] border border-[#e8edf3] p-[12px] md:p-[14px]">
        <div class="flex items-center gap-[8px] md:hidden">
          <button @click="showMobileFilters = !showMobileFilters"
            class="flex-1 h-[40px] rounded-[10px] bg-[#eef4ff] text-[#0468ff] font-semibold text-[14px] flex items-center justify-center gap-[8px]">
            <font-awesome-icon :icon="['fas', 'sliders']" />
            Фильтры
            <span v-if="activeCount" class="min-w-[20px] h-[20px] px-[6px] rounded-full bg-[#0468ff] text-white text-[12px] grid place-items-center">{{ activeCount }}</span>
          </button>
          <button @click="goToMap"
            class="h-[40px] px-[14px] rounded-[10px] border border-[#e2e8f0] text-[#152242] text-[14px] font-semibold flex items-center gap-[6px]">
            <font-awesome-icon :icon="['far', 'map']" class="text-[#0468ff]" /> На карте
          </button>
        </div>

        <div class="flex-col md:flex md:flex-row md:flex-wrap md:items-center gap-[10px] mt-[12px] md:mt-0"
          :class="showMobileFilters ? 'flex' : 'hidden'">
          <div class="flex bg-[#eef2f7] rounded-[10px] p-[3px] overflow-x-auto">
            <button v-for="d of DEALS" :key="d.value" @click="updateQuery({ dealType: d.value })"
              class="flex-1 md:flex-none whitespace-nowrap px-[14px] h-[34px] rounded-[8px] text-[14px] font-semibold transition-colors"
              :class="filters.dealType === d.value ? 'bg-white text-[#0468ff] shadow-sm' : 'text-[#475569] hover:text-[#152242]'">
              {{ d.label }}
            </button>
          </div>

          <select :value="filters.typeObject" @change="updateQuery({ typeObject: $event.target.value, buildingType: '' })"
            class="h-[40px] px-[10px] rounded-[10px] border border-[#e2e8f0] bg-white text-[14px] text-[#152242] outline-none focus:border-[#0468ff] md:w-[190px]">
            <option value="">Любой тип</option>
            <optgroup label="Жилая">
              <option v-for="t of RESIDENTIAL" :key="t" :value="t">{{ t }}</option>
            </optgroup>
            <optgroup label="Коммерческая">
              <option v-for="t of COMMERCIAL" :key="t" :value="t">{{ t }}</option>
            </optgroup>
          </select>

          <div class="flex items-center gap-[4px]">
            <span class="text-[13px] text-[#64748b] mr-[2px]">Комнат</span>
            <button v-for="r of ROOMS" :key="r.value" @click="toggleRoom(r.value)"
              class="w-[38px] h-[38px] rounded-[10px] border text-[14px] font-semibold transition-colors"
              :class="filters.quantityRoom.includes(r.value) ? 'bg-[#0468ff] border-[#0468ff] text-white' : 'bg-white border-[#e2e8f0] text-[#152242] hover:border-[#0468ff]'">
              {{ r.label }}
            </button>
          </div>

          <div class="flex items-center gap-[6px]">
            <input v-model="priceFromInput" @input="onTextFilter" inputmode="numeric" placeholder="Цена от"
              class="w-full md:w-[110px] h-[40px] px-[12px] rounded-[10px] border border-[#e2e8f0] text-[14px] outline-none focus:border-[#0468ff]" />
            <span class="text-[#94a3b8]">—</span>
            <input v-model="priceToInput" @input="onTextFilter" inputmode="numeric" placeholder="до"
              class="w-full md:w-[110px] h-[40px] px-[12px] rounded-[10px] border border-[#e2e8f0] text-[14px] outline-none focus:border-[#0468ff]" />
          </div>

          <div class="relative md:flex-1 md:min-w-[200px]">
            <font-awesome-icon :icon="['fas', 'magnifying-glass']" class="absolute left-[12px] top-1/2 -translate-y-1/2 text-[#94a3b8] text-[13px]" />
            <input v-model="qInput" @input="onTextFilter" placeholder="Поиск по объявлениям"
              class="w-full h-[40px] pl-[34px] pr-[12px] rounded-[10px] border border-[#e2e8f0] text-[14px] outline-none focus:border-[#0468ff]" />
          </div>

          <button @click="openExtraFilters"
            class="h-[40px] px-[14px] rounded-[10px] border border-[#e2e8f0] text-[14px] font-semibold text-[#152242] hover:border-[#0468ff] flex items-center justify-center gap-[8px]">
            <font-awesome-icon :icon="['fas', 'sliders']" class="text-[#0468ff]" />
            Ещё фильтры
            <span v-if="extraCount" class="min-w-[20px] h-[20px] px-[6px] rounded-full bg-[#0468ff] text-white text-[12px] grid place-items-center">{{ extraCount }}</span>
          </button>

          <button v-if="activeCount" @click="resetFilters"
            class="h-[40px] px-[10px] text-[14px] font-semibold text-[#64748b] hover:text-[#ef4444]">
            Сбросить
          </button>
        </div>
      </section>

      <!-- Заголовок результатов -->
      <div class="flex flex-wrap items-center justify-between gap-[10px] mt-[18px] mb-[12px]">
        <div>
          <h1 class="text-[20px] md:text-[22px] font-bold text-[#152242] m-0">{{ heading }}</h1>
          <p class="text-[14px] text-[#64748b] m-0 mt-[2px]">
            <template v-if="loading">Ищем объявления…</template>
            <template v-else>Найдено {{ total }} {{ plural(total, ['объявление', 'объявления', 'объявлений']) }}</template>
          </p>
        </div>
        <div class="flex flex-wrap items-center gap-[8px]">
          <NotifyMe v-if="items.length" compact />
          <select :value="filters.sort" @change="updateQuery({ sort: $event.target.value === 'new' ? '' : $event.target.value })"
            class="h-[38px] px-[10px] rounded-[10px] border border-[#e2e8f0] bg-white text-[14px] text-[#152242] outline-none">
            <option value="new">Сначала новые</option>
            <option value="price_asc">Сначала дешевле</option>
            <option value="price_desc">Сначала дороже</option>
            <option value="area_desc">По площади</option>
          </select>
          <button @click="goToMap"
            class="hidden md:flex h-[38px] px-[14px] rounded-[10px] bg-[#eef4ff] text-[#0468ff] text-[14px] font-semibold items-center gap-[6px]">
            <font-awesome-icon :icon="['far', 'map']" /> На карте
          </button>
        </div>
      </div>

      <!-- Скелетон загрузки -->
      <div v-if="loading" class="flex flex-col gap-[14px]">
        <div v-for="n in 3" :key="n" class="bg-white rounded-[16px] border border-[#e8edf3] overflow-hidden flex flex-col md:flex-row animate-pulse">
          <div class="md:w-[320px] h-[200px] md:h-[240px] bg-[#e2e8f0]"></div>
          <div class="flex-1 p-[20px] flex flex-col gap-[12px]">
            <div class="h-[22px] w-[40%] bg-[#e2e8f0] rounded"></div>
            <div class="h-[16px] w-[70%] bg-[#eef2f7] rounded"></div>
            <div class="h-[14px] w-[50%] bg-[#eef2f7] rounded"></div>
            <div class="h-[14px] w-[90%] bg-[#eef2f7] rounded mt-[8px]"></div>
          </div>
        </div>
      </div>

      <!-- Ошибка -->
      <div v-else-if="loadError" class="bg-white rounded-[16px] border border-[#e8edf3] p-[32px] text-center">
        <p class="text-[#152242] font-semibold m-0">{{ loadError }}</p>
        <button @click="load(true)" class="mt-[12px] h-[38px] px-[18px] rounded-[10px] bg-[#0468ff] text-white text-[14px] font-semibold">Повторить</button>
      </div>

      <!-- Пусто -->
      <div v-else-if="!items.length" class="bg-white rounded-[16px] border border-[#e8edf3] p-[32px] text-center">
        <div class="w-[64px] h-[64px] mx-auto rounded-full bg-[#eef4ff] grid place-items-center text-[#0468ff] text-[26px]">
          <font-awesome-icon :icon="['fas', 'magnifying-glass']" />
        </div>
        <p class="text-[18px] font-bold text-[#152242] mt-[14px] mb-[4px]">Ничего не нашлось</p>
        <p class="text-[14px] text-[#64748b] m-0">Попробуйте изменить или сбросить фильтры</p>
        <button v-if="activeCount" @click="resetFilters" class="mt-[14px] h-[38px] px-[18px] rounded-[10px] bg-[#0468ff] text-white text-[14px] font-semibold">Сбросить фильтры</button>
        <NotifyMe class="mt-[16px]" />
      </div>

      <!-- Список -->
      <div v-else class="flex flex-col gap-[14px]">
        <article v-for="item of items" :key="item.id" @click="openAnnoun(item)" :data-promo="item.promo_active || undefined"
          class="group bg-white rounded-[16px] border overflow-hidden flex flex-col md:flex-row cursor-pointer transition-shadow hover:shadow-[0_8px_30px_rgba(15,23,42,0.08)]"
          :class="item.promo_active === 'vip' ? 'promo-vip border-[#f5c451]' : 'border-[#e8edf3]'">

          <div class="relative md:w-[320px] md:shrink-0 aspect-[16/10] md:aspect-auto md:h-[240px] bg-[#e2e8f0] overflow-hidden"
            @mouseenter="item.warm = true">
            <!-- лента фото: сдвигается плавно; свайп на телефоне -->
            <div v-if="item.pictures.length" class="flex h-full transition-transform duration-[400ms] ease-[cubic-bezier(.22,.61,.36,1)]"
              :style="`transform: translateX(-${item.photoIndex * 100}%)`"
              @touchstart.passive="swipeStart(item, $event)" @touchend="swipeEnd(item, $event)">
              <!-- соседнее фото грузим заранее, как только к карточке потянулись, — чтобы при сдвиге не выезжал пустой кадр -->
              <img v-for="(p, idx) of item.pictures" :key="p.id || idx" :src="idx <= item.photoIndex + (item.warm ? 1 : 0) ? photoUrl(p) : undefined"
                :style="`transform: rotate(${p.rotation || 0}deg)`" draggable="false"
                :loading="idx ? 'eager' : 'lazy'" class="w-full h-full shrink-0 object-cover" :alt="idx === 0 ? (item.title || item.type_object) : ''" />
            </div>
            <div v-else class="w-full h-full grid place-items-center text-[#94a3b8] text-[34px]">
              <font-awesome-icon :icon="['far', 'image']" />
            </div>

            <div class="absolute top-[10px] left-[10px] flex items-center gap-[6px]">
              <!-- платное продвижение: VIP выше ТОП (сортировку делает сервер) -->
              <span v-if="item.promo_active === 'vip'" class="promo-badge-vip px-[8px] py-[3px] rounded-[8px] text-[12px] font-extrabold text-[#5b3a00] shadow-sm">
                <font-awesome-icon :icon="['fas', 'crown']" class="text-[10px] mr-[3px]" />VIP
              </span>
              <span v-else-if="item.promo_active === 'top'" class="px-[8px] py-[3px] rounded-[8px] bg-[#0468ff] text-[12px] font-extrabold text-white shadow-sm">
                <font-awesome-icon :icon="['fas', 'arrow-up']" class="text-[10px] mr-[3px]" />ТОП
              </span>
              <span class="px-[8px] py-[3px] rounded-[8px] bg-white text-[12px] font-semibold text-[#152242] shadow-sm">
                {{ dealLabel(item) }}
              </span>
            </div>

            <button @click.stop="toggleLike(item)" :aria-label="item.is_liked ? 'Убрать из избранного' : 'В избранное'"
              class="absolute top-[10px] right-[10px] w-[36px] h-[36px] rounded-full bg-white grid place-items-center shadow-sm hover:scale-105 transition-transform">
              <font-awesome-icon :icon="[item.is_liked ? 'fas' : 'far', 'heart']" :class="item.is_liked ? 'text-[#ef4444]' : 'text-[#152242]'" />
            </button>

            <template v-if="item.pictures.length > 1">
              <button @click.stop="shiftPhoto(item, -1)" aria-label="Предыдущее фото"
                class="absolute left-[8px] top-1/2 -translate-y-1/2 w-[30px] h-[30px] rounded-full bg-black/45 text-white grid place-items-center md:opacity-0 md:group-hover:opacity-100 transition-opacity">
                <font-awesome-icon :icon="['fas', 'chevron-left']" class="text-[12px]" />
              </button>
              <button @click.stop="shiftPhoto(item, 1)" aria-label="Следующее фото"
                class="absolute right-[8px] top-1/2 -translate-y-1/2 w-[30px] h-[30px] rounded-full bg-black/45 text-white grid place-items-center md:opacity-0 md:group-hover:opacity-100 transition-opacity">
                <font-awesome-icon :icon="['fas', 'chevron-right']" class="text-[12px]" />
              </button>
              <div class="absolute bottom-[10px] left-0 right-0 flex justify-center gap-[5px]">
                <span v-for="(p, idx) of item.pictures.slice(0, 7)" :key="p.id || idx"
                  class="h-[6px] rounded-full transition-all"
                  :class="idx === Math.min(item.photoIndex, 6) ? 'w-[16px] bg-white' : 'w-[6px] bg-white/60'"></span>
              </div>
              <span class="absolute bottom-[8px] right-[10px] px-[7px] py-[1px] rounded-[8px] bg-black/50 text-white text-[11px]">
                {{ item.photoIndex + 1 }}/{{ item.pictures.length }}
              </span>
            </template>
          </div>

          <div class="flex-1 min-w-0 p-[16px] md:p-[20px] flex flex-col">
            <div class="flex items-start justify-between gap-[12px]">
              <div class="min-w-0">
                <div class="text-[22px] font-bold text-[#152242] leading-tight">
                  {{ fmt(item.price) }} <span class="text-[15px] font-semibold">сомони</span><span class="text-[14px] font-normal text-[#64748b]">{{ priceSuffix(item) }}</span>
                </div>
                <div v-if="pricePerMeter(item)" class="text-[13px] text-[#64748b] mt-[2px]">{{ pricePerMeter(item) }} сомони/м²</div>
              </div>
              <span class="text-[12px] text-[#94a3b8] whitespace-nowrap mt-[5px]">{{ timeAgo(item.created_at) }}</span>
            </div>

            <h3 class="mt-[10px] mb-0 text-[16px] font-semibold text-[#0468ff] leading-snug line-clamp-2">{{ item.title || item.type_object }}</h3>
            <div v-if="facts(item)" class="mt-[6px] text-[14px] text-[#152242]">{{ facts(item) }}</div>
            <div class="mt-[6px] flex items-center gap-[6px] text-[13px] text-[#64748b] min-w-0">
              <font-awesome-icon :icon="['fas', 'house']" class="text-[11px] shrink-0" />
              <span class="shrink-0">{{ item.type_object }}</span>
              <template v-if="item.address">
                <span class="shrink-0">·</span>
                <font-awesome-icon :icon="['fas', 'location-dot']" class="text-[11px] shrink-0 text-[#0468ff]" />
                <span class="truncate">{{ item.address }}</span>
              </template>
            </div>
            <p v-if="item.description" class="mt-[10px] mb-0 text-[14px] text-[#475569] line-clamp-2">{{ item.description }}</p>

            <div class="mt-auto pt-[14px] md:mt-[14px] flex flex-wrap items-center justify-between gap-[10px] border-t border-[#f1f5f9]">
              <div v-if="item.owner" class="flex items-center gap-[8px] min-w-0">
                <img :src="`${apiBase()}/api/avatar/${item.owner.avatar || 'avatar.jpg'}`" class="w-[32px] h-[32px] rounded-full object-cover bg-[#e2e8f0] shrink-0" alt="" />
                <span class="text-[14px] text-[#152242] truncate">{{ ownerName(item.owner) }}</span>
              </div>
              <div v-if="!isMine(item)" class="flex items-center gap-[8px] ml-auto">
                <a v-if="revealed[item.id] && item.owner?.phone_number" :href="`tel:+992${item.owner.phone_number}`" @click.stop
                  class="h-[36px] px-[14px] rounded-[10px] bg-[#0468ff] text-white text-[14px] font-semibold flex items-center gap-[6px] no-underline">
                  <font-awesome-icon :icon="['fas', 'phone']" class="text-[12px]" /> +992 {{ item.owner.phone_number }}
                </a>
                <button v-else-if="!user?.id || item.owner?.phone_number" @click.stop="revealPhone(item)"
                  class="h-[36px] px-[14px] rounded-[10px] bg-[#0468ff] hover:bg-[#0353cc] text-white text-[14px] font-semibold flex items-center gap-[6px]">
                  <font-awesome-icon :icon="['fas', 'phone']" class="text-[12px]" /> Показать телефон
                </button>
                <button @click.stop="goToChat(item)"
                  class="h-[36px] px-[14px] rounded-[10px] bg-[#eef4ff] hover:bg-[#dbe8ff] text-[#0468ff] text-[14px] font-semibold flex items-center gap-[6px]">
                  <font-awesome-icon :icon="['far', 'comment']" class="text-[12px]" /> Написать
                </button>
              </div>
            </div>
          </div>
        </article>

        <button v-if="page < lastPage" @click="load(false)" :disabled="loadingMore"
          class="h-[44px] rounded-[12px] bg-white border border-[#e2e8f0] text-[#0468ff] text-[15px] font-semibold hover:border-[#0468ff]">
          <font-awesome-icon v-if="loadingMore" :icon="['fas', 'spinner']" spin-pulse />
          <span v-else>Показать ещё</span>
        </button>
      </div>
    </div>

    <MooreFilter :getAnnouns="applyExtraFilters" v-if="showFIlterModal" />
  </div>
</template>

<script setup>
useHead({ title: 'Объявления' })

const DEALS = [
  { label: 'Все', value: '' },
  { label: 'Купить', value: 'Продажа' },
  { label: 'Снять', value: 'Аренда' },
  { label: 'Посуточно', value: 'Посуточно' },
]
const RESIDENTIAL = ['Квартира', 'Квартира в Новостройке', 'Комната', 'Дом/Дача', 'Коттедж', 'Часть дома', 'Участок']
const COMMERCIAL = ['Офис', 'Здание', 'Торговая площадь', 'Склад', 'Коммерческая земля']
const ROOMS = [{ label: '1', value: '1' }, { label: '2', value: '2' }, { label: '3', value: '3' }, { label: '4+', value: '4' }]
// Поля модалки «Ещё фильтры» (MooreFilter)
const EXTRA_KEYS = ['totalAreaFrom', 'totalAreaTo', 'areaFrom', 'areaTo', 'kitchenAreaFrom', 'kitchenAreaTo', 'floorFrom', 'floorTo',
  'floorHouseFrom', 'floorHouseTo', 'yearFrom', 'yearTo', 'landAreaFrom', 'landAreaTo', 'houseAreaFrom', 'houseAreaTo', 'repair', 'typeHome', 'parking', 'condition', 'balcon']
const ARRAY_KEYS = ['quantityRoom', 'buildingType', 'repair', 'typeHome', 'parking', 'condition']
const FILTER_KEYS = ['dealType', 'typeObject', 'buildingType', 'quantityRoom', 'priceFrom', 'priceTo', 'q', ...EXTRA_KEYS]

const route = useRoute()
const router = useRouter()
const { showFIlterModal, showMadoal } = useSwitch()
const { direction } = getData()
const { user } = getUserByServer(getToken())

const items = ref([])
const total = ref(0)
const page = ref(1)
const lastPage = ref(1)
const loading = ref(true)
const loadingMore = ref(false)
const loadError = ref('')
const showMobileFilters = ref(false)
const revealed = reactive({})

// --- Фильтры живут в URL: так их можно отправить ссылкой, и «назад» в браузере возвращает прошлый поиск
const asArray = (v) => (v === undefined || v === null || v === '' ? [] : Array.isArray(v) ? v : [v])
const filters = computed(() => ({
  dealType: route.query.dealType || '',
  typeObject: route.query.typeObject || '',
  quantityRoom: asArray(route.query.quantityRoom).map(String),
  priceFrom: route.query.priceFrom || '',
  priceTo: route.query.priceTo || '',
  q: route.query.q || '',
  sort: route.query.sort || 'new',
}))
const activeCount = computed(() => FILTER_KEYS.filter((k) => asArray(route.query[k]).length).length)
const extraCount = computed(() => EXTRA_KEYS.filter((k) => asArray(route.query[k]).length).length)
const heading = computed(() => {
  const deal = { 'Продажа': 'Продажа', 'Аренда': 'Аренда', 'Посуточно': 'Посуточная аренда' }[filters.value.dealType]
  const estate = route.query.typeRealEstate === 'Коммерческая' ? 'Коммерческая недвижимость' : ''
  return [deal, filters.value.typeObject || estate].filter(Boolean).join(' · ') || 'Все объявления'
})

function updateQuery(patch) {
  const next = { ...route.query, ...patch }
  for (const key of Object.keys(next)) {
    const v = next[key]
    if (v === '' || v === null || v === undefined || (Array.isArray(v) && !v.length)) delete next[key]
  }
  router.replace({ path: route.path, query: next })
}

function toggleRoom(value) {
  const current = filters.value.quantityRoom
  updateQuery({ quantityRoom: current.includes(value) ? current.filter((r) => r !== value) : [...current, value] })
}

const priceFromInput = ref(filters.value.priceFrom)
const priceToInput = ref(filters.value.priceTo)
const qInput = ref(filters.value.q)
let textTimer = null
function onTextFilter() {
  clearTimeout(textTimer)
  textTimer = setTimeout(() => {
    updateQuery({
      priceFrom: String(priceFromInput.value).replace(/\D/g, ''),
      priceTo: String(priceToInput.value).replace(/\D/g, ''),
      q: qInput.value.trim(),
    })
  }, 500)
}

function resetFilters() {
  router.replace({ path: route.path, query: route.query.sort ? { sort: route.query.sort } : {} })
}

function openExtraFilters() {
  // MooreFilter берёт стартовые значения (и typeObject для набора полей) из sessionStorage.filter
  sessionStorage.setItem('filter', JSON.stringify(buildBody(1)))
  showFIlterModal.value = true
}
function applyExtraFilters(extra) {
  const patch = {}
  for (const key of EXTRA_KEYS) patch[key] = extra?.[key] ?? ''
  updateQuery(patch)
}

// --- Загрузка
function buildBody(pageNumber) {
  const body = { page: pageNumber }
  for (const [key, value] of Object.entries(route.query)) {
    body[key] = ARRAY_KEYS.includes(key) ? asArray(value) : value
  }
  return body
}

let requestId = 0
async function load(reset = true) {
  const id = ++requestId
  if (reset) loading.value = true
  else loadingMore.value = true
  loadError.value = ''
  try {
    const token = getToken()
    const res = await fetch(`${apiBase()}/api/announs/search`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        ...(token ? { Authorization: 'Bearer ' + token } : {}),
      },
      body: JSON.stringify(buildBody(reset ? 1 : page.value + 1)),
    })
    if (!res.ok) throw new Error(String(res.status))
    const json = await res.json()
    if (id !== requestId) return // пришёл ответ на уже устаревший фильтр
    const prepared = json.data.map((item) => ({
      ...item,
      image: item.pictures, // страница объявления ждёт поле image
      photoIndex: 0,
      warm: false,
      address: item.address || null, // адрес, выбранный владельцем на карте; у старых объявлений его нет
    }))
    items.value = reset ? prepared : [...items.value, ...prepared]
    total.value = json.total
    page.value = json.page
    lastPage.value = json.last_page
    geocode(prepared)
  } catch (e) {
    if (id === requestId) loadError.value = 'Не удалось загрузить объявления'
  } finally {
    if (id === requestId) {
      loading.value = false
      loadingMore.value = false
    }
  }
}

watch(() => route.query, () => {
  if (route.path !== '/list-announ') return
  priceFromInput.value = filters.value.priceFrom
  priceToInput.value = filters.value.priceTo
  qInput.value = filters.value.q
  load(true)
}, { deep: true })

onMounted(() => load(true))
onUnmounted(() => clearTimeout(textTimer))

// Старым объявлениям (до сохранения адреса при подаче) адрес подбираем по координатам у Яндекс.Карт
function geocode(list) {
  if (!window.ymaps) return
  window.ymaps.ready(() => {
    for (const item of list) {
      if (item.address || !item.coord_x || !item.coord_y) continue
      window.ymaps.geocode([item.coord_x, item.coord_y]).then((res) => {
        const obj = res.geoObjects.get(0)
        const target = items.value.find((i) => i.id === item.id)
        if (obj && target) target.address = obj.getAddressLine()
      }).catch(() => {})
    }
  })
}

// --- Действия
function openAnnoun(item) {
  router.push(`/show_announ-${item.id}`)
}

function shiftPhoto(item, step) {
  item.warm = true
  const count = item.pictures.length
  item.photoIndex = (item.photoIndex + step + count) % count
}

// свайп по фото: сдвиг пальцем больше 40px — следующее/предыдущее фото, а не переход в объявление
let touchX = null
function swipeStart(item, e) {
  item.warm = true
  touchX = e.touches[0].clientX
}
function swipeEnd(item, e) {
  if (touchX === null) return
  const dx = e.changedTouches[0].clientX - touchX
  touchX = null
  if (Math.abs(dx) < 40 || item.pictures.length < 2) return
  e.preventDefault() // иначе после свайпа сработает клик по карточке
  shiftPhoto(item, dx < 0 ? 1 : -1)
}

function requireLogin() {
  direction.value = route.fullPath
  showMadoal.value = true
}

function isMine(item) {
  return user.value?.id && item.owner?.id === user.value.id
}

async function toggleLike(item) {
  if (!user.value?.id) return requireLogin()
  const prevLiked = item.is_liked
  const prevCount = item.likes_count
  item.is_liked = !prevLiked
  item.likes_count += prevLiked ? -1 : 1
  try {
    const res = await fetch(`${apiBase()}/api/likes`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json', Authorization: 'Bearer ' + getToken() },
      body: JSON.stringify({ announ_id: item.id }),
    })
    if (!res.ok) throw new Error()
    const json = await res.json()
    item.is_liked = json.liked
    item.likes_count = json['likes-count']
  } catch (e) {
    item.is_liked = prevLiked
    item.likes_count = prevCount
  }
}

function revealPhone(item) {
  if (!user.value?.id) return requireLogin()
  revealed[item.id] = true
}

function goToChat(item) {
  if (!user.value?.id) return requireLogin()
  if (!item.owner || item.owner.id === user.value.id) return
  router.push(`/chat-${user.value.id}-${user.value.uniq_id}-${item.owner.id}-${item.owner.uniq_id}`)
}

function goToMap() {
  router.push({ path: '/yandex-map', query: route.query })
}

// --- Форматирование
const fmt = (n) => (n === null || n === undefined ? '' : Number(n).toLocaleString('ru-RU'))
const photoUrl = (picture) => `${apiBase()}/api/image/medium/${picture.pictures}`

function dealLabel(item) {
  if (item.deal_type === 'Аренда') return item.rental_type === 'Посуточно' ? 'Посуточно' : 'Аренда'
  return item.deal_type || 'Объявление'
}
function priceSuffix(item) {
  if (item.deal_type !== 'Аренда') return ''
  return item.rental_type === 'Посуточно' ? ' / сутки' : ' / мес'
}
function pricePerMeter(item) {
  if (item.deal_type !== 'Продажа' || !item.price || !item.total_area) return ''
  return fmt(Math.round(item.price / item.total_area))
}
function facts(item) {
  const parts = []
  if (item.quantity_room) parts.push(`${item.quantity_room}-комн.`)
  else if (item.quantity_bedrooms) parts.push(`${item.quantity_bedrooms} ${plural(item.quantity_bedrooms, ['спальня', 'спальни', 'спален'])}`)
  const area = item.total_area || item.house_area || item.building_area
  if (area) parts.push(`${area} м²`)
  if (item.land_area) parts.push(`участок ${item.land_area} сот.`)
  if (item.floor && item.floor_in_house) parts.push(`${item.floor}/${item.floor_in_house} эт.`)
  else if (item.floor_in_house) parts.push(`${item.floor_in_house} ${plural(item.floor_in_house, ['этаж', 'этажа', 'этажей'])}`)
  if (item.numberSeats) parts.push(`${item.numberSeats} раб. мест`)
  return parts.join(' · ')
}
function ownerName(owner) {
  return [owner.name, owner.last_name].filter(Boolean).join(' ') || 'Владелец'
}
function plural(n, forms) {
  const mod10 = n % 10
  const mod100 = n % 100
  if (mod10 === 1 && mod100 !== 11) return forms[0]
  if (mod10 >= 2 && mod10 <= 4 && (mod100 < 10 || mod100 >= 20)) return forms[1]
  return forms[2]
}
function timeAgo(dateString) {
  const minutes = Math.floor((Date.now() - new Date(dateString).getTime()) / 60000)
  if (minutes < 1) return 'только что'
  if (minutes < 60) return `${minutes} мин. назад`
  const hours = Math.floor(minutes / 60)
  if (hours < 24) return `${hours} ч. назад`
  const days = Math.floor(hours / 24)
  if (days < 7) return `${days} ${plural(days, ['день', 'дня', 'дней'])} назад`
  if (days < 30) return `${Math.floor(days / 7)} нед. назад`
  return new Date(dateString).toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' })
}
</script>

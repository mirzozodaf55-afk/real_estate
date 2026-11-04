<template>
    <main>
        <!-- z-10 и без overflow-hidden: выпадающие панели должны выходить за нижний край блока -->
        <section class="hero relative z-10 w-full">
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <!-- Фото фона загружает админ (Админка → Оформление); поверх — затемнение, чтобы белый текст читался -->
                <template v-if="site.hero_image">
                    <div class="absolute inset-0 bg-cover bg-center" :style="{ backgroundImage: `url('${site.hero_image}')` }" data-test="hero-photo"></div>
                    <div class="absolute inset-0" :style="{ background: heroOverlay }"></div>
                </template>
                <template v-else>
                    <div class="absolute -top-[120px] -right-[80px] w-[420px] h-[420px] rounded-full bg-[#38bdf8]/25 blur-[90px]"></div>
                    <div class="absolute -bottom-[160px] left-[10%] w-[380px] h-[380px] rounded-full bg-[#6366f1]/25 blur-[90px]"></div>
                </template>
            </div>

            <div class="relative max-w-[1160px] mx-auto px-[24px] pt-[64px] pb-[72px]">
                <h1 class="m-0 text-white font-bold text-[40px] leading-[1.15] tracking-[-0.01em] max-w-[720px]">
                    {{ t('Найдите жильё, которое подходит именно вам') }}
                </h1>
                <p class="mt-[12px] mb-[32px] text-[17px] text-white/80 max-w-[620px]">
                    {{ t('Квартиры, дома и коммерческая недвижимость — купить, снять надолго или посуточно') }}
                </p>

                <div class="flex flex-wrap items-center justify-between gap-[12px] mb-[12px]">
                    <div class="inline-flex p-[4px] rounded-[12px] bg-white/15 backdrop-blur-sm" role="tablist">
                        <button v-for="deal of DEALS" :key="deal.value" type="button" role="tab"
                            :aria-selected="form.dealType === deal.value" @click="setDeal(deal.value)"
                            class="px-[18px] h-[38px] rounded-[9px] text-[15px] font-semibold transition-colors"
                            :class="form.dealType === deal.value ? 'bg-white text-[#152242] shadow-sm' : 'text-white hover:bg-white/15'">
                            {{ t(deal.label) }}
                        </button>
                    </div>
                    <button type="button" @click="postAnnoun"
                        class="h-[38px] px-[16px] rounded-[10px] border border-white/40 text-white text-[14px] font-semibold hover:bg-white hover:text-[#0468ff] transition-colors">
                        <font-awesome-icon :icon="['fas', 'plus']" class="mr-[6px]" />{{ t('Разместить объявление') }}
                    </button>
                </div>

                <form ref="card" @submit.prevent="submit"
                    class="relative bg-white rounded-[16px] shadow-[0_20px_50px_rgba(2,12,40,0.25)] p-[8px] grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-[1.25fr_1fr_1.1fr_1.6fr_auto] gap-[4px]">

                    <!-- Тип недвижимости -->
                    <div class="relative">
                        <button type="button" class="field" :class="{ 'field-open': panel === 'type' }" @click="toggle('type')" data-test="field-type">
                            <span class="field-label">{{ t('Тип недвижимости') }}</span>
                            <span class="field-value">{{ t(form.typeObject || 'Любой тип') }}</span>
                            <font-awesome-icon :icon="['fas', 'chevron-down']" class="field-chevron" :class="{ 'rotate-180': panel === 'type' }" />
                        </button>
                        <div v-if="panel === 'type'" class="panel w-[300px]">
                            <div class="grid grid-cols-2 p-[3px] rounded-[10px] bg-[#f1f5f9] mb-[10px]">
                                <button v-for="c of CATEGORIES" :key="c.value" type="button" @click="category = c.value"
                                    class="h-[32px] rounded-[8px] text-[14px] font-semibold transition-colors"
                                    :class="category === c.value ? 'bg-white text-[#152242] shadow-sm' : 'text-[#64748b]'">{{ t(c.label) }}</button>
                            </div>
                            <button v-for="opt of typeOptions" :key="opt || 'any'" type="button" @click="setType(opt)"
                                class="option" :class="{ 'option-active': form.typeObject === opt }">
                                <span class="radio" :class="{ 'radio-on': form.typeObject === opt }"></span>
                                {{ t(opt || t('Любой тип')) }}
                            </button>
                        </div>
                    </div>

                    <!-- Комнаты -->
                    <div class="relative">
                        <button type="button" class="field" :class="{ 'field-open': panel === 'rooms', 'opacity-50': !roomsApply }" :disabled="!roomsApply"
                            @click="toggle('rooms')" data-test="field-rooms" :title="roomsApply ? '' : t('Не применяется к этому типу')">
                            <span class="field-label">{{ t('Комнаты') }}</span>
                            <span class="field-value">{{ t(roomsLabel) }}</span>
                            <font-awesome-icon :icon="['fas', 'chevron-down']" class="field-chevron" :class="{ 'rotate-180': panel === 'rooms' }" />
                        </button>
                        <div v-if="panel === 'rooms'" class="panel w-[260px]">
                            <div class="text-[13px] text-[#64748b] mb-[10px]">{{ t('Можно выбрать несколько') }}</div>
                            <div class="flex gap-[8px]">
                                <button v-for="r of ROOMS" :key="r.value" type="button" @click="toggleRoom(r.value)" :aria-pressed="form.quantityRoom.includes(r.value)"
                                    class="flex-1 h-[40px] rounded-[10px] border text-[15px] font-semibold transition-colors"
                                    :class="form.quantityRoom.includes(r.value) ? 'bg-[#0468ff] border-[#0468ff] text-white' : 'border-[#e2e8f0] text-[#152242] hover:border-[#0468ff]'">
                                    {{ t(r.label) }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Цена -->
                    <div class="relative">
                        <button type="button" class="field" :class="{ 'field-open': panel === 'price' }" @click="toggle('price')" data-test="field-price">
                            <span class="field-label">{{ t('Цена, сомони') }}</span>
                            <span class="field-value">{{ t(priceLabel) }}</span>
                            <font-awesome-icon :icon="['fas', 'chevron-down']" class="field-chevron" :class="{ 'rotate-180': panel === 'price' }" />
                        </button>
                        <div v-if="panel === 'price'" class="panel w-[300px]">
                            <div class="flex items-center gap-[8px]">
                                <input :value="formatNumber(form.priceFrom)" @input="setPrice('priceFrom', $event)" inputmode="numeric" :placeholder="t('от')" data-test="price-from"
                                    class="w-full h-[40px] px-[12px] rounded-[10px] border border-[#e2e8f0] outline-none focus:border-[#0468ff] text-[15px]" />
                                <span class="text-[#94a3b8]">—</span>
                                <input :value="formatNumber(form.priceTo)" @input="setPrice('priceTo', $event)" inputmode="numeric" :placeholder="t('до')" data-test="price-to"
                                    class="w-full h-[40px] px-[12px] rounded-[10px] border border-[#e2e8f0] outline-none focus:border-[#0468ff] text-[15px]" />
                            </div>
                            <button v-if="form.priceFrom || form.priceTo" type="button" @click="form.priceFrom = form.priceTo = ''"
                                class="mt-[10px] text-[13px] text-[#0468ff] hover:underline">{{ t('Сбросить цену') }}</button>
                        </div>
                    </div>

                    <!-- Поиск по тексту объявления -->
                    <label class="field cursor-text">
                        <span class="field-label">{{ t('Поиск') }}</span>
                        <input v-model="form.q" @focus="panel = null" type="text" maxlength="255" autocomplete="off" data-test="field-q"
                            :placeholder="t('Район, улица или ключевое слово')"
                            class="w-full bg-transparent outline-none text-[15px] text-[#152242] placeholder:text-[#94a3b8] p-0 border-0" />
                    </label>

                    <div class="flex gap-[6px] p-[2px] sm:col-span-2 lg:col-span-1">
                        <button type="button" @click="openMap" :title="t('Показать на карте')" :aria-label="t('Показать на карте')"
                            class="h-[56px] w-[56px] shrink-0 rounded-[12px] bg-[#f1f5f9] text-[#152242] hover:bg-[#e2e8f0] transition-colors grid place-items-center">
                            <font-awesome-icon :icon="['fas', 'location-dot']" class="text-[18px]" />
                        </button>
                        <button type="submit" data-test="submit"
                            class="h-[56px] flex-1 lg:flex-none px-[26px] rounded-[12px] bg-[#0468ff] hover:bg-[#0353cc] text-white text-[16px] font-bold transition-colors">
                            <font-awesome-icon :icon="['fas', 'magnifying-glass']" class="mr-[8px]" />{{ t('Найти') }}
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <div class="bg-[#f4f6fb] pb-[56px]">
            <div class="max-w-[1160px] mx-auto px-[24px]">
                <!-- Новые объявления -->
                <section class="pt-[36px]">
                    <div class="flex items-end justify-between gap-[12px] mb-[16px]">
                        <div>
                            <h2 class="m-0 text-[26px] font-bold text-[#152242]">{{ t('Новые объявления') }}</h2>
                            <p class="text-[14px] text-[#64748b] mt-[4px] mb-0">{{ t('Только что опубликованные — проверены модератором') }}</p>
                        </div>
                        <NuxtLink to="/list-announ" class="shrink-0 h-[40px] px-[16px] rounded-[10px] border border-[#e2e8f0] bg-white text-[14px] font-semibold text-[#152242] flex items-center gap-[8px] no-underline hover:border-[#0468ff] hover:text-[#0468ff]">
                            {{ t('Все объявления') }} <font-awesome-icon :icon="['fas', 'arrow-right']" class="text-[12px]" />
                        </NuxtLink>
                    </div>

                    <div v-if="latestLoading" class="grid grid-cols-2 lg:grid-cols-4 gap-[16px]">
                        <div v-for="n in 4" :key="n" class="h-[290px] rounded-[18px] bg-[#e2e8f0] animate-pulse"></div>
                    </div>
                    <div v-else-if="!latest.length" class="rounded-[18px] bg-white border border-[#e8edf3] p-[32px] text-center">
                        <p class="text-[16px] font-semibold text-[#152242] m-0">{{ t('Пока нет опубликованных объявлений') }}</p>
                        <p class="text-[14px] text-[#64748b] mt-[4px] mb-[14px]">{{ t('Станьте первым — это займёт пару минут') }}</p>
                        <button type="button" @click="postAnnoun" class="h-[42px] px-[18px] rounded-[10px] bg-[#0468ff] text-white text-[14px] font-semibold">{{ t('Разместить объявление') }}</button>
                    </div>
                    <div v-else class="grid grid-cols-2 lg:grid-cols-4 gap-[16px]" data-test="home-latest">
                        <NuxtLink v-for="a of latest" :key="a.id" :to="`/show_announ-${a.id}`"
                            class="group rounded-[18px] bg-white border overflow-hidden no-underline transition-shadow hover:shadow-[0_12px_32px_rgba(15,23,42,0.1)]"
                            :class="a.promo_active === 'vip' ? 'promo-vip border-[#f5c451]' : 'border-[#e8edf3]'">
                            <div class="relative aspect-[4/3] bg-[#e2e8f0] overflow-hidden">
                                <img v-if="a.pictures?.[0]" :src="`${apiBase()}/api/image/medium/${a.pictures[0].pictures}`"
                                    class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-[1.04]" alt="" loading="lazy" />
                                <div v-else class="w-full h-full grid place-items-center text-[#94a3b8] text-[26px]"><font-awesome-icon :icon="['far', 'image']" /></div>
                                <div class="absolute left-[10px] top-[10px] flex gap-[6px]">
                                    <span v-if="a.promo_active === 'vip'" class="promo-badge-vip px-[7px] py-[2px] rounded-[8px] text-[11.5px] font-extrabold text-[#5b3a00] shadow-sm"><font-awesome-icon :icon="['fas', 'crown']" class="text-[9px] mr-[3px]" />VIP</span>
                                    <span v-else-if="a.promo_active === 'top'" class="px-[7px] py-[2px] rounded-[8px] bg-[#0468ff] text-[11.5px] font-extrabold text-white shadow-sm"><font-awesome-icon :icon="['fas', 'arrow-up']" class="text-[9px] mr-[3px]" />{{ t('ТОП') }}</span>
                                    <span class="px-[8px] py-[2px] rounded-[8px] bg-white text-[12px] font-semibold text-[#152242] shadow-sm">{{ dealLabel(a) }}</span>
                                </div>
                                <span v-if="a.pictures?.length > 1" class="absolute right-[10px] bottom-[10px] px-[7px] py-[1px] rounded-[8px] bg-black/55 text-white text-[11px]">
                                    <font-awesome-icon :icon="['fas', 'camera']" class="text-[10px]" /> {{ a.pictures.length }}
                                </span>
                            </div>
                            <div class="p-[12px]">
                                <div class="text-[18px] font-bold text-[#152242]">{{ formatNumber(a.price) }} <span class="text-[13px]">{{ t('сомони') }}</span><span class="text-[12px] font-normal text-[#64748b]">{{ priceSuffix(a) }}</span></div>
                                <div class="text-[14px] font-semibold text-[#0468ff] line-clamp-1 mt-[2px]">{{ a.title || t(a.type_object) }}</div>
                                <div class="text-[12.5px] text-[#64748b] truncate mt-[3px]">{{ announFacts(a) }}</div>
                                <div v-if="a.address" class="text-[12px] text-[#94a3b8] truncate mt-[4px]"><font-awesome-icon :icon="['fas', 'location-dot']" class="mr-[3px]" />{{ a.address }}</div>
                            </div>
                        </NuxtLink>
                    </div>
                </section>

                <!-- Промо -->
                <section class="pt-[44px] grid grid-cols-1 lg:grid-cols-2 gap-[16px]">
                    <div class="relative overflow-hidden rounded-[20px] p-[26px] bg-gradient-to-br from-[#0468ff] to-[#4f46e5] text-white">
                        <div class="absolute -right-[40px] -bottom-[60px] w-[220px] h-[220px] rounded-full bg-white/10"></div>
                        <div class="relative">
                            <div class="text-[13px] font-semibold text-white/75 uppercase tracking-[0.08em]">{{ t('Собственникам') }}</div>
                            <h3 class="m-0 mt-[6px] text-[22px] font-bold leading-tight">{{ t('Разместите объявление бесплатно') }}</h3>
                            <p class="text-[14.5px] text-white/85 mt-[8px] mb-[18px] max-w-[380px]">{{ t('Укажите адрес на карте, добавьте фото — ИИ поможет написать описание. После проверки модератором объявление увидят все.') }}</p>
                            <button type="button" @click="postAnnoun" class="h-[44px] px-[20px] rounded-[12px] bg-white text-[#0468ff] text-[15px] font-bold hover:bg-[#eef4ff]">
                                <font-awesome-icon :icon="['fas', 'plus']" class="mr-[6px]" />{{ t('Разместить') }}
                            </button>
                        </div>
                    </div>
                    <NuxtLink to="/yandex-map" class="relative overflow-hidden rounded-[20px] p-[26px] bg-white border border-[#e8edf3] no-underline group">
                        <div class="absolute right-[18px] top-1/2 -translate-y-1/2 w-[150px] h-[150px] rounded-full bg-[#eef4ff] grid place-items-center text-[#0468ff] text-[54px] transition-transform group-hover:scale-105">
                            <font-awesome-icon :icon="['fas', 'map-location-dot']" />
                        </div>
                        <div class="relative max-w-[60%]">
                            <div class="text-[13px] font-semibold text-[#64748b] uppercase tracking-[0.08em]">{{ t('Поиск по карте') }}</div>
                            <h3 class="m-0 mt-[6px] text-[22px] font-bold leading-tight text-[#152242]">{{ t('Смотрите объявления рядом с нужным местом') }}</h3>
                            <p class="text-[14.5px] text-[#64748b] mt-[8px] mb-[18px]">{{ t('Цены прямо на карте, фото — при наведении.') }}</p>
                            <span class="inline-flex items-center gap-[8px] h-[44px] px-[20px] rounded-[12px] bg-[#0468ff] text-white text-[15px] font-bold">{{ t('Открыть карту') }} <font-awesome-icon :icon="['fas', 'arrow-right']" class="text-[13px]" /></span>
                        </div>
                    </NuxtLink>
                </section>

                <!-- Быстрые ссылки -->
                <section class="pt-[44px]">
                    <h2 class="m-0 mb-[16px] text-[26px] font-bold text-[#152242]">{{ t('Быстрые ссылки') }}</h2>
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-[16px]">
                        <div v-for="g of QUICK_LINKS" :key="g.title" class="rounded-[18px] bg-white border border-[#e8edf3] p-[18px]">
                            <div class="text-[16px] font-bold text-[#152242] mb-[8px]">{{ t(g.title) }}</div>
                            <ul class="list-none m-0 p-0 flex flex-col">
                                <li v-for="l of g.links" :key="l.label">
                                    <NuxtLink :to="{ path: '/list-announ', query: l.query }"
                                        class="flex items-center justify-between py-[7px] text-[14.5px] text-[#334155] no-underline hover:text-[#0468ff]">
                                        {{ t(l.label) }} <font-awesome-icon :icon="['fas', 'chevron-right']" class="text-[10px] text-[#cbd5e1]" />
                                    </NuxtLink>
                                </li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- Как это работает -->
                <section class="pt-[44px]">
                    <h2 class="m-0 mb-[16px] text-[26px] font-bold text-[#152242]">{{ t('Как это работает') }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-[16px]">
                        <div v-for="(s, i) of STEPS" :key="s.title" class="rounded-[18px] bg-white border border-[#e8edf3] p-[20px]">
                            <div class="flex items-center gap-[12px]">
                                <span class="w-[42px] h-[42px] rounded-[12px] bg-[#eef4ff] text-[#0468ff] grid place-items-center text-[17px]"><font-awesome-icon :icon="['fas', s.icon]" /></span>
                                <span class="text-[13px] font-bold text-[#94a3b8]">{{ t('Шаг') }} {{ i + 1 }}</span>
                            </div>
                            <h3 class="m-0 mt-[12px] text-[17px] font-bold text-[#152242]">{{ t(s.title) }}</h3>
                            <p class="text-[14px] text-[#64748b] mt-[6px] mb-0 leading-[1.55]">{{ t(s.text) }}</p>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </main>
    
</template>
<script setup>
useHead({ title: () => t('Аренда и продажа недвижимости') })

const router = useRouter()
const route = useRoute()
const { direction } = getData()
const { showMadoal } = useSwitch()
const { user } = getUserByServer(getToken())
const { settings: site } = useSiteSettings()
const heroOverlay = computed(() => heroOverlayStyle(site.value.hero_overlay))

// Те же значения, что понимает страница списка (ListAnnouns) и сервер (App\Services\AnnounFilter)
const DEALS = [
    { label: 'Купить', value: 'Продажа' },
    { label: 'Снять', value: 'Аренда' },
    { label: 'Посуточно', value: 'Посуточно' },
]
const CATEGORIES = [{ label: 'Жилая', value: 'residential' }, { label: 'Коммерческая', value: 'commercial' }]
const RESIDENTIAL = ['Квартира', 'Квартира в Новостройке', 'Комната', 'Дом/Дача', 'Коттедж', 'Часть дома', 'Участок']
const COMMERCIAL = ['Офис', 'Здание', 'Торговая площадь', 'Склад', 'Коммерческая земля']
const ROOMS = [{ label: '1', value: '1' }, { label: '2', value: '2' }, { label: '3', value: '3' }, { label: '4+', value: '4' }]
// Комнатность бессмысленна для участков, комнат и коммерческих объектов
const NO_ROOMS = ['Комната', 'Участок', ...COMMERCIAL]

// Одно состояние формы на весь блок; заполняется из URL, чтобы «назад» со списка вернул прежний поиск
const asArray = (v) => (v === undefined || v === null || v === '' ? [] : Array.isArray(v) ? v : [v])
const form = reactive({
    dealType: DEALS.some((d) => d.value === route.query.dealType) ? route.query.dealType : 'Продажа',
    typeObject: route.query.typeObject ?? 'Квартира',
    quantityRoom: asArray(route.query.quantityRoom).map(String),
    priceFrom: String(route.query.priceFrom || route.query.min_price || ''),
    priceTo: String(route.query.priceTo || route.query.max_price || ''),
    q: route.query.q || '',
})
const category = ref(COMMERCIAL.includes(form.typeObject) ? 'commercial' : 'residential')

const typeOptions = computed(() => {
    const list = category.value === 'commercial' ? COMMERCIAL
        // новостройки только продаются
        : RESIDENTIAL.filter((t) => t !== 'Квартира в Новостройке' || form.dealType === 'Продажа')
    return ['', ...list]
})
const roomsApply = computed(() => !NO_ROOMS.includes(form.typeObject))

const roomsLabel = computed(() => {
    if (!roomsApply.value || !form.quantityRoom.length) return t('Любое количество')
    return t('{n} комн.', { n: [...form.quantityRoom].sort().map((r) => (r === '4' ? '4+' : r)).join(', ') })
})
const priceLabel = computed(() => {
    const { priceFrom: from, priceTo: to } = form
    if (from && to) return `${formatNumber(from)} – ${formatNumber(to)}`
    if (from) return t('от {n}', { n: formatNumber(from) })
    if (to) return t('до {n}', { n: formatNumber(to) })
    return t('Любая')
})

// --- выпадающие панели: открыта не больше одной, закрываются кликом мимо и Esc
const panel = ref(null)
const card = ref(null)
function toggle(name) {
    panel.value = panel.value === name ? null : name
}
function onDocumentDown(e) {
    if (panel.value && card.value && !card.value.contains(e.target)) panel.value = null
}
function onKey(e) {
    if (e.key === 'Escape') panel.value = null
}
onMounted(() => {
    document.addEventListener('mousedown', onDocumentDown)
    document.addEventListener('keydown', onKey)
})
onUnmounted(() => {
    document.removeEventListener('mousedown', onDocumentDown)
    document.removeEventListener('keydown', onKey)
})

function setDeal(value) {
    form.dealType = value
    if (value !== 'Продажа' && form.typeObject === 'Квартира в Новостройке') form.typeObject = 'Квартира'
}
function setType(value) {
    form.typeObject = value
    panel.value = null
}
function toggleRoom(value) {
    const i = form.quantityRoom.indexOf(value)
    i === -1 ? form.quantityRoom.push(value) : form.quantityRoom.splice(i, 1)
}
function setPrice(key, e) {
    form[key] = e.target.value.replace(/\D/g, '').slice(0, 12)
    // поле показывает число с пробелами — возвращаем отформатированное значение, иначе курсор «прыгает» по старому тексту
    e.target.value = formatNumber(form[key])
}

function buildQuery() {
    let { priceFrom, priceTo } = form
    // «от 500 000 до 100 000» — пользователь перепутал поля, просто меняем местами
    if (priceFrom && priceTo && Number(priceFrom) > Number(priceTo)) [priceFrom, priceTo] = [priceTo, priceFrom]
    const query = {
        dealType: form.dealType,
        typeObject: form.typeObject,
        quantityRoom: roomsApply.value ? [...form.quantityRoom].sort() : [],
        priceFrom,
        priceTo,
        q: form.q.trim(),
    }
    for (const key of Object.keys(query)) {
        const v = query[key]
        if (v === '' || (Array.isArray(v) && !v.length)) delete query[key]
    }
    return query
}
async function goTo(path) {
    panel.value = null
    const query = buildQuery()
    // запоминаем поиск в адресе главной — «назад» из списка вернёт заполненную форму
    await router.replace({ path: '/', query })
    router.push({ path, query })
}
const submit = () => goTo('/list-announ')
const openMap = () => goTo('/yandex-map')

const STEPS = [
    { icon: 'magnifying-glass', title: 'Найдите вариант', text: 'Фильтры, поиск по карте и Domly-помощник подберут жильё под ваш бюджет и район.' },
    { icon: 'comments', title: 'Напишите владельцу', text: 'Задайте вопросы в чате прямо на сайте или позвоните — контакты в объявлении.' },
    { icon: 'circle-check', title: 'Договоритесь о просмотре', text: 'Все объявления проходят проверку модератором, фото сверяет ИИ.' },
]

// Свежие объявления — тот же поиск, что у списка, без фильтров
const latest = ref([])
const latestLoading = ref(true)
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
        latestLoading.value = false
    }
})

function postAnnoun() {
    localStorage.removeItem('announ')
    if (user.value?.id) {
        router.push('/announ')
    } else {
        direction.value = '/announ'
        showMadoal.value = true
    }
}
</script>

<style scoped>
.hero {
    background: linear-gradient(135deg, #0b1f4d 0%, #0a3a9e 55%, #0468ff 100%);
}
.field {
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: center;
    width: 100%;
    height: 60px;
    padding: 0 40px 0 16px;
    border: 0;
    border-radius: 12px;
    background: transparent;
    text-align: left;
    transition: background-color .15s;
}
.field:not(:disabled):hover,
.field-open,
.field:focus-within {
    background: #f1f5f9;
}
.field-label {
    white-space: nowrap;
    font-size: 12px;
    color: #64748b;
    margin-bottom: 2px;
}
.field-value {
    font-size: 15px;
    font-weight: 600;
    color: #152242;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.field-chevron {
    position: absolute;
    right: 16px;
    top: 50%;
    margin-top: -6px;
    font-size: 12px;
    color: #94a3b8;
    transition: transform .15s;
}
.panel {
    position: absolute;
    top: calc(100% + 10px);
    left: 0;
    z-index: 30;
    max-width: calc(100vw - 32px);
    padding: 14px;
    border-radius: 14px;
    background: #fff;
    border: 1px solid #e8edf3;
    box-shadow: 0 16px 40px rgba(15, 23, 42, .18);
}
.option {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 9px 10px;
    border: 0;
    border-radius: 8px;
    background: none;
    font-size: 15px;
    color: #152242;
    text-align: left;
}
.option:hover {
    background: #f1f5f9;
}
.option-active {
    font-weight: 600;
    color: #0468ff;
}
.radio {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
    border-radius: 50%;
    border: 2px solid #cbd5e1;
}
.radio-on {
    border: 5px solid #0468ff;
}
</style>

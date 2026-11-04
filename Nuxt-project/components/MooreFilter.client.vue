<template>
  <div class="fixed inset-0 z-[300] bg-black/50 flex items-end sm:items-center justify-center" @click.self="close" data-test="more-filters">
    <div class="w-full sm:w-[640px] sm:max-w-[94vw] max-h-[88vh] sm:max-h-[85vh] flex flex-col bg-white rounded-t-[20px] sm:rounded-[18px] shadow-[0_-10px_40px_rgba(15,23,42,0.25)] overflow-hidden">
      <header class="flex items-center justify-between gap-[12px] px-[20px] h-[60px] border-b border-[#eef2f7] shrink-0">
        <h3 class="m-0 text-[19px] font-bold text-[#152242]">{{ t('Ещё фильтры') }}</h3>
        <button type="button" @click="close" :aria-label="t('Закрыть')" class="w-[36px] h-[36px] rounded-full grid place-items-center text-[#64748b] hover:bg-[#f1f5f9]">
          <font-awesome-icon :icon="['fas', 'xmark']" class="text-[18px]" />
        </button>
      </header>

      <div class="flex-1 overflow-y-auto px-[20px] py-[18px] flex flex-col gap-[22px]">
        <!-- Диапазоны «от — до» -->
        <div v-if="ranges.length" class="grid grid-cols-1 sm:grid-cols-2 gap-x-[16px] gap-y-[16px]">
          <div v-for="r of ranges" :key="r.key">
            <div class="text-[14px] font-semibold text-[#152242] mb-[6px]">{{ t(r.label) }}<span v-if="r.unit" class="font-normal text-[#94a3b8]">, {{ t(r.unit) }}</span></div>
            <div class="flex items-center gap-[8px]">
              <input :value="f[r.key + 'From']" @input="setNumber(r.key + 'From', $event)" inputmode="numeric" :placeholder="t('от')"
                class="flex-1 min-w-0 h-[42px] px-[12px] rounded-[10px] border border-[#e2e8f0] bg-white text-[15px] text-[#152242] outline-none focus:border-[#0468ff]" />
              <span class="text-[#94a3b8]">—</span>
              <input :value="f[r.key + 'To']" @input="setNumber(r.key + 'To', $event)" inputmode="numeric" :placeholder="t('до')"
                class="flex-1 min-w-0 h-[42px] px-[12px] rounded-[10px] border border-[#e2e8f0] bg-white text-[15px] text-[#152242] outline-none focus:border-[#0468ff]" />
            </div>
          </div>
        </div>

        <!-- Варианты чипами -->
        <div v-for="g of groups" :key="g.key">
          <div class="text-[14px] font-semibold text-[#152242] mb-[8px]">{{ t(g.label) }}</div>
          <div class="flex flex-wrap gap-[8px]">
            <button v-for="o of g.options" :key="o.value" type="button" @click="toggle(g, o.value)"
              class="h-[38px] px-[14px] rounded-full border text-[14px] transition-colors"
              :class="isOn(g, o.value) ? 'border-[#0468ff] bg-[#eef4ff] text-[#0468ff] font-semibold' : 'border-[#e2e8f0] bg-white text-[#334155] hover:border-[#94a3b8]'">
              <font-awesome-icon v-if="isOn(g, o.value)" :icon="['fas', 'check']" class="text-[11px] mr-[4px]" />{{ t(o.label) }}
            </button>
          </div>
        </div>
      </div>

      <footer class="flex items-center gap-[10px] px-[20px] py-[12px] border-t border-[#eef2f7] shrink-0">
        <button type="button" @click="clearAll" class="h-[46px] px-[16px] rounded-[12px] text-[15px] font-semibold text-[#334155] hover:bg-[#f1f5f9]">
          {{ t('Сбросить') }}<span v-if="selectedCount"> ({{ selectedCount }})</span>
        </button>
        <button type="button" @click="apply" data-test="more-filters-apply"
          class="flex-1 h-[46px] rounded-[12px] bg-[#0468ff] hover:bg-[#0353cc] text-white text-[15px] font-bold">{{ t('Показать объявления') }}</button>
      </footer>
    </div>
  </div>
</template>

<script setup>
// Дополнительные фильтры списка. Стартовые значения и тип объекта — из sessionStorage.filter (кладёт ListAnnouns),
// результат отдаём в getAnnouns — ListAnnouns переносит его в адрес страницы.
const props = defineProps({ getAnnouns: { type: Function, required: true } })
const { showFIlterModal } = useSwitch()

const start = JSON.parse(sessionStorage.getItem('filter') || '{}') || {}
const RANGE_KEYS = ['totalArea', 'kitchenArea', 'area', 'houseArea', 'landArea', 'floor', 'floorHouse', 'year']
const LIST_KEYS = ['repair', 'typeHome', 'parking', 'condition']
const asArray = (v) => (v === undefined || v === null || v === '' ? [] : Array.isArray(v) ? v : [v])

const f = reactive({})
for (const k of RANGE_KEYS) {
  f[k + 'From'] = start[k + 'From'] ?? ''
  f[k + 'To'] = start[k + 'To'] ?? ''
}
for (const k of LIST_KEYS) f[k] = asArray(start[k]).map(String)
f.balcon = start.balcon || ''

const FLATS = ['Квартира', 'Квартира в Новостройке', 'Комната']
const HOUSES = ['Дом/Дача', 'Коттедж', 'Часть дома', 'Участок']
const COMMERCIAL = ['Офис', 'Здание', 'Торговая площадь', 'Склад', 'Коммерческая земля']
const kind = FLATS.includes(start.typeObject) ? 'flat'
  : HOUSES.includes(start.typeObject) ? 'house'
  : COMMERCIAL.includes(start.typeObject) || start.typeRealEstate === 'Коммерческая' ? 'commercial'
  : 'any'
const isRent = ['Аренда', 'Посуточно'].includes(start.dealType)

const ranges = computed(() => ({
  flat: [
    { key: 'totalArea', label: 'Общая площадь', unit: 'м²' }, { key: 'kitchenArea', label: 'Кухня', unit: 'м²' },
    { key: 'area', label: 'Жилая площадь', unit: 'м²' }, { key: 'floor', label: 'Этаж' },
    { key: 'floorHouse', label: 'Этажей в доме' }, { key: 'year', label: 'Год постройки' },
  ],
  house: start.typeObject === 'Участок'
    ? [{ key: 'landArea', label: 'Участок', unit: 'сот.' }]
    : [{ key: 'houseArea', label: 'Площадь дома', unit: 'м²' }, { key: 'landArea', label: 'Участок', unit: 'сот.' }, { key: 'year', label: 'Год постройки' }],
  commercial: [{ key: 'totalArea', label: 'Площадь', unit: 'м²' }, { key: 'floor', label: 'Этаж' }, { key: 'year', label: 'Год постройки' }],
  any: [{ key: 'floor', label: 'Этаж' }, { key: 'year', label: 'Год постройки' }],
}[kind]))

const opts = (list) => list.map((o) => (typeof o === 'string' ? { value: o, label: o } : o))
const conditions = ref([])
const groups = computed(() => {
  const list = []
  if (kind !== 'commercial' && start.typeObject !== 'Участок') {
    list.push({ key: 'repair', label: 'Ремонт', options: REPAIR_OPTIONS })
    list.push({ key: 'typeHome', label: kind === 'house' ? 'Материал стен' : 'Тип дома',
      options: opts(['Кирпичный', 'Монолитный', 'Монолитно-кирпичный', 'Панельный', 'Блочный', 'Деревянный', 'Сталинский']) })
  }
  if (kind === 'flat') list.push({ key: 'balcon', label: 'Балкон или лоджия', single: true, options: [{ value: 'есть', label: 'Есть' }, { value: 'нет', label: 'Нет' }] })
  if (start.typeObject !== 'Участок') list.push({ key: 'parking', label: 'Парковка', options: opts(['Наземная', 'Подземная', 'Многоуровневая', 'На крыше']) })
  if (isRent && conditions.value.length) list.push({ key: 'condition', label: 'Удобства', options: conditions.value })
  return list
})

onMounted(async () => {
  if (!isRent) return
  try {
    const res = await fetch(`${apiBase()}/api/conditions`, { headers: { Accept: 'application/json' } })
    const list = res.ok ? await res.json() : []
    // «можно с детьми/животными» — это условия владельца, а не удобства квартиры
    conditions.value = list.filter((c) => !['Можно с детьми', 'Можно с животными'].includes(c.name)).map((c) => ({ value: String(c.id), label: c.name }))
  } catch (e) { /* без списка удобств остальные фильтры работают */ }
})

const isOn = (g, v) => (g.single ? f[g.key] === v : f[g.key].includes(v))
function toggle(g, v) {
  if (g.single) f[g.key] = f[g.key] === v ? '' : v
  else f[g.key] = isOn(g, v) ? f[g.key].filter((x) => x !== v) : [...f[g.key], v]
}
function setNumber(key, e) {
  f[key] = e.target.value.replace(/\D/g, '').slice(0, 6)
  e.target.value = f[key]
}

const selectedCount = computed(() => Object.values(f).filter((v) => (Array.isArray(v) ? v.length : v !== '')).length)

function clearAll() {
  for (const k of Object.keys(f)) f[k] = Array.isArray(f[k]) ? [] : ''
}
function apply() {
  showFIlterModal.value = false
  props.getAnnouns({ ...f })
}
function close() {
  showFIlterModal.value = false
}
</script>

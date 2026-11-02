<template>
  <WzShell title="Что вы хотите разместить?" subtitle="Выберите тип сделки и объект — от этого зависят следующие шаги" :error="error" @next="next" :hide-back="true">
    <div>
      <span class="wz-label">Тип сделки</span>
      <div class="grid grid-cols-2 gap-[10px] max-w-[460px]">
        <button v-for="o of DEALS" :key="o.value" type="button" @click="setDeal(o.value)" :data-test="`deal-${o.value}`"
          class="flex items-center gap-[12px] p-[14px] rounded-[14px] border-2 text-left transition-colors"
          :class="d.rent === o.value ? 'border-[#0468ff] bg-[#eef4ff]' : 'border-[#e8edf3] hover:border-[#cbd5e1]'">
          <span class="w-[42px] h-[42px] rounded-[12px] grid place-items-center text-[18px]" :class="d.rent === o.value ? 'bg-[#0468ff] text-white' : 'bg-[#f1f5f9] text-[#475569]'">
            <font-awesome-icon :icon="['fas', o.icon]" />
          </span>
          <span>
            <span class="block text-[16px] font-bold text-[#152242]">{{ o.label }}</span>
            <span class="block text-[12.5px] text-[#64748b]">{{ o.hint }}</span>
          </span>
        </button>
      </div>
    </div>

    <WzChoice v-if="d.rent === 'Аренда'" label="Срок аренды" :model-value="d.typeRent" @update:model-value="setRentType"
      :options="['Длительно', 'Несколько месяцев', 'Посуточно']" :invalid="tried && !d.typeRent" error="Выберите срок аренды" />

    <WzChoice v-if="d.rent && (d.rent === 'Продажа' || d.typeRent)" label="Тип недвижимости" :model-value="d.Estate" @update:model-value="setEstate"
      :options="estateOptions" :invalid="tried && !d.Estate" error="Выберите тип недвижимости"
      :hint="d.typeRent === 'Посуточно' ? 'Посуточно сдают только жилую недвижимость' : ''" />

    <div v-if="d.Estate">
      <span class="wz-label">Объект</span>
      <div class="grid grid-cols-2 sm:grid-cols-3 gap-[10px]">
        <button v-for="o of objectOptions" :key="o.value" type="button" @click="d.objects = o.value" :data-test="`object-${o.value}`"
          class="flex flex-col items-start gap-[8px] p-[14px] rounded-[14px] border-2 text-left transition-colors min-h-[96px]"
          :class="d.objects === o.value ? 'border-[#0468ff] bg-[#eef4ff]' : tried && !validObject ? 'border-[#fecaca]' : 'border-[#e8edf3] hover:border-[#cbd5e1]'">
          <font-awesome-icon :icon="['fas', o.icon]" class="text-[22px]" :class="d.objects === o.value ? 'text-[#0468ff]' : 'text-[#64748b]'" />
          <span class="text-[14.5px] font-semibold text-[#152242] leading-tight">{{ o.label }}</span>
        </button>
      </div>
      <p v-if="tried && !validObject" class="wz-err">Выберите объект</p>
    </div>
  </WzShell>
</template>

<script setup>
const d = useAnnounDraft()
const route = useRoute()
const tried = ref(false)

const DEALS = [
  { value: 'Продажа', label: 'Продать', hint: 'Квартира, дом, участок', icon: 'tag' },
  { value: 'Аренда', label: 'Сдать', hint: 'Надолго или посуточно', icon: 'key' },
]
const RESIDENTIAL = [
  { value: 'Квартира', label: 'Квартира', icon: 'building' },
  { value: 'Квартира в Новостройке', label: 'Новостройка', icon: 'city', saleOnly: true },
  { value: 'Комната', label: 'Комната', icon: 'door-open' },
  { value: 'Дом/Дача', label: 'Дом / дача', icon: 'house', daily: true },
  { value: 'Коттедж', label: 'Коттедж', icon: 'house-chimney' },
  { value: 'Часть дома', label: 'Часть дома', icon: 'house-user' },
  { value: 'Участок', label: 'Участок', icon: 'tree', saleOnly: true },
]
const COMMERCIAL = [
  { value: 'Офис', label: 'Офис', icon: 'briefcase' },
  { value: 'Здание', label: 'Здание', icon: 'building-columns' },
  { value: 'Торговая площадь', label: 'Торговая площадь', icon: 'store' },
  { value: 'Склад', label: 'Склад', icon: 'warehouse' },
  { value: 'Коммерческая земля', label: 'Коммерческая земля', icon: 'map-location-dot' },
]
const DAILY = ['Квартира', 'Комната', 'Дом/Дача']

const estateOptions = computed(() => (d.value.typeRent === 'Посуточно' ? ['Жилая'] : ['Жилая', 'Коммерческая']))
const objectOptions = computed(() => {
  if (d.value.Estate === 'Коммерческая') return COMMERCIAL
  if (d.value.rent === 'Аренда' && d.value.typeRent === 'Посуточно') return RESIDENTIAL.filter((o) => DAILY.includes(o.value))
  return RESIDENTIAL.filter((o) => !o.saleOnly || d.value.rent === 'Продажа')
})
const validObject = computed(() => objectOptions.value.some((o) => o.value === d.value.objects))
const error = computed(() => (tried.value && !canGo.value ? 'Заполните отмеченные поля' : ''))
const canGo = computed(() => d.value.rent && (d.value.rent === 'Продажа' || d.value.typeRent) && d.value.Estate && validObject.value)

function setDeal(v) {
  d.value.rent = v
  // у продажи нет срока аренды — иначе на сервер уходил лишний rental_type
  if (v === 'Продажа') delete d.value.typeRent
  if (!validObject.value) delete d.value.objects
}
function setRentType(v) {
  d.value.typeRent = v
  if (v === 'Посуточно') d.value.Estate = 'Жилая'
  if (!validObject.value) delete d.value.objects
}
function setEstate(v) {
  d.value.Estate = v
  if (!validObject.value) delete d.value.objects
}

function next() {
  tried.value = true
  if (!canGo.value) return
  navigateTo(announStepNav(d.value, route.path).next)
}

// Справочник удобств (мебель, техника, «можно с детьми»…) — нужен на следующих шагах; сервер добавит недостающие
onMounted(() => {
  fetch(`${apiBase()}/api/create/furniture`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(['Можно с детьми', 'Можно с животными', 'Холодильник', 'Посудамоечная машина', 'Стиральная машина',
      'В комнатах', 'На кухне', 'Кондиционер', 'Телевизор', 'Интернет', 'Телефон', 'Ванна', 'Душевая кабина', 'Баня', 'Гараж', 'Бассейн']),
  }).catch(() => {})
})
</script>

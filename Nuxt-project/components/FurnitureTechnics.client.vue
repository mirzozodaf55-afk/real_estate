<template>
  <WzShell :title="kind === 'flat' ? 'Мебель и техника' : kind === 'land' ? 'Об участке' : 'Дом и участок'"
    :subtitle="kind === 'flat' ? 'Отметьте, что есть в квартире, — арендаторы ищут по этим пунктам' : 'Площадь и состояние — главное, на что смотрят покупатели'"
    :error="error" @next="next">

    <template v-if="kind === 'flat'">
      <p v-if="loadingConditions" class="text-[14px] text-[#94a3b8] m-0"><font-awesome-icon :icon="['fas', 'spinner']" spin-pulse /> Загружаем список…</p>
      <WzChoice v-for="g of groups" :key="g.title" multiple :label="g.title" :options="g.options"
        :model-value="d.condition || []" @update:model-value="d.condition = $event" />
      <p class="wz-hint -mt-[10px]">Можно ничего не отмечать — тогда в объявлении этого раздела не будет.</p>
    </template>

    <template v-else>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-[16px]">
        <WzNumber v-model="d.landArea" label="Площадь участка" suffix="сот." :max="6" width="w-full"
          :invalid="tried && !d.landArea" error="Укажите площадь участка" hint="1 сотка = 100 м²" />
        <WzNumber v-if="kind === 'house'" v-model="d.homeArea" label="Площадь дома" suffix="м²" :max="5" width="w-full"
          :invalid="tried && !d.homeArea" error="Укажите площадь дома" />
        <WzNumber v-if="kind === 'house'" v-model="d.quantityBedroom" label="Спален" :max="2" width="w-full"
          :invalid="tried && !d.quantityBedroom" error="Укажите количество спален" />
      </div>

      <WzChoice v-model="d.landCategry" label="Категория земли" :options="LAND_CATEGORIES"
        :invalid="tried && !d.landCategry" error="Выберите категорию земли" />

      <WzChoice v-if="kind === 'house'" v-model="d.conditionHome" label="Состояние дома" :options="HOME_CONDITIONS"
        :invalid="tried && !d.conditionHome" error="Выберите состояние дома" />

      <WzChoice v-model="d.electricity" label="Электричество" :options="YES_NO"
        :invalid="tried && !d.electricity" error="Укажите, есть ли электричество" />

      <WzChoice v-if="kind === 'house' && yardOptions.length" multiple label="На участке" :options="yardOptions"
        :model-value="d.condition || []" @update:model-value="d.condition = $event" hint="Необязательно" />
    </template>
  </WzShell>
</template>

<script setup>
const d = useAnnounDraft()
const route = useRoute()
const tried = ref(false)
const kind = computed(() => announKind(d.value))

const YES_NO = [{ value: 'есть', label: 'Есть' }, { value: 'нет', label: 'Нет' }]
const LAND_CATEGORIES = ['Земли населённых пунктов', 'Земли сельхозназначения', 'Другое']
// значения — как в базе (в т.ч. «достроит»), подписи — грамотно
const HOME_CONDITIONS = [
  { value: 'Можно жить', label: 'Можно жить' },
  { value: 'Нужен ремонт', label: 'Нужен ремонт' },
  { value: 'Нужно достроит', label: 'Нужно достроить' },
  { value: 'Нужен капремонт или под снос', label: 'Капремонт или под снос' },
]

// Удобства приходят из справочника /api/conditions (id + название); в черновике — массив id (d.condition)
const conditions = ref([])
const loadingConditions = ref(true)
const LABELS = { 'На кухне': 'Мебель на кухне', 'В комнатах': 'Мебель в комнатах' }
const pick = (names) => conditions.value.filter((c) => names.includes(c.name)).map((c) => ({ value: c.id, label: LABELS[c.name] || c.name }))
const groups = computed(() => [
  { title: 'Мебель', options: pick(['На кухне', 'В комнатах']) },
  { title: 'Техника', options: pick(['Холодильник', 'Стиральная машина', 'Посудамоечная машина', 'Кондиционер', 'Телевизор']) },
  { title: 'Ванная комната', options: pick(['Ванна', 'Душевая кабина']) },
  { title: 'Связь', options: pick(['Интернет', 'Телефон']) },
].filter((g) => g.options.length))
const yardOptions = computed(() => pick(['Баня', 'Гараж', 'Бассейн']))

const valid = computed(() => {
  if (kind.value === 'flat') return true
  if (!d.value.landArea || !d.value.landCategry || !d.value.electricity) return false
  if (kind.value === 'house' && (!d.value.homeArea || !d.value.quantityBedroom || !d.value.conditionHome)) return false
  return true
})
const error = computed(() => (tried.value && !valid.value ? 'Заполните отмеченные поля' : ''))

function next() {
  tried.value = true
  if (valid.value) navigateTo(announStepNav(d.value, route.path).next)
}

onMounted(async () => {
  try {
    const res = await fetch(`${apiBase()}/api/conditions`, { headers: { Accept: 'application/json' } })
    conditions.value = res.ok ? await res.json() : []
  } catch (e) {
    conditions.value = []
  } finally {
    loadingConditions.value = false
  }
})
</script>

<template>
  <WzShell :title="kind === 'land' ? 'Где находится участок?' : 'Где находится объект?'"
    subtitle="Найдите адрес или отметьте точку на карте — покупатели увидят объявление на карте и в поиске по адресу"
    :error="error" @next="next">
    <WzLocationPicker :draft="d" :invalid="tried && !hasPoint" />

    <template v-if="kind !== 'land'">
      <div class="wz-section-title">{{ kind === 'flat' ? 'О доме' : 'О доме и постройке' }}</div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-[16px]">
        <WzNumber v-if="kind === 'flat'" v-model="d.floor" label="Этаж" :max="3" width="w-full"
          :invalid="tried && (!d.floor || floorTooHigh)" :error="floorTooHigh ? 'Больше этажности дома' : 'Укажите этаж'" />
        <WzNumber v-model="d.floorHouse" :label="kind === 'flat' ? 'Этажей в доме' : 'Количество этажей'" :max="3" width="w-full"
          :invalid="tried && !d.floorHouse" error="Укажите этажность" />
        <WzNumber v-model="d.year" label="Год постройки" :max="4" width="w-full" placeholder="например, 2015"
          :invalid="tried && !yearOk" :error="`Год от 1900 до ${currentYear}`" />
      </div>

      <WzChoice v-model="d.selectType" :label="kind === 'flat' ? 'Тип дома' : 'Материал стен'" :options="HOUSE_TYPES"
        :invalid="tried && !d.selectType" error="Выберите тип дома" />
    </template>
  </WzShell>
</template>

<script setup>
const d = useAnnounDraft()
const route = useRoute()
const tried = ref(false)
const currentYear = new Date().getFullYear()
const HOUSE_TYPES = ['Кирпичный', 'Монолитный', 'Монолитно-кирпичный', 'Панельный', 'Блочный', 'Деревянный', 'Сталинский']

const kind = computed(() => announKind(d.value))
const hasPoint = computed(() => !!(d.value.coord_x && d.value.coord_y))
const floorTooHigh = computed(() => d.value.floor && d.value.floorHouse && +d.value.floor > +d.value.floorHouse)
const yearOk = computed(() => d.value.year >= 1900 && d.value.year <= currentYear)

const valid = computed(() => {
  if (!hasPoint.value) return false
  if (kind.value === 'land') return true
  if (!d.value.floorHouse || !yearOk.value || !d.value.selectType) return false
  if (kind.value === 'flat' && (!d.value.floor || floorTooHigh.value)) return false
  return true
})
const error = computed(() => (tried.value && !valid.value ? 'Заполните отмеченные поля' : ''))

function next() {
  tried.value = true
  if (valid.value) navigateTo(announStepNav(d.value, route.path).next)
}
</script>

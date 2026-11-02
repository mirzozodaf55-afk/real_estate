<template>
  <WzShell :title="d.objects === 'Комната' ? 'Комната и квартира' : 'Комнаты и площадь'"
    subtitle="Площадь указывайте как в документах — по ней ищут чаще всего" :error="error" @next="next">
    <WzChoice v-model="d.quantityRoom" :label="d.objects === 'Комната' ? 'Комнат в квартире' : 'Количество комнат'"
      :options="ROOMS" :invalid="tried && !d.quantityRoom" error="Выберите количество комнат" />

    <div>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-[16px]">
        <WzNumber v-model="d.generalArea" label="Общая площадь" suffix="м²" :max="5" width="w-full"
          :invalid="tried && (!d.generalArea || areaConflict)" :error="areaConflict ? '' : 'Укажите общую площадь'" />
        <WzNumber v-model="d.liveArea" label="Жилая площадь" suffix="м²" :max="5" width="w-full"
          :invalid="tried && (!d.liveArea || areaConflict)" :error="areaConflict ? '' : 'Укажите жилую площадь'" />
        <WzNumber v-model="d.kitchenArea" label="Площадь кухни" suffix="м²" :max="4" width="w-full"
          :invalid="tried && (!d.kitchenArea || areaConflict)" :error="areaConflict ? '' : 'Укажите площадь кухни'" />
      </div>
      <p v-if="areaConflict" class="wz-err">Жилая площадь и кухня вместе не могут быть больше общей ({{ d.liveArea }} + {{ d.kitchenArea }} &gt; {{ d.generalArea }} м²)</p>
      <p v-else class="wz-hint">Общая = жилая + кухня + коридор, санузел, балкон</p>
    </div>
  </WzShell>
</template>

<script setup>
const d = useAnnounDraft()
const route = useRoute()
const tried = ref(false)
const ROOMS = [1, 2, 3, 4, 5, { value: 6, label: '6+' }]

const areaConflict = computed(() => d.value.generalArea && d.value.liveArea && d.value.kitchenArea
  && +d.value.liveArea + +d.value.kitchenArea > +d.value.generalArea)
const valid = computed(() => d.value.quantityRoom && d.value.generalArea && d.value.liveArea && d.value.kitchenArea && !areaConflict.value)
const error = computed(() => (tried.value && !valid.value ? 'Заполните отмеченные поля' : ''))

function next() {
  tried.value = true
  if (valid.value) navigateTo(announStepNav(d.value, route.path).next)
}
</script>

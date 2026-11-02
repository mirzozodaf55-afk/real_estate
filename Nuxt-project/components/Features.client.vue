<template>
  <WzShell title="Ремонт и удобства дома" subtitle="Эти параметры чаще всего выбирают в фильтрах поиска" :error="error" @next="next">
    <WzChoice v-model="d.repair" label="Ремонт" :options="REPAIR_OPTIONS" :invalid="tried && !d.repair" error="Выберите ремонт" />

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-[22px]">
      <WzChoice v-model="d.balcon" label="Балкон или лоджия" :options="YES_NO" :invalid="tried && !d.balcon" error="Укажите, есть ли балкон" />
      <WzChoice v-model="d.elevator" label="Лифт" :options="YES_NO" :invalid="tried && !d.elevator" error="Укажите, есть ли лифт" />
    </div>

    <WzChoice v-model="d.parking" label="Парковка" :options="PARKING" :invalid="tried && !d.parking" error="Выберите парковку" />
  </WzShell>
</template>

<script setup>
const d = useAnnounDraft()
const route = useRoute()
const tried = ref(false)
// значения — как в базе и фильтрах поиска («есть»/«нет» строчными)
const YES_NO = [{ value: 'есть', label: 'Есть' }, { value: 'нет', label: 'Нет' }]
const PARKING = ['Наземная', 'Подземная', 'Многоуровневая', 'На крыше', { value: 'Нет', label: 'Нет парковки' }]

const valid = computed(() => d.value.repair && d.value.balcon && d.value.elevator && d.value.parking)
const error = computed(() => (tried.value && !valid.value ? 'Заполните отмеченные поля' : ''))

function next() {
  tried.value = true
  if (valid.value) navigateTo(announStepNav(d.value, route.path).next)
}
</script>

<template>
  <WzShell :title="`${d.objects || 'Коммерческая недвижимость'}: всё об объекте`" subtitle="Заполните параметры, добавьте фото и цену — после проверки модератором объявление появится в поиске"
    :error="error" :loading="sending" final @next="place">

    <WzLocationPicker :draft="d" :invalid="tried && !hasPoint" />

    <div class="wz-section-title">Параметры</div>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-[16px]">
      <WzNumber v-if="!isLand" v-model="d.generalArea" :label="isBuilding ? 'Площадь' : 'Общая площадь'" suffix="м²" :max="6" width="w-full"
        :invalid="tried && !d.generalArea" error="Укажите площадь" />
      <WzNumber v-if="!isLand" v-model="d.floor" :label="isBuilding ? 'Этажей в здании' : 'Этаж'" :max="3" width="w-full"
        :invalid="tried && (!d.floor || floorTooHigh)" :error="floorTooHigh ? 'Больше этажности здания' : 'Укажите этаж'" />
      <WzNumber v-if="!isLand && !isBuilding" v-model="d.floorHouse" label="Этажей в здании" :max="3" width="w-full"
        :invalid="tried && !d.floorHouse" error="Укажите этажность" />
      <WzNumber v-if="!isLand" v-model="d.CeilingHeight" label="Высота потолков" suffix="м" :max="2" width="w-full" hint="Необязательно" />
      <WzNumber v-if="showSeats" v-model="d.numberSeats" label="Рабочих мест" :max="5" width="w-full" hint="Необязательно" />
      <WzNumber v-model="d.landArea" :label="isLand ? 'Площадь участка' : 'Участок'" suffix="сот." :max="6" width="w-full"
        :invalid="tried && isLand && !d.landArea" error="Укажите площадь участка" :hint="isLand ? '' : 'Необязательно'" />
    </div>

    <template v-if="!isLand">
      <WzChoice v-if="showLayout" v-model="d.layout" label="Планировка" :options="['Открытая', 'Кабинетная', 'Коридорная', 'Смешанная']" />
      <WzChoice v-model="d.State" :label="isBuilding ? 'Ремонт' : 'Состояние'" :options="stateOptions" />
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-[22px]">
        <WzChoice v-model="d.Furniture" label="Мебель" :options="['Есть', 'Нет']" />
        <WzChoice v-if="isBuilding" v-model="d.elevator" label="Лифт" :options="['Есть', 'Нет']" />
      </div>
      <WzChoice v-model="d.parking" label="Парковка" :options="['Наземная', 'Подземная', 'Многоуровневая', 'На крыше', 'Нет']" />
    </template>

    <template v-if="!isLand && !isBuilding">
      <div class="wz-section-title">О здании</div>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-[16px]">
        <WzNumber v-model="d.year" label="Год постройки" :max="4" width="w-full" hint="Необязательно" />
        <WzNumber v-model="d.buildingArea" label="Площадь здания" suffix="м²" :max="7" width="w-full" hint="Необязательно" />
      </div>
    </template>

    <WzChoice v-if="showInfrastructure && infrastructure.length" multiple label="Рядом есть" :options="infrastructure"
      :model-value="d.infrastructure || []" @update:model-value="d.infrastructure = $event" hint="Необязательно" />

    <div class="wz-section-title">Фото</div>
    <Pictures :invalid="tried && !images.length" />

    <div class="wz-section-title">Описание</div>
    <WzAiWrite :draft="d" hint="ИИ напишет текст по параметрам выше — проверьте и поправьте" />
    <label class="block">
      <span class="wz-label">Заголовок</span>
      <input v-model="d.title" maxlength="150" class="wz-input" :class="{ 'wz-input--bad': tried && !titleOk }" placeholder="Например: Офис 120 м² в бизнес-центре, первая линия" />
      <span v-if="tried && !titleOk" class="wz-err block">Минимум 6 символов</span>
    </label>
    <label class="block">
      <span class="wz-label">Описание</span>
      <textarea v-model="d.description" rows="6" maxlength="3000" class="wz-input" :class="{ 'wz-input--bad': tried && !descriptionOk }"
        placeholder="Расположение, отделка, коммуникации, для какого бизнеса подойдёт"></textarea>
      <span v-if="tried && !descriptionOk" class="wz-err block">Напишите хотя бы пару предложений — от 15 символов</span>
    </label>
    <label class="block">
      <span class="wz-label">Видео <span class="font-normal text-[#94a3b8]">— необязательно</span></span>
      <input v-model.trim="d.linkVideo" type="url" class="wz-input" :class="{ 'wz-input--bad': tried && !videoOk }" placeholder="https://youtube.com/…" />
      <span v-if="tried && !videoOk" class="wz-err block">Ссылка должна начинаться с https://</span>
    </label>

    <div class="wz-section-title">Цена</div>
    <WzNumber v-model="d.price" :label="d.rent === 'Аренда' ? 'Аренда в месяц' : 'Цена'" suffix="сомони" :max="10" width="max-w-[280px]"
      :invalid="tried && !priceOk" error="Укажите цену — от 100 сомони" />
  </WzShell>
</template>

<script setup>
const d = useAnnounDraft()
const { images } = getData()
const tried = ref(false)
const sending = ref(false)
const serverError = ref('')

const isLand = computed(() => d.value.objects === 'Коммерческая земля')
const isBuilding = computed(() => d.value.objects === 'Здание')
const isWarehouse = computed(() => d.value.objects === 'Склад')
const showLayout = computed(() => !isLand.value && !isBuilding.value && !isWarehouse.value)
const showSeats = computed(() => !isLand.value && !isBuilding.value)
const showInfrastructure = computed(() => !isLand.value && !isBuilding.value && !isWarehouse.value)
const stateOptions = computed(() => {
  if (isBuilding.value) return ['Типовой', 'Дизайнерский', 'Под чистовую отделку', 'Требуется ремонт']
  if (isWarehouse.value) return ['Типовой', 'Под чистовую отделку', 'Требуется косметический ремонт', 'Требуется капитальный ремонт']
  return ['Офисная отделка', 'Под чистовую отделку', 'Требуется косметический ремонт', 'Требуется капитальный ремонт']
})

const hasPoint = computed(() => !!(d.value.coord_x && d.value.coord_y))
const floorTooHigh = computed(() => !isBuilding.value && d.value.floor && d.value.floorHouse && +d.value.floor > +d.value.floorHouse)
const titleOk = computed(() => (d.value.title || '').trim().length >= 6)
const descriptionOk = computed(() => (d.value.description || '').trim().length >= 15)
const videoOk = computed(() => !d.value.linkVideo || /^https?:\/\/\S+\.\S+/.test(d.value.linkVideo))
const priceOk = computed(() => d.value.price >= 100)

const valid = computed(() => {
  if (!hasPoint.value || !images.value.length || !titleOk.value || !descriptionOk.value || !videoOk.value || !priceOk.value) return false
  if (isLand.value) return !!d.value.landArea
  if (!d.value.generalArea || !d.value.floor || floorTooHigh.value) return false
  if (!isBuilding.value && !d.value.floorHouse) return false
  return true
})
const error = computed(() => serverError.value || (tried.value && !valid.value ? 'Заполните отмеченные поля' : ''))

// Инфраструктура рядом — справочник с id; в черновике массив id (d.infrastructure)
const infrastructure = ref([])
onMounted(async () => {
  try {
    const res = await fetch(`${apiBase()}/api/infrastructure`, { headers: { Accept: 'application/json' } })
    const list = res.ok ? await res.json() : []
    infrastructure.value = list.map((i) => ({ value: i.id, label: i.name.charAt(0).toUpperCase() + i.name.slice(1).replace(/-/g, ' ') }))
  } catch (e) { /* без списка инфраструктуры форму всё равно можно отправить */ }
})

async function place() {
  tried.value = true
  serverError.value = ''
  if (!valid.value || sending.value) return
  sending.value = true
  try {
    const form = new FormData()
    images.value.forEach((img) => form.append('images[]', img.file))
    form.append('announs', JSON.stringify(d.value))
    form.append('dataImage', JSON.stringify(images.value.map(({ position, rotation }) => ({ position, rotation }))))
    const res = await fetch(`${apiBase()}/api/create/announ`, {
      method: 'POST',
      headers: { Accept: 'application/json', Authorization: 'Bearer ' + getToken() },
      body: form,
    })
    const json = await res.json().catch(() => ({}))
    if (!res.ok) {
      serverError.value = res.status === 401 ? 'Войдите в аккаунт, чтобы разместить объявление' : (json.message || 'Не удалось разместить объявление, попробуйте ещё раз')
      return
    }
    localStorage.removeItem('announ')
    d.value = {}
    images.value = []
    navigateTo('/personal_area/my_announ?created=1')
  } catch (e) {
    serverError.value = 'Нет связи с сервером, попробуйте ещё раз'
  } finally {
    sending.value = false
  }
}
</script>

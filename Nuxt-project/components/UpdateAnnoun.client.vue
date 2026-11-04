<template>
  <div class="min-h-screen bg-[#f4f6fb] pb-[110px]">
    <div class="max-w-[860px] mx-auto px-[16px] pt-[16px]">
      <NuxtLink to="/personal_area/my_announ" class="inline-flex items-center gap-[8px] text-[14px] font-semibold text-[#475569] hover:text-[#0468ff] mb-[12px] no-underline">
        <font-awesome-icon :icon="['fas', 'chevron-left']" class="text-[12px]" /> {{ t('Мои объявления') }}
      </NuxtLink>

      <div v-if="loading" class="flex flex-col gap-[14px]">
        <div v-for="n in 3" :key="n" class="h-[180px] bg-white rounded-[16px] border border-[#e8edf3] animate-pulse"></div>
      </div>

      <div v-else-if="loadError" class="bg-white rounded-[16px] border border-[#e8edf3] p-[28px] text-center">
        <p class="text-[#152242] font-semibold m-0">{{ t(loadError) }}</p>
        <NuxtLink to="/personal_area/my_announ" class="inline-flex mt-[12px] h-[38px] px-[18px] rounded-[10px] bg-[#0468ff] text-white text-[14px] font-semibold items-center no-underline">{{ t('К моим объявлениям') }}</NuxtLink>
      </div>

      <form v-else @submit.prevent="save" class="flex flex-col gap-[14px]" novalidate>
        <div>
          <h1 class="text-[24px] font-bold text-[#152242] m-0">{{ t('Редактирование объявления') }}</h1>
          <div class="flex flex-wrap gap-[8px] mt-[8px]">
            <span v-for="b of badges" :key="b" class="px-[10px] py-[3px] rounded-[8px] bg-white border border-[#e2e8f0] text-[#475569] text-[12px] font-semibold">{{ t(b) }}</span>
            <span v-if="announ.deleted_at" class="px-[10px] py-[3px] rounded-[8px] bg-[#f1f5f9] text-[#475569] text-[12px] font-semibold">{{ t('Снято с публикации') }}</span>
          </div>
          <p class="text-[13px] text-[#94a3b8] mt-[6px] mb-0">{{ t('Тип сделки, тип объекта и адрес изменить нельзя — для этого разместите новое объявление.') }}</p>
        </div>

        <!-- Фото -->
        <section class="bg-white rounded-[16px] border p-[16px] md:p-[20px]" :class="errors.photos ? 'border-[#fca5a5]' : 'border-[#e8edf3]'">
          <div class="flex items-center justify-between gap-[10px] mb-[12px]">
            <h2 class="text-[17px] font-bold text-[#152242] m-0">{{ t('Фотографии') }} <span class="text-[#94a3b8] font-normal text-[14px]">{{ photos.length }} / {{ MAX_PHOTOS }}</span></h2>
            <span class="text-[12px] text-[#94a3b8]">{{ t('Первое фото — обложка') }}</span>
          </div>
          <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-[10px]">
            <div v-for="(p, idx) of photos" :key="p.key" class="relative aspect-[4/3] rounded-[12px] overflow-hidden bg-[#e2e8f0] group">
              <img :src="p.src" :style="`transform: rotate(${p.rotation}deg)`" class="w-full h-full object-cover transition-transform" alt="" />
              <span v-if="idx === 0" class="absolute top-[6px] left-[6px] px-[7px] py-[1px] rounded-[6px] bg-[#0468ff] text-white text-[11px] font-semibold">{{ t('Обложка') }}</span>
              <span v-if="p.isNew" class="absolute top-[6px] right-[6px] px-[7px] py-[1px] rounded-[6px] bg-[#059669] text-white text-[11px] font-semibold">{{ t('Новое') }}</span>
              <div class="absolute inset-x-0 bottom-0 p-[6px] flex justify-between bg-gradient-to-t from-black/60 to-transparent">
                <div class="flex gap-[4px]">
                  <button type="button" @click="move(idx, -1)" :disabled="idx === 0" class="photo-btn" :aria-label="t('Левее')"><font-awesome-icon :icon="['fas', 'chevron-left']" /></button>
                  <button type="button" @click="move(idx, 1)" :disabled="idx === photos.length - 1" class="photo-btn" :aria-label="t('Правее')"><font-awesome-icon :icon="['fas', 'chevron-right']" /></button>
                </div>
                <div class="flex gap-[4px]">
                  <button v-if="idx !== 0" type="button" @click="makeCover(idx)" class="photo-btn" :aria-label="t('Сделать обложкой')" :title="t('Сделать обложкой')"><font-awesome-icon :icon="['fas', 'star']" /></button>
                  <button type="button" @click="rotate(p)" class="photo-btn" :aria-label="t('Повернуть')" :title="t('Повернуть')"><font-awesome-icon :icon="['fas', 'rotate-right']" /></button>
                  <button type="button" @click="removePhoto(idx)" class="photo-btn hover:!bg-[#dc2626]" :aria-label="t('Удалить фото')" :title="t('Удалить')"><font-awesome-icon :icon="['far', 'trash-can']" /></button>
                </div>
              </div>
            </div>
            <label v-if="photos.length < MAX_PHOTOS"
              class="aspect-[4/3] rounded-[12px] border-2 border-dashed border-[#cbd5e1] hover:border-[#0468ff] hover:bg-[#f8fbff] flex flex-col items-center justify-center gap-[6px] text-[#64748b] cursor-pointer transition-colors">
              <font-awesome-icon :icon="['fas', 'camera']" class="text-[22px] text-[#0468ff]" />
              <span class="text-[13px] font-semibold">{{ t('Добавить фото') }}</span>
              <input type="file" accept="image/*" multiple class="hidden" @change="addPhotos" />
            </label>
          </div>
          <p v-if="errors.photos" class="text-[13px] text-[#dc2626] mt-[8px] mb-0">{{ t(errors.photos) }}</p>
        </section>

        <!-- Основное -->
        <section class="card">
          <h2 class="card-title">{{ t('Основное') }}</h2>
          <div class="flex flex-col gap-[14px]">
            <div v-if="aiEnabled" class="flex flex-wrap items-center gap-[8px] px-[12px] py-[10px] rounded-[12px] bg-gradient-to-r from-[#eef4ff] to-[#f3e8ff]">
              <button type="button" @click="writeWithAi" :disabled="aiBusy" data-test="ai-describe"
                class="h-[36px] px-[14px] rounded-[10px] bg-gradient-to-r from-[#0468ff] to-[#7c3aed] text-white text-[14px] font-semibold disabled:opacity-60 flex items-center gap-[8px]">
                <font-awesome-icon :icon="['fas', aiBusy ? 'spinner' : 'wand-magic-sparkles']" :spin-pulse="aiBusy" />
                {{ aiBusy ? t('Пишу…') : (form.description ? t('Улучшить текст с ИИ') : t('Написать текст с ИИ')) }}
              </button>
              <button v-if="aiUndo" type="button" @click="undoAi" class="h-[36px] px-[10px] rounded-[10px] text-[13.5px] font-semibold text-[#475569] hover:bg-white/70">
                <font-awesome-icon :icon="['fas', 'rotate-left']" /> {{ t('Вернуть мой текст') }}
              </button>
              <span class="text-[12.5px] text-[#64748b] basis-full sm:basis-auto">{{ aiError || t('ИИ напишет заголовок и описание по характеристикам — проверьте и поправьте перед сохранением') }}</span>
            </div>
            <label class="block">
              <span class="field-label">{{ t('Заголовок') }}</span>
              <input v-model="form.title" maxlength="150" class="field" :class="errors.title && 'field-error'" :placeholder="t('Например: Светлая 2-комнатная квартира у парка')" />
              <span class="field-hint">{{ (form.title || '').length }} / 150</span>
            </label>
            <label class="block">
              <span class="field-label">{{ t('Описание') }}</span>
              <textarea v-model="form.description" maxlength="5000" rows="6" class="field !h-auto py-[10px] resize-y" :placeholder="t('Расскажите о преимуществах, состоянии, инфраструктуре рядом…')"></textarea>
              <span class="field-hint">{{ (form.description || '').length }} / 5000</span>
            </label>
            <div class="grid sm:grid-cols-2 gap-[14px]">
              <label class="block">
                <span class="field-label">{{ t('Цена, сомони') }}{{ isRent ? (announ.rental_type === 'Посуточно' ? t(' за сутки') : t(' в месяц')) : '' }} *</span>
                <input v-model.number="form.price" type="number" min="1" inputmode="numeric" class="field" :class="errors.price && 'field-error'" />
                <span v-if="errors.price" class="field-error-text">{{ t(errors.price) }}</span>
              </label>
              <label class="block">
                <span class="field-label">{{ t('Ссылка на видео (YouTube)') }}</span>
                <input v-model.trim="form.linkVideo" type="url" class="field" :class="errors.linkVideo && 'field-error'" placeholder="https://youtube.com/…" />
                <span v-if="errors.linkVideo" class="field-error-text">{{ t(errors.linkVideo) }}</span>
              </label>
            </div>
          </div>
        </section>

        <!-- Параметры по типу объекта -->
        <section v-for="section of sections" :key="section.title" class="card">
          <h2 class="card-title">{{ t(section.title) }}</h2>
          <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-[14px]">
            <label v-for="f of section.fields" :key="f.key" class="block">
              <span class="field-label">{{ t(f.label) }}</span>
              <div v-if="f.type === 'number'" class="relative">
                <input v-model="form[f.key]" type="number" :min="f.min ?? 0" inputmode="numeric" class="field" :class="[f.suffix && 'pr-[44px]', errors[f.key] && 'field-error']" />
                <span v-if="f.suffix" class="absolute right-[12px] top-1/2 -translate-y-1/2 text-[13px] text-[#94a3b8]">{{ t(f.suffix) }}</span>
              </div>
              <select v-else v-model="form[f.key]" class="field bg-white">
                <option value="">{{ t('Не указано') }}</option>
                <option v-for="o of optionsFor(f)" :key="o.value" :value="o.value">{{ t(o.label) }}</option>
              </select>
              <span v-if="errors[f.key]" class="field-error-text">{{ t(errors[f.key]) }}</span>
            </label>
          </div>
        </section>

        <!-- Условия аренды -->
        <section v-if="isRent" class="card">
          <h2 class="card-title">{{ t('Условия аренды') }}</h2>
          <div class="grid sm:grid-cols-3 gap-[14px]">
            <label class="block">
              <span class="field-label">{{ t('Срок аренды') }}</span>
              <select v-model="form.rentalPeriod" class="field bg-white">
                <option value="">{{ t('Не указано') }}</option>
                <option v-for="o of withCurrent(['От года', 'Несколько месяцев'], form.rentalPeriod)" :key="o.value" :value="o.value">{{ t(o.label) }}</option>
              </select>
            </label>
            <label class="toggle">
              <input type="checkbox" class="hidden" :checked="form.allowKids === 'Можно с детьми'" @change="form.allowKids = $event.target.checked ? 'Можно с детьми' : 'нет'" />
              <span><font-awesome-icon :icon="['fas', 'child']" /> {{ t('Можно с детьми') }}</span>
            </label>
            <label class="toggle">
              <input type="checkbox" class="hidden" :checked="form.allowAnimal === 'Можно с животными'" @change="form.allowAnimal = $event.target.checked ? 'Можно с животными' : 'нет'" />
              <span><font-awesome-icon :icon="['fas', 'paw']" /> {{ t('Можно с животными') }}</span>
            </label>
          </div>
          <div v-if="amenities.length" class="mt-[16px]">
            <span class="field-label">{{ t('Удобства') }}</span>
            <div class="flex flex-wrap gap-[8px]">
              <label v-for="c of amenities" :key="c.id" class="toggle !h-[36px]">
                <input type="checkbox" class="hidden" :value="c.id" v-model="form.conditions" />
                <span>{{ c.name === 'В комнатах' ? t('Мебель в комнатах') : c.name === 'На кухне' ? t('Мебель на кухне') : t(c.name) }}</span>
              </label>
            </div>
          </div>
        </section>

        <p v-if="saveError" class="bg-[#fef2f2] border border-[#fecaca] text-[#b91c1c] rounded-[12px] px-[14px] py-[10px] text-[14px] m-0">{{ t(saveError) }}</p>
      </form>
    </div>

    <!-- Нижняя панель -->
    <div v-if="!loading && !loadError" class="fixed bottom-0 left-0 right-0 z-[50] bg-white border-t border-[#e8edf3]">
      <div class="max-w-[860px] mx-auto px-[16px] py-[12px] flex items-center justify-between gap-[10px]">
        <span class="text-[13px] text-[#64748b] hidden sm:block">{{ dirty ? t('Есть несохранённые изменения') : t('Изменений нет') }}</span>
        <div class="flex gap-[8px] ml-auto">
          <NuxtLink to="/personal_area/my_announ" class="h-[42px] px-[18px] rounded-[10px] border border-[#e2e8f0] text-[14px] font-semibold text-[#152242] flex items-center no-underline">{{ t('Отмена') }}</NuxtLink>
          <button @click="save" :disabled="saving || !dirty"
            class="h-[42px] px-[20px] rounded-[10px] text-white text-[14px] font-semibold flex items-center gap-[8px] transition-colors"
            :class="saving || !dirty ? 'bg-[#93b8f5] cursor-not-allowed' : 'bg-[#0468ff] hover:bg-[#0353cc]'">
            <font-awesome-icon v-if="saving" :icon="['fas', 'spinner']" spin-pulse />
            {{ t('Сохранить изменения') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
useHead({ title: () => t('Редактирование объявления') })

const route = useRoute()
const router = useRouter()
const MAX_PHOTOS = 20

// Колонка БД ↔ поле формы (те же имена, что ждёт MyAnnounController::EDITABLE)
const FIELD_MAP = {
  title: 'title', description: 'description', price: 'price', quantityRoom: 'quantity_room', totalArea: 'total_area',
  livingArea: 'living_area', kitchenArea: 'kitchen_area', floor: 'floor', floorInHouse: 'floor_in_house', year: 'year_of_construction',
  houseType: 'house_type', repair: 'repair', balcony: 'Balcony', elevator: 'elevator', parking: 'parking', houseArea: 'house_area',
  landArea: 'land_area', bedrooms: 'quantity_bedrooms', conditionHome: 'condition_of_the_home', electricity: 'electricity',
  landCategory: 'Land_category', buildingArea: 'building_area', ceilingHeight: 'CeilingHeight', layout: 'layout', state: 'state',
  furniture: 'furniture', numberSeats: 'numberSeats', rentalPeriod: 'rental_period', allowKids: 'allowKids', allowAnimal: 'allowAnimal',
  linkVideo: 'link_video',
}
const NUMERIC = ['price', 'quantityRoom', 'totalArea', 'livingArea', 'kitchenArea', 'floor', 'floorInHouse', 'year', 'houseArea', 'landArea', 'bedrooms', 'buildingArea', 'ceilingHeight', 'numberSeats']

// Значения как в мастере создания объявления (чтобы фильтры поиска их находили)
const OPTIONS = {
  houseType: ['Кирпичный', 'Монолитный', 'Панельный', 'Блочный', 'Деревянный', 'Монолитно-кирпичный', 'Сталинский'],
  repair: [{ value: 'Без ремонта', label: 'Без ремонта' }, { value: 'Обичный', label: 'Обычный' }, 'Косметический', 'Евро', 'Дизайнерский'],
  parking: ['Наземная', 'Многоуровневая', 'Подземная', 'На крыше', 'Нет'],
  conditionHome: ['Можно жить', 'Нужен ремонт', 'Нужен капремонт или под снос', { value: 'Нужно достроит', label: 'Нужно достроить' }],
  landCategory: ['Земли населённых пунктов', 'Земли сельхозназначения', 'Другое'],
  layout: ['Кабинетная', 'Открытая', 'Коридорная', 'Смешанная'],
  state: ['Офисная отделка', 'Под чистовую отделку', 'Типовой', 'Дизайнерский', { value: 'Нужен капистальный', label: 'Нужен капитальный ремонт' }, { value: 'Нужен Косметический', label: 'Нужен косметический ремонт' }],
}

const loading = ref(true)
const loadError = ref('')
const saving = ref(false)
const saveError = ref('')
const errors = reactive({})
const announ = ref({})
const form = reactive({ conditions: [] })
const photos = ref([])
const amenities = ref([])
let initialSnapshot = ''
let keySeq = 0

const isCommercial = computed(() => announ.value.type_real_estate === 'Коммерческая')
const isRent = computed(() => announ.value.deal_type === 'Аренда')
const isLand = computed(() => ['Участок', 'Коммерческая земля'].includes(announ.value.type_object))
const isHouse = computed(() => ['Дом/Дача', 'Коттедж', 'Часть дома', 'Дом'].includes(announ.value.type_object))
const yes = computed(() => (isCommercial.value ? 'Есть' : 'есть'))
const no = computed(() => (isCommercial.value ? 'Нет' : 'нет'))

// --- ИИ пишет заголовок и описание по заполненным полям
const aiEnabled = useAiStatus()
const aiBusy = ref(false)
const aiError = ref('')
const aiUndo = ref(null)

async function writeWithAi() {
  aiBusy.value = true
  aiError.value = ''
  try {
    const { title, description, conditions, ...fields } = form
    const facts = {
      'сделка': announ.value.deal_type,
      'срок аренды': announ.value.rental_type,
      'тип объекта': announ.value.type_object,
      'категория': announ.value.type_real_estate,
      ...fields,
      'удобства': amenities.value.filter((c) => (conditions || []).includes(c.id)).map((c) => c.name).join(', '),
    }
    const res = await aiDescribe(facts, title, description)
    aiUndo.value = { title, description }
    form.title = res.title
    form.description = res.description
  } catch (e) {
    aiError.value = e.message
  } finally {
    aiBusy.value = false
  }
}

function undoAi() {
  form.title = aiUndo.value.title
  form.description = aiUndo.value.description
  aiUndo.value = null
}

const badges = computed(() => {
  const a = announ.value
  const deal = a.deal_type === 'Аренда' ? (a.rental_type === 'Посуточно' ? 'Посуточная аренда' : 'Аренда') : a.deal_type
  return [deal, a.type_real_estate, a.type_object].filter(Boolean)
})

const sections = computed(() => {
  const yesNo = { type: 'select', yesNo: true }
  if (isLand.value) {
    return [{ title: 'Участок', fields: [
      { key: 'landArea', label: 'Площадь участка', type: 'number', suffix: 'сот.' },
      { key: 'landCategory', label: 'Категория земли', type: 'select' },
      { key: 'electricity', label: 'Электричество', ...yesNo },
    ] }]
  }
  if (isCommercial.value) {
    return [{ title: 'Параметры помещения', fields: [
      { key: 'totalArea', label: 'Площадь', type: 'number', suffix: 'м²' },
      { key: 'buildingArea', label: 'Площадь здания', type: 'number', suffix: 'м²' },
      { key: 'landArea', label: 'Площадь участка', type: 'number', suffix: 'сот.' },
      { key: 'floor', label: 'Этаж', type: 'number', min: -5 },
      { key: 'floorInHouse', label: 'Этажей в здании', type: 'number' },
      { key: 'ceilingHeight', label: 'Высота потолков', type: 'number', suffix: 'м' },
      { key: 'year', label: 'Год постройки', type: 'number', min: 1800 },
      { key: 'numberSeats', label: 'Рабочих мест', type: 'number' },
      { key: 'layout', label: 'Планировка', type: 'select' },
      { key: 'state', label: 'Состояние', type: 'select' },
      { key: 'furniture', label: 'Мебель', ...yesNo },
      { key: 'elevator', label: 'Лифт', ...yesNo },
      { key: 'parking', label: 'Парковка', type: 'select' },
    ] }]
  }
  if (isHouse.value) {
    return [{ title: 'О доме', fields: [
      { key: 'houseArea', label: 'Площадь дома', type: 'number', suffix: 'м²' },
      { key: 'landArea', label: 'Площадь участка', type: 'number', suffix: 'сот.' },
      { key: 'bedrooms', label: 'Спален', type: 'number' },
      { key: 'floorInHouse', label: 'Этажей', type: 'number' },
      { key: 'year', label: 'Год постройки', type: 'number', min: 1800 },
      { key: 'houseType', label: 'Материал стен', type: 'select' },
      { key: 'conditionHome', label: 'Состояние дома', type: 'select' },
      { key: 'repair', label: 'Ремонт', type: 'select' },
      { key: 'electricity', label: 'Электричество', ...yesNo },
      { key: 'parking', label: 'Парковка', type: 'select' },
    ] }]
  }
  return [
    { title: 'О квартире', fields: [
      { key: 'quantityRoom', label: 'Комнат', type: 'number' },
      { key: 'totalArea', label: 'Общая площадь', type: 'number', suffix: 'м²' },
      { key: 'livingArea', label: 'Жилая площадь', type: 'number', suffix: 'м²' },
      { key: 'kitchenArea', label: 'Площадь кухни', type: 'number', suffix: 'м²' },
      { key: 'floor', label: 'Этаж', type: 'number', min: -5 },
      { key: 'floorInHouse', label: 'Этажей в доме', type: 'number' },
      { key: 'repair', label: 'Ремонт', type: 'select' },
      { key: 'balcony', label: 'Балкон', ...yesNo },
    ] },
    { title: 'О доме', fields: [
      { key: 'year', label: 'Год постройки', type: 'number', min: 1800 },
      { key: 'houseType', label: 'Тип дома', type: 'select' },
      { key: 'elevator', label: 'Лифт', ...yesNo },
      { key: 'parking', label: 'Парковка', type: 'select' },
    ] },
  ]
})

// Список вариантов + текущее значение, если его нет в списке (чтобы ничего не потерять при сохранении)
function withCurrent(list, current) {
  const options = list.map((o) => (typeof o === 'string' ? { value: o, label: o } : o))
  if (current && !options.some((o) => o.value === current)) options.push({ value: current, label: current })
  return options
}
function optionsFor(f) {
  if (f.yesNo) return withCurrent([{ value: yes.value, label: 'Есть' }, { value: no.value, label: 'Нет' }], form[f.key])
  return withCurrent(OPTIONS[f.key] || [], form[f.key])
}

const headers = () => ({ Accept: 'application/json', Authorization: 'Bearer ' + getToken() })

function snapshot() {
  return JSON.stringify({ form, photos: photos.value.map((p) => [p.id || p.key, p.rotation]) })
}
const dirty = computed(() => {
  // зависимость от формы и фото, чтобы computed пересчитывался
  JSON.stringify(form); photos.value.forEach((p) => p.rotation)
  return snapshot() !== initialSnapshot
})

async function load() {
  const id = Number(route.query.id)
  if (!id) {
    loadError.value = 'Объявление не выбрано'
    loading.value = false
    return
  }
  try {
    const [res, condRes] = await Promise.all([
      fetch(`${apiBase()}/api/my/announs/${id}`, { headers: headers() }),
      fetch(`${apiBase()}/api/conditions`),
    ])
    if (res.status === 404) throw new Error('Объявление не найдено или принадлежит другому пользователю')
    if (!res.ok) throw new Error('Не удалось загрузить объявление')
    const data = (await res.json()).data
    announ.value = data
    for (const [key, column] of Object.entries(FIELD_MAP)) form[key] = data[column] ?? ''
    form.conditions = (data.condition_ids || []).map(Number)
    photos.value = (data.pictures || []).map((p) => ({
      key: `p${p.id}`, id: p.id, rotation: Number(p.rotation) || 0,
      src: `${apiBase()}/api/image/small/${p.pictures}`,
    }))
    if (condRes.ok) {
      amenities.value = (await condRes.json()).filter((c) => !['Можно с детьми', 'Можно с животными'].includes(c.name))
    }
    initialSnapshot = snapshot()
    useHead({ title: () => t('Редактирование: {x}', { x: data.title || t(data.type_object) }) })
  } catch (e) {
    loadError.value = e.message || 'Не удалось загрузить объявление'
  } finally {
    loading.value = false
  }
}

// --- Фото
function addPhotos(event) {
  const files = [...event.target.files]
  for (const file of files) {
    if (photos.value.length >= MAX_PHOTOS) break
    if (!file.type.startsWith('image/')) continue
    photos.value.push({ key: `n${++keySeq}`, isNew: true, file, rotation: 0, src: URL.createObjectURL(file) })
  }
  event.target.value = ''
  delete errors.photos
}
function removePhoto(idx) {
  const [p] = photos.value.splice(idx, 1)
  if (p.isNew) URL.revokeObjectURL(p.src)
}
function move(idx, step) {
  const target = idx + step
  if (target < 0 || target >= photos.value.length) return
  const list = photos.value
  ;[list[idx], list[target]] = [list[target], list[idx]]
}
function makeCover(idx) {
  const [p] = photos.value.splice(idx, 1)
  photos.value.unshift(p)
}
function rotate(p) {
  p.rotation = (p.rotation + 90) % 360
}

// --- Проверка перед отправкой
function validate() {
  for (const key of Object.keys(errors)) delete errors[key]
  if (!photos.value.length) errors.photos = 'Добавьте хотя бы одну фотографию'
  if (!(Number(form.price) > 0)) errors.price = 'Укажите цену'
  const n = (k) => (form[k] === '' || form[k] === null ? null : Number(form[k]))
  if (n('floor') !== null && n('floorInHouse') !== null && n('floor') > n('floorInHouse')) errors.floor = 'Этаж больше этажности дома'
  if (n('totalArea') !== null && (n('livingArea') || 0) + (n('kitchenArea') || 0) > n('totalArea')) errors.livingArea = 'Жилая + кухня больше общей площади'
  if (form.linkVideo && !/^https?:\/\/\S+$/.test(form.linkVideo)) errors.linkVideo = 'Ссылка должна начинаться с http:// или https://'
  return !Object.keys(errors).length
}

async function save() {
  saveError.value = ''
  if (!validate()) {
    saveError.value = 'Исправьте отмеченные поля'
    return
  }
  saving.value = true
  try {
    const payload = {}
    for (const key of Object.keys(FIELD_MAP)) {
      const v = form[key]
      payload[key] = NUMERIC.includes(key) ? (v === '' || v === null ? null : Number(v)) : (v ?? '')
    }
    if (isRent.value && !isCommercial.value) payload.conditions = form.conditions

    const fd = new FormData()
    fd.append('announ', JSON.stringify(payload))
    fd.append('photos', JSON.stringify(photos.value.filter((p) => !p.isNew).map((p) => ({ id: p.id, rotation: p.rotation }))))
    // новые фото добавляются в конец, поэтому порядок «старые → новые» сохраняем как есть
    const newOnes = photos.value.filter((p) => p.isNew)
    newOnes.forEach((p) => fd.append('newPhotos[]', p.file))
    fd.append('newPhotosMeta', JSON.stringify(newOnes.map((p) => ({ rotation: p.rotation }))))

    const res = await fetch(`${apiBase()}/api/my/announs/${announ.value.id}`, { method: 'POST', headers: headers(), body: fd })
    const json = await res.json().catch(() => ({}))
    if (!res.ok) {
      if (json.errors) {
        const reverse = Object.fromEntries(Object.keys(FIELD_MAP).map((k) => [k, k]))
        for (const [key, messages] of Object.entries(json.errors)) if (reverse[key]) errors[key] = messages[0]
      }
      saveError.value = json.message || 'Не удалось сохранить объявление'
      return
    }
    initialSnapshot = snapshot()
    router.push({ path: '/personal_area/my_announ', query: { saved: 1 } })
  } catch (e) {
    saveError.value = 'Нет связи с сервером'
  } finally {
    saving.value = false
  }
}

// Предупреждение при уходе со страницы с несохранёнными изменениями
function beforeUnload(e) {
  if (dirty.value && !saving.value) {
    e.preventDefault()
    e.returnValue = ''
  }
}
onMounted(() => {
  load()
  window.addEventListener('beforeunload', beforeUnload)
})
onUnmounted(() => {
  window.removeEventListener('beforeunload', beforeUnload)
  photos.value.filter((p) => p.isNew).forEach((p) => URL.revokeObjectURL(p.src))
})
</script>

<style scoped>
.card { @apply bg-white rounded-[16px] border border-[#e8edf3] p-[16px] md:p-[20px]; }
.card-title { @apply text-[17px] font-bold text-[#152242] m-0 mb-[14px]; }
.field-label { @apply block text-[13px] font-semibold text-[#475569] mb-[6px]; }
.field { @apply w-full h-[42px] px-[12px] rounded-[10px] border border-[#e2e8f0] text-[15px] text-[#152242] outline-none transition-colors focus:border-[#0468ff]; }
.field-error { @apply border-[#f87171]; }
.field-error-text { @apply block text-[12px] text-[#dc2626] mt-[4px]; }
.field-hint { @apply block text-[12px] text-[#94a3b8] mt-[4px] text-right; }
.photo-btn { @apply w-[26px] h-[26px] rounded-[7px] bg-black/55 text-white text-[11px] grid place-items-center disabled:opacity-30 hover:bg-black/75; }
.toggle { @apply flex items-center h-[42px] cursor-pointer select-none; }
.toggle span { @apply w-full h-full flex items-center justify-center gap-[8px] px-[12px] rounded-[10px] border border-[#e2e8f0] text-[14px] font-semibold text-[#475569] transition-colors; }
.toggle input:checked + span { @apply bg-[#eef4ff] border-[#0468ff] text-[#0468ff]; }
</style>

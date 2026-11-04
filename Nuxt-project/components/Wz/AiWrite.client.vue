<template>
  <div v-if="aiEnabled" class="flex flex-wrap items-center gap-[8px] px-[12px] py-[10px] rounded-[14px] bg-gradient-to-r from-[#eef4ff] to-[#f3e8ff]">
    <button type="button" @click="writeWithAi" :disabled="aiBusy" data-test="ai-describe"
      class="h-[38px] px-[14px] rounded-[10px] bg-gradient-to-r from-[#0468ff] to-[#7c3aed] text-white text-[14px] font-semibold disabled:opacity-60 flex items-center gap-[8px]">
      <font-awesome-icon :icon="['fas', aiBusy ? 'spinner' : 'wand-magic-sparkles']" :spin-pulse="aiBusy" />
      {{ aiBusy ? t('Пишу…') : (draft.description ? t('Улучшить текст с ИИ') : t('Написать текст с ИИ')) }}
    </button>
    <button v-if="aiUndo" type="button" @click="undoAi" class="h-[38px] px-[10px] rounded-[10px] text-[13.5px] font-semibold text-[#475569] hover:bg-white/70">
      <font-awesome-icon :icon="['fas', 'rotate-left']" /> {{ t('Вернуть мой текст') }}
    </button>
    <span class="text-[12.5px] text-[#64748b]">{{ aiError ? t(aiError) : t(hint) }}</span>
  </div>
</template>

<script setup>
// ИИ пишет заголовок и описание по тому, что уже заполнено в черновике объявления
const props = defineProps({
  draft: { type: Object, required: true },
  hint: { type: String, default: 'ИИ напишет текст по тому, что вы указали на прошлых шагах, — проверьте и поправьте' },
})

const aiEnabled = useAiStatus()
const aiBusy = ref(false)
const aiError = ref('')
const aiUndo = ref(null)
const FACT_LABELS = {
  rent: 'сделка', typeRent: 'срок аренды', Estate: 'категория', objects: 'тип объекта', address: 'адрес', quantityRoom: 'комнат',
  generalArea: 'общая площадь, м²', liveArea: 'жилая площадь, м²', kitchenArea: 'кухня, м²', floor: 'этаж', floorHouse: 'этажей в доме',
  year: 'год постройки', repair: 'ремонт', selectType: 'тип дома', elevator: 'лифт', balcon: 'балкон', parking: 'парковка',
  landArea: 'участок, сотки', homeArea: 'площадь дома, м²', quantityBedroom: 'спален', conditionHome: 'состояние дома', electricity: 'электричество',
  // коммерческая
  CeilingHeight: 'высота потолков, м', numberSeats: 'рабочих мест', layout: 'планировка', State: 'состояние', Furniture: 'мебель',
  buildingArea: 'площадь здания, м²',
}

async function writeWithAi() {
  const d = props.draft
  aiBusy.value = true
  aiError.value = ''
  try {
    const facts = {}
    for (const [key, label] of Object.entries(FACT_LABELS)) {
      if (d[key] !== undefined && d[key] !== null && d[key] !== '') facts[label] = d[key]
    }
    const res = await aiDescribe(facts, d.title || '', d.description || '')
    aiUndo.value = { title: d.title, description: d.description }
    d.title = res.title
    d.description = res.description
  } catch (e) {
    aiError.value = e.message
  } finally {
    aiBusy.value = false
  }
}

function undoAi() {
  props.draft.title = aiUndo.value.title || ''
  props.draft.description = aiUndo.value.description || ''
  aiUndo.value = null
}
</script>

<template>
  <!-- Причина отказа: готовая из справочника (админка → «Причины отказа») и/или свой текст; «Другое» — только свой текст -->
  <div>
    <div class="flex flex-wrap gap-[6px]">
      <button v-for="r of reasons" :key="r.id" type="button" @click="$emit('update:reasonId', reasonId === r.id ? null : r.id)" :title="loc(r, 'text')"
        class="px-[10px] h-[30px] rounded-full border text-[12.5px] transition-colors"
        :class="reasonId === r.id ? 'border-[#dc2626] bg-[#fef2f2] text-[#b91c1c] font-semibold' : 'border-[#e2e8f0] bg-white text-[#475569] hover:border-[#94a3b8]'">
        {{ loc(r, 'title') }}
      </button>
      <button type="button" @click="$emit('update:reasonId', null)"
        class="px-[10px] h-[30px] rounded-full border text-[12.5px] transition-colors"
        :class="!reasonId ? 'border-[#dc2626] bg-[#fef2f2] text-[#b91c1c] font-semibold' : 'border-[#e2e8f0] bg-white text-[#475569] hover:border-[#94a3b8]'">
        {{ t('Другое') }}
      </button>
    </div>
    <p v-if="selected" class="m-0 mt-[8px] px-[10px] py-[8px] rounded-[10px] bg-[#fef2f2] text-[13px] text-[#991b1b] leading-[1.45]">
      {{ loc(selected, 'text') }}
      <template v-if="loc(selected, 'text') !== selected.text"><br /><span class="opacity-80">{{ t('Владелец получит по-русски:') }} {{ selected.text }}</span></template>
    </p>
    <textarea :id="inputId" :value="text" @input="$emit('update:text', $event.target.value)" rows="2" maxlength="500" data-test="reason"
      class="w-full mt-[8px] p-[10px] rounded-[10px] border border-[#e2e8f0] bg-white outline-none focus:border-[#dc2626] text-[14px] resize-y"
      :placeholder="selected ? t('Уточнение для владельца — необязательно') : t('Опишите причину — её увидит владелец (от 5 символов)')"></textarea>
    <p v-if="lang !== 'ru'" class="m-0 mt-[4px] text-[12px] text-[#64748b]">{{ t('Свой текст владелец получит как есть — пишите по-русски') }}</p>
  </div>
</template>

<script setup>
const props = defineProps({
  reasons: { type: Array, default: () => [] },
  reasonId: { type: Number, default: null },
  text: { type: String, default: '' },
  inputId: { type: String, default: undefined },
})
defineEmits(['update:reasonId', 'update:text'])
const selected = computed(() => props.reasons.find((r) => r.id === props.reasonId))
// модератору — причина на языке сайта (title_en / text_en), нет перевода — русский; владельцу всегда уходит русский text
const { lang } = useLang()
const loc = (r, field) => (lang.value === 'en' && r[field + '_en']) || r[field]
</script>

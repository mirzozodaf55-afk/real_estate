<template>
  <!-- Переключатель языка: RU / EN. Выбор запоминается (cookie), страница перерисовывается сразу -->
  <div class="lang-switch inline-flex items-center p-[3px] rounded-[10px] bg-[#f1f5f9]" role="group" :aria-label="t('Язык')" data-test="lang-switch">
    <button v-for="l of LANGS" :key="l.code" type="button" @click="setLang(l.code)" :title="l.label" :aria-pressed="lang === l.code"
      class="h-[28px] px-[9px] rounded-[8px] text-[12.5px] font-bold transition-colors"
      :class="lang === l.code ? 'bg-white text-[#0468ff] shadow-sm' : 'text-[#64748b] hover:text-[#152242]'">
      {{ l.short }}
    </button>
  </div>
</template>

<script setup>
const { lang, setLang } = useLang()
</script>

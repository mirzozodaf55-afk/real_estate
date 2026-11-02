<template>
  <span class="site-logo inline-flex items-center gap-[9px] select-none" :class="{ 'site-logo--light': light }" aria-label="Domly — недвижимость">
    <svg :width="size" :height="size" viewBox="0 0 40 40" fill="none" aria-hidden="true">
      <defs>
        <linearGradient id="site-logo-bg" x1="0" y1="0" x2="40" y2="40" gradientUnits="userSpaceOnUse">
          <stop stop-color="#0468ff" />
          <stop offset="1" stop-color="#4f46e5" />
        </linearGradient>
      </defs>
      <!-- light — для цветного фона (шапка помощника): белая плашка, рисунок синий -->
      <rect width="40" height="40" rx="11" :fill="light ? '#fff' : 'url(#site-logo-bg)'" />
      <!-- крыша дома -->
      <path d="M8.5 19.5 20 10l11.5 9.5" :stroke="light ? '#0468ff' : '#fff'" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
      <!-- метка на карте вместо двери -->
      <path d="M20 31.5s-6-5.4-6-9.6a6 6 0 1 1 12 0c0 4.2-6 9.6-6 9.6Z" :fill="light ? '#0468ff' : '#fff'" />
      <circle cx="20" cy="21.8" r="2.3" :fill="light ? '#fff' : '#2f5bff'" />
    </svg>
    <span v-if="!compact" class="site-logo__word" :style="wordSize ? `font-size:${wordSize}px` : ''">dom<span class="site-logo__tld">ly</span><span v-if="suffix" class="site-logo__suffix">{{ suffix }}</span></span>
  </span>
</template>

<script setup>
defineProps({
  size: { type: Number, default: 34 },
  compact: { type: Boolean, default: false },
  // белый вариант для цветного фона
  light: { type: Boolean, default: false },
  // подпись после названия: «domly помощник»
  suffix: { type: String, default: '' },
  wordSize: { type: Number, default: 0 },
})
</script>

<style scoped>
.site-logo__word {
  font-size: 25px;
  font-weight: 800;
  letter-spacing: -0.045em;
  line-height: 1;
  color: #152242;
  text-transform: none;
  white-space: nowrap;
}
.site-logo__tld {
  color: #0468ff;
}
.site-logo__suffix {
  margin-left: 0.3em;
  font-weight: 600;
  letter-spacing: -0.01em;
}
.site-logo--light .site-logo__word {
  color: #fff !important;
}
.site-logo--light .site-logo__tld {
  color: #cfe0ff !important;
}
</style>

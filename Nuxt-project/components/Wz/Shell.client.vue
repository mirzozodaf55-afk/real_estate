<template>
  <div class="min-h-[calc(100vh-70px)] bg-[#f4f6fb] pb-[96px] lg:pb-[40px]">
    <div class="max-w-[1120px] mx-auto px-[16px] pt-[20px] lg:pt-[28px] grid grid-cols-1 lg:grid-cols-[260px_1fr] gap-[20px] items-start">
      <!-- Шаги -->
      <aside class="hidden lg:block lg:sticky lg:top-[20px]">
        <div class="bg-white rounded-[16px] border border-[#e8edf3] p-[16px]">
          <div class="text-[12px] uppercase tracking-[0.08em] text-[#94a3b8] font-bold">{{ t('Новое объявление') }}</div>
          <div class="text-[14px] font-semibold text-[#152242] mt-[2px] truncate">{{ summary }}</div>
          <ol class="list-none m-0 mt-[14px] p-0 flex flex-col gap-[2px]">
            <li v-for="(s, i) of nav.steps" :key="s.path">
              <button type="button" :disabled="i > nav.index" @click="i < nav.index && navigateTo(s.path)"
                class="w-full flex items-center gap-[10px] px-[8px] py-[7px] rounded-[10px] text-left transition-colors"
                :class="i === nav.index ? 'bg-[#eef4ff]' : i < nav.index ? 'hover:bg-[#f8fafc] cursor-pointer' : 'cursor-default'">
                <span class="w-[24px] h-[24px] rounded-full grid place-items-center text-[12px] font-bold shrink-0"
                  :class="i < nav.index ? 'bg-[#16a34a] text-white' : i === nav.index ? 'bg-[#0468ff] text-white' : 'bg-[#eef2f7] text-[#94a3b8]'">
                  <font-awesome-icon v-if="i < nav.index" :icon="['fas', 'check']" class="text-[11px]" />
                  <template v-else>{{ i + 1 }}</template>
                </span>
                <span class="text-[14px]" :class="i === nav.index ? 'font-semibold text-[#0468ff]' : i < nav.index ? 'text-[#334155]' : 'text-[#94a3b8]'">{{ t(s.label) }}</span>
              </button>
            </li>
          </ol>
        </div>
        <p class="text-[12px] text-[#94a3b8] mt-[10px] px-[4px] leading-[1.5]">{{ t('Всё, что вы заполнили, сохраняется — можно вернуться позже и продолжить.') }}</p>
      </aside>

      <main class="min-w-0">
        <!-- Телефон: прогресс -->
        <div class="lg:hidden mb-[12px]">
          <div class="flex justify-between text-[12.5px] text-[#64748b] mb-[6px]">
            <span>{{ t('Шаг') }} {{ nav.index + 1 }} {{ t('из') }} {{ nav.steps.length }}</span>
            <span class="font-semibold text-[#152242] truncate ml-[8px]">{{ t(nav.steps[nav.index]?.label) }}</span>
          </div>
          <div class="h-[6px] rounded-full bg-[#e2e8f0] overflow-hidden">
            <div class="h-full rounded-full bg-gradient-to-r from-[#60a5fa] to-[#0468ff] transition-all" :style="{ width: `${((nav.index + 1) / nav.steps.length) * 100}%` }"></div>
          </div>
        </div>

        <section class="bg-white rounded-[18px] border border-[#e8edf3] p-[18px] sm:p-[26px]">
          <h1 class="text-[22px] sm:text-[26px] font-bold text-[#152242] m-0 leading-tight">{{ title }}</h1>
          <p v-if="subtitle" class="text-[14px] text-[#64748b] mt-[6px] mb-0">{{ t(subtitle) }}</p>
          <div class="mt-[22px] flex flex-col gap-[22px]">
            <slot />
          </div>
        </section>

        <!-- Кнопки: на телефоне прилипают к низу экрана -->
        <div class="fixed lg:static inset-x-0 bottom-0 z-[40] bg-white/95 lg:bg-transparent backdrop-blur lg:backdrop-blur-0 border-t lg:border-0 border-[#e8edf3] px-[16px] lg:px-0 py-[10px] lg:py-0 lg:mt-[16px]">
          <p v-if="error" class="text-[13px] text-[#dc2626] mt-0 mb-[8px] lg:text-right" role="alert" data-test="wz-error">
            <font-awesome-icon :icon="['fas', 'circle-exclamation']" class="mr-[4px]" />{{ t(error) }}
          </p>
          <div class="flex gap-[10px] lg:justify-end">
            <button v-if="nav.prev && !hideBack" type="button" @click="navigateTo(nav.prev)"
              class="h-[46px] px-[18px] rounded-[12px] border border-[#e2e8f0] bg-white text-[15px] font-semibold text-[#334155] hover:border-[#94a3b8]">
              <font-awesome-icon :icon="['fas', 'arrow-left']" class="mr-[6px] text-[13px]" />{{ t('Назад') }}
            </button>
            <button type="button" @click="$emit('next')" :disabled="loading" data-test="wz-next"
              class="h-[46px] flex-1 lg:flex-none lg:min-w-[180px] px-[22px] rounded-[12px] text-white text-[15px] font-bold transition-colors disabled:opacity-70"
              :class="final ? 'bg-[#16a34a] hover:bg-[#15803d]' : 'bg-[#0468ff] hover:bg-[#0353cc]'">
              <font-awesome-icon v-if="loading" :icon="['fas', 'spinner']" spin-pulse class="mr-[6px]" />
              {{ nextLabel || (final ? t('Разместить') : t('Далее')) }}
              <font-awesome-icon v-if="!final && !loading" :icon="['fas', 'arrow-right']" class="ml-[6px] text-[13px]" />
            </button>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  title: { type: String, required: true },
  subtitle: String,
  error: String,
  nextLabel: String,
  loading: Boolean,
  final: Boolean,
  hideBack: Boolean,
})
defineEmits(['next'])

const route = useRoute()
const { announData } = getData()
const nav = computed(() => announStepNav(announData.value, route.path))
const summary = computed(() => {
  const d = announData.value || {}
  const deal = d.rent === 'Аренда' ? (d.typeRent === 'Посуточно' ? 'Посуточно' : 'Аренда') : d.rent
  return [deal && t(deal), d.objects && t(d.objects)].filter(Boolean).join(' · ') || t('Выберите тип')
})

useHead({ title: computed(() => t('{x} — новое объявление', { x: props.title })) })
</script>

<!-- Общие стили полей формы для всех шагов (не scoped — используются внутри слотов) -->
<style>
.wz-label {
  display: block;
  font-size: 14px;
  font-weight: 600;
  color: #152242;
  margin-bottom: 8px;
}
.wz-hint {
  font-size: 12.5px;
  color: #94a3b8;
  margin-top: 6px;
}
.wz-err {
  font-size: 12.5px;
  color: #dc2626;
  margin-top: 6px;
}
.wz-input {
  width: 100%;
  height: 46px;
  padding: 0 14px;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  background: #fff;
  font-size: 15px;
  color: #152242;
  outline: none;
  transition: border-color .15s, box-shadow .15s;
}
.wz-input:focus {
  border-color: #0468ff;
  box-shadow: 0 0 0 3px rgba(4, 104, 255, .12);
}
.wz-input--bad {
  border-color: #fca5a5;
}
textarea.wz-input {
  height: auto;
  padding: 12px 14px;
  line-height: 1.5;
  resize: vertical;
}
.wz-section-title {
  font-size: 16px;
  font-weight: 700;
  color: #152242;
  padding-top: 6px;
  border-top: 1px solid #eef2f7;
}
.wz-section-title:first-child {
  border-top: 0;
  padding-top: 0;
}
input.wz-input::-webkit-outer-spin-button,
input.wz-input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
</style>

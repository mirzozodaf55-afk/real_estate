<template>
  <WzShell :title="t('Заголовок и описание')" :subtitle="t('Расскажите, чем объект хорош: что рядом, в каком состоянии, когда можно въехать')" :error="error" @next="next">
    <WzAiWrite :draft="d" />

    <label class="block">
      <span class="wz-label">{{ t('Заголовок') }}</span>
      <input v-model="d.title" maxlength="150" class="wz-input" :class="{ 'wz-input--bad': tried && !titleOk }"
        :placeholder="t('Например: Светлая двушка у парка, свежий ремонт')" data-test="title" />
      <span class="flex justify-between gap-[10px]">
        <span :class="tried && !titleOk ? 'wz-err' : 'wz-hint'">{{ tried && !titleOk ? t('Минимум 6 символов') : t('Коротко о главном — его видят в поиске') }}</span>
        <span class="wz-hint">{{ (d.title || '').length }}/150</span>
      </span>
    </label>

    <label class="block">
      <span class="wz-label">{{ t('Описание') }}</span>
      <textarea v-model="d.description" rows="8" maxlength="3000" class="wz-input" :class="{ 'wz-input--bad': tried && !descriptionOk }" data-test="description"
        :placeholder="t('Уютная квартира в тихом районе. Окна во двор, свежий ремонт 2023 года. Рядом школа, садик и остановка. Можно въезжать сразу.')"></textarea>
      <span class="flex justify-between gap-[10px]">
        <span :class="tried && !descriptionOk ? 'wz-err' : 'wz-hint'">{{ tried && !descriptionOk ? t('Напишите хотя бы пару предложений — от 15 символов') : t('Не указывайте телефон и ссылки — контакты видны в профиле') }}</span>
        <span class="wz-hint">{{ (d.description || '').length }}/3000</span>
      </span>
    </label>

    <label class="block">
      <span class="wz-label">{{ t('Видео') }} <span class="font-normal text-[#94a3b8]">{{ t('— необязательно') }}</span></span>
      <span class="relative block">
        <font-awesome-icon :icon="['fab', 'youtube']" class="absolute left-[14px] top-1/2 -translate-y-1/2 text-[#ef4444]" />
        <input v-model.trim="d.linkVideo" type="url" class="wz-input !pl-[40px]" :class="{ 'wz-input--bad': tried && !videoOk }" placeholder="https://youtube.com/…" />
      </span>
      <span v-if="tried && !videoOk" class="wz-err block">{{ t('Ссылка должна начинаться с https://') }}</span>
      <span v-else class="wz-hint block">{{ t('Видеообзор помогает продать быстрее') }}</span>
    </label>
  </WzShell>
</template>

<script setup>
const d = useAnnounDraft()
const route = useRoute()
const tried = ref(false)

const titleOk = computed(() => (d.value.title || '').trim().length >= 6)
const descriptionOk = computed(() => (d.value.description || '').trim().length >= 15)
// при редактировании сервер принимает только настоящую ссылку — проверяем сразу
const videoOk = computed(() => !d.value.linkVideo || /^https?:\/\/\S+\.\S+/.test(d.value.linkVideo))
const valid = computed(() => titleOk.value && descriptionOk.value && videoOk.value)
const error = computed(() => (tried.value && !valid.value ? 'Заполните отмеченные поля' : ''))

function next() {
  tried.value = true
  if (valid.value) navigateTo(announStepNav(d.value, route.path).next)
}

</script>

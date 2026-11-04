<template>
  <WzShell :title="t('Фото и цена')" :subtitle="t('Объявления с хорошими фото смотрят в несколько раз чаще')" :error="error" :loading="sending" final
    @next="place">
    <div>
      <span class="wz-label">{{ t('Фотографии') }}</span>
      <Pictures :invalid="tried && !images.length" />
    </div>

    <div class="wz-section-title">{{ isRent ? t('Цена и условия') : t('Цена') }}</div>

    <WzNumber v-model="d.price" :label="priceLabel" :suffix="t('сомони')" :max="10" width="max-w-[280px]"
      :invalid="tried && !priceOk" :error="t('Укажите цену — от 100 сомони')" :hint="pricePreview" />

    <template v-if="isRent">
      <WzChoice multiple :label="t('Можно')" :options="ALLOW" :model-value="allowed" @update:model-value="setAllowed" :hint="t('Необязательно')" />
      <WzChoice v-if="d.typeRent !== 'Посуточно'" v-model="d.period" :label="t('Срок договора')" :options="['От года', 'Несколько месяцев']" :hint="t('Необязательно')" />
    </template>

    <div class="rounded-[14px] bg-[#f8fafc] border border-[#eef2f7] p-[14px] text-[13.5px] text-[#475569] leading-[1.55]">
      <font-awesome-icon :icon="['fas', 'shield-halved']" class="text-[#0468ff] mr-[6px]" />
      {{ t('После нажатия «Разместить» объявление проверит модератор — обычно это недолго. Когда его опубликуют, придёт уведомление и письмо на почту.') }}
    </div>
  </WzShell>
</template>

<script setup>
const d = useAnnounDraft()
const { images } = getData()
const tried = ref(false)
const sending = ref(false)
const serverError = ref('')

const isRent = computed(() => d.value.rent === 'Аренда')
const priceLabel = computed(() => (!isRent.value ? 'Цена' : d.value.typeRent === 'Посуточно' ? 'Цена за сутки' : 'Аренда в месяц'))
const priceOk = computed(() => d.value.price >= 100)
const pricePreview = computed(() => (d.value.price ? `В объявлении: ${formatNumber(d.value.price)} сомони${isRent.value ? (d.value.typeRent === 'Посуточно' ? ' / сутки' : ' / мес') : ''}` : ''))

// «Можно с детьми / с животными» — в черновике отдельные поля (allowKids/allowAnimals), «нет» если не отмечено
const ALLOW = [
  { value: 'Можно с детьми', label: 'С детьми', icon: ['fas', 'child'] },
  { value: 'Можно с животными', label: 'С животными', icon: ['fas', 'paw'] },
]
const allowed = computed(() => ALLOW.map((a) => a.value).filter((v) => d.value.allowKids === v || d.value.allowAnimals === v))
function setAllowed(list) {
  d.value.allowKids = list.includes('Можно с детьми') ? 'Можно с детьми' : 'нет'
  d.value.allowAnimals = list.includes('Можно с животными') ? 'Можно с животными' : 'нет'
}

const valid = computed(() => images.value.length > 0 && priceOk.value)
const error = computed(() => serverError.value || (tried.value && !valid.value ? (!images.value.length ? 'Добавьте хотя бы одно фото' : 'Укажите цену') : ''))

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
    // черновик и фото больше не нужны — следующее объявление начнётся с чистого листа
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

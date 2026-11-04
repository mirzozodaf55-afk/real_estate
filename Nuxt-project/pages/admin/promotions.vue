<template>
  <AdminShell v-slot="{ user: me }">
    <div v-if="me.role !== 'admin'" class="bg-white rounded-[16px] border border-[#e8edf3] p-[28px] text-center text-[15px] text-[#475569]">
      {{ t('Продвижение настраивает только администратор.') }}
    </div>
    <template v-else>
      <div class="mb-[14px]">
        <h1 class="text-[24px] font-bold text-[#152242] m-0">{{ t('Продвижение') }}</h1>
        <p class="text-[14px] text-[#64748b] m-0 mt-[2px]">{{ t('Заявки на ТОП и VIP: проверьте, что оплата пришла, и включите продвижение') }}</p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-[14px] items-start">
        <!-- Заявки -->
        <div class="min-w-0">
          <div class="flex gap-[6px] mb-[12px] overflow-x-auto">
            <button v-for="tb of TABS" :key="tb.value" @click="setTab(tb.value)"
              class="h-[36px] px-[14px] rounded-full text-[14px] font-semibold whitespace-nowrap border"
              :class="tab === tb.value ? 'bg-[#152242] text-white border-[#152242]' : 'bg-white text-[#334155] border-[#e2e8f0] hover:border-[#94a3b8]'">
              {{ t(tb.label) }}<span v-if="counts[tb.value]" class="ml-[6px] text-[12px] opacity-80">{{ counts[tb.value] }}</span>
            </button>
          </div>

          <div v-if="loading" class="flex flex-col gap-[10px]">
            <div v-for="n in 3" :key="n" class="h-[120px] rounded-[16px] bg-[#e2e8f0] animate-pulse"></div>
          </div>
          <p v-else-if="error" class="bg-white rounded-[16px] border border-[#fecaca] p-[18px] text-[#dc2626] m-0">{{ t(error) }}</p>
          <div v-else-if="!items.length" class="bg-white rounded-[16px] border border-[#e8edf3] p-[28px] text-center text-[14px] text-[#64748b]">
            {{ tab === 'pending' ? t('Новых заявок нет') : t('Здесь пока пусто') }}
          </div>

          <ul v-else class="list-none m-0 p-0 flex flex-col gap-[10px]" data-test="promo-requests">
            <li v-for="r of items" :key="r.id" class="bg-white rounded-[16px] border border-[#e8edf3] p-[14px] flex flex-col sm:flex-row gap-[14px]">
              <NuxtLink :to="`/show_announ-${r.announ_id}`" target="_blank" class="shrink-0 w-full sm:w-[140px] h-[100px] rounded-[12px] overflow-hidden bg-[#e2e8f0] block">
                <img v-if="r.announ?.pictures?.[0]" :src="`${apiBase()}/api/image/small/${r.announ.pictures[0].pictures}`" class="w-full h-full object-cover" alt="" />
              </NuxtLink>
              <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-[8px]">
                  <span class="px-[8px] py-[2px] rounded-[8px] text-[12px] font-extrabold" :class="r.plan === 'vip' ? 'promo-badge-vip text-[#5b3a00]' : 'bg-[#0468ff] text-white'">
                    <font-awesome-icon :icon="['fas', r.plan === 'vip' ? 'crown' : 'arrow-up']" class="text-[10px] mr-[3px]" />{{ r.plan === 'vip' ? 'VIP' : t('ТОП') }}
                  </span>
                  <span class="text-[16px] font-bold text-[#152242]">{{ formatNumber(r.price) }} {{ t('сомони') }}</span>
                  <span class="text-[13px] text-[#64748b]">· {{ r.days }} {{ tn(r.days, ['день', 'дня', 'дней']) }} · {{ timeAgo(r.created_at) }}</span>
                </div>
                <NuxtLink :to="`/show_announ-${r.announ_id}`" target="_blank" class="block mt-[4px] text-[14px] font-semibold text-[#0468ff] truncate no-underline">
                  {{ r.announ?.title || r.announ?.type_object || t('Объявление №{n}', { n: r.announ_id }) }}
                </NuxtLink>
                <div class="text-[13px] text-[#64748b] mt-[2px] truncate">
                  {{ [r.owner?.name, r.owner?.last_name].filter(Boolean).join(' ') || t('Без имени') }} · {{ r.owner?.email }}<span v-if="r.owner?.phone_number"> · {{ r.owner.phone_number }}</span>
                </div>
                <div class="mt-[8px] px-[10px] py-[7px] rounded-[10px] bg-[#f8fafc] border border-[#eef2f7] text-[13.5px] text-[#152242] break-words">
                  <span class="text-[#64748b]">{{ t('Оплата:') }}</span> {{ r.payment_note || '—' }}
                </div>
                <div v-if="r.status === 'rejected'" class="mt-[6px] text-[13px] text-[#b91c1c]">{{ t('Причина отказа:') }} {{ r.reason }}</div>
                <div v-if="r.status === 'approved' && r.announ?.promo_until" class="mt-[6px] text-[13px] text-[#047857]">
                  {{ t('Сейчас:') }} {{ r.announ.promo === 'vip' ? 'VIP' : t('ТОП') }} {{ t('до') }} {{ formatDate(r.announ.promo_until) }}
                </div>

                <!-- Решение -->
                <div v-if="r.status === 'pending'" class="mt-[10px] flex flex-wrap gap-[8px]">
                  <button @click="approve(r)" :disabled="busy === r.id" data-test="promo-approve"
                    class="h-[36px] px-[14px] rounded-[10px] bg-[#16a34a] hover:bg-[#15803d] text-white text-[14px] font-semibold disabled:opacity-60">
                    <font-awesome-icon :icon="['fas', busy === r.id ? 'spinner' : 'check']" :spin-pulse="busy === r.id" class="mr-[4px]" /> {{ t('Оплата получена — включить') }}
                  </button>
                  <button v-if="rejecting !== r.id" @click="rejecting = r.id; reason = ''" :disabled="busy === r.id"
                    class="h-[36px] px-[14px] rounded-[10px] border border-[#fecaca] text-[#dc2626] text-[14px] font-semibold hover:bg-[#fef2f2]">{{ t('Отклонить') }}</button>
                </div>
                <div v-if="rejecting === r.id" class="mt-[10px] flex flex-col sm:flex-row gap-[8px]">
                  <input v-model="reason" maxlength="300" :placeholder="t('Например: оплата не найдена')" class="flex-1 h-[38px] px-[12px] rounded-[10px] border border-[#e2e8f0] bg-white text-[14px] outline-none focus:border-[#dc2626]" />
                  <button @click="reject(r)" :disabled="reason.trim().length < 3 || busy === r.id" class="h-[38px] px-[14px] rounded-[10px] bg-[#dc2626] text-white text-[14px] font-semibold disabled:opacity-50">{{ t('Отклонить') }}</button>
                  <button @click="rejecting = null" class="h-[38px] px-[12px] rounded-[10px] text-[14px] text-[#64748b]">{{ t('Отмена') }}</button>
                </div>
              </div>
            </li>
          </ul>
        </div>

        <!-- Тарифы и реквизиты -->
        <form @submit.prevent="saveSettings" class="bg-white rounded-[16px] border border-[#e8edf3] p-[16px] lg:sticky lg:top-[90px]" data-test="promo-settings">
          <div class="text-[16px] font-bold text-[#152242]">{{ t('Тарифы') }}</div>
          <div v-for="key of ['vip', 'top']" :key="key" class="mt-[12px]">
            <div class="text-[13px] font-semibold text-[#334155] mb-[4px]">{{ key === 'vip' ? t('VIP — самый верх, золотая рамка') : t('ТОП — сразу под VIP') }}</div>
            <div class="grid grid-cols-2 gap-[8px]">
              <label class="block">
                <span class="text-[12px] text-[#64748b]">{{ t('Цена, сомони') }}</span>
                <input v-model.number="form[key + '_price']" type="number" min="0" class="mt-[2px] w-full h-[38px] px-[10px] rounded-[10px] border border-[#e2e8f0] bg-white text-[14px] outline-none focus:border-[#0468ff]" />
              </label>
              <label class="block">
                <span class="text-[12px] text-[#64748b]">{{ t('Дней') }}</span>
                <input v-model.number="form[key + '_days']" type="number" min="1" max="365" class="mt-[2px] w-full h-[38px] px-[10px] rounded-[10px] border border-[#e2e8f0] bg-white text-[14px] outline-none focus:border-[#0468ff]" />
              </label>
            </div>
          </div>
          <label class="block mt-[14px]">
            <span class="text-[13px] font-semibold text-[#334155]">{{ t('Реквизиты для оплаты') }}</span>
            <textarea v-model="form.requisites" rows="4" maxlength="1000" :placeholder="t('Например:\nAlif Mobi: +992 90 000 00 00 (Фирдавс М.)\nКарта Корти Милли: 0000 0000 0000 0000')"
              class="mt-[4px] w-full px-[10px] py-[8px] rounded-[10px] border border-[#e2e8f0] bg-white text-[14px] outline-none focus:border-[#0468ff]"></textarea>
            <span class="text-[12px] text-[#94a3b8]">{{ t('Их видит владелец в окне «Продвинуть»') }}</span>
          </label>
          <p v-if="settingsMsg" class="m-0 mt-[8px] text-[13px]" :class="settingsErr ? 'text-[#dc2626]' : 'text-[#047857]'">{{ t(settingsMsg) }}</p>
          <button type="submit" :disabled="saving" class="mt-[12px] w-full h-[40px] rounded-[10px] bg-[#0468ff] hover:bg-[#0353cc] text-white text-[14px] font-semibold disabled:opacity-60">
            {{ saving ? t('Сохраняю…') : t('Сохранить') }}
          </button>
        </form>
      </div>
    </template>

    <div v-if="toast" class="fixed bottom-[20px] left-1/2 -translate-x-1/2 z-[500] px-[16px] py-[10px] rounded-[12px] bg-[#152242] text-white text-[14px] shadow-lg">{{ t(toast) }}</div>
  </AdminShell>
</template>

<script setup>
useHead({ title: () => t('Админка — продвижение') })

const TABS = [
  { value: 'pending', label: 'Ждут проверки' },
  { value: 'approved', label: 'Включены' },
  { value: 'rejected', label: 'Отклонены' },
]
const tab = ref('pending')
const items = ref([])
const counts = ref({})
const loading = ref(true)
const error = ref('')
const busy = ref(null)
const rejecting = ref(null)
const reason = ref('')
const toast = ref('')
let toastTimer = null

const form = reactive({ vip_price: 0, vip_days: 7, top_price: 0, top_days: 7, requisites: '' })
const saving = ref(false)
const settingsMsg = ref('')
const settingsErr = ref(false)
let settingsLoaded = false

function showToast(text) {
  toast.value = text
  clearTimeout(toastTimer)
  toastTimer = setTimeout(() => { toast.value = '' }, 3000)
}

async function load() {
  loading.value = true
  error.value = ''
  try {
    const data = await adminApi(`/promotions?status=${tab.value}`)
    items.value = data.items
    counts.value = data.counts
    // форму тарифов заполняем один раз — чтобы не затирать то, что админ сейчас печатает
    if (!settingsLoaded) {
      Object.assign(form, {
        vip_price: data.plans.vip.price, vip_days: data.plans.vip.days,
        top_price: data.plans.top.price, top_days: data.plans.top.days,
        requisites: data.requisites,
      })
      settingsLoaded = true
    }
  } catch (e) {
    error.value = e.status === 403 ? 'Нет доступа' : 'Не удалось загрузить заявки'
  } finally {
    loading.value = false
  }
}

function setTab(value) {
  tab.value = value
  rejecting.value = null
  load()
}

async function approve(r) {
  busy.value = r.id
  try {
    await adminApi(`/promotions/${r.id}/approve`, { method: 'POST' })
    showToast('Продвижение включено, владелец получит уведомление')
    load()
  } catch (e) {
    showToast(e.message)
  } finally {
    busy.value = null
  }
}

async function reject(r) {
  busy.value = r.id
  try {
    await adminApi(`/promotions/${r.id}/reject`, { method: 'POST', body: { reason: reason.value } })
    rejecting.value = null
    showToast('Заявка отклонена')
    load()
  } catch (e) {
    showToast(e.message)
  } finally {
    busy.value = null
  }
}

async function saveSettings() {
  saving.value = true
  settingsMsg.value = ''
  try {
    await adminApi('/promotions/settings', { method: 'POST', body: { ...form } })
    settingsErr.value = false
    settingsMsg.value = 'Сохранено'
  } catch (e) {
    settingsErr.value = true
    settingsMsg.value = e.message
  } finally {
    saving.value = false
  }
}

const formatDate = (d) => (d ? new Date(d).toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '')

onMounted(load)
onUnmounted(() => clearTimeout(toastTimer))
</script>

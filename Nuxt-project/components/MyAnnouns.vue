<template>
  <section class="flex-1 min-w-0 p-[16px] md:p-[24px]">
    <div class="max-w-[860px]">
      <div class="flex flex-wrap items-center justify-between gap-[12px] mb-[14px]">
        <div>
          <h2 class="text-[22px] font-bold text-[#152242] m-0">Мои объявления</h2>
          <p class="text-[14px] text-[#64748b] m-0 mt-[2px]">Редактируйте, снимайте с публикации и возвращайте объявления</p>
        </div>
        <NuxtLink to="/announ" class="h-[40px] px-[16px] rounded-[10px] bg-[#0468ff] hover:bg-[#0353cc] text-white text-[14px] font-semibold flex items-center gap-[8px] no-underline">
          <font-awesome-icon :icon="['fas', 'plus']" /> Разместить объявление
        </NuxtLink>
      </div>

      <div class="inline-flex bg-[#eef2f7] rounded-[10px] p-[3px] mb-[16px]">
        <button v-for="t of tabs" :key="t.value" @click="tab = t.value"
          class="px-[14px] h-[34px] rounded-[8px] text-[14px] font-semibold transition-colors flex items-center gap-[6px]"
          :class="tab === t.value ? 'bg-white text-[#0468ff] shadow-sm' : 'text-[#475569] hover:text-[#152242]'">
          {{ t.label }}
          <span class="text-[12px] px-[6px] rounded-full" :class="tab === t.value ? 'bg-[#eef4ff]' : 'bg-white/70'">{{ t.count }}</span>
        </button>
      </div>

      <div v-if="loading" class="flex flex-col gap-[12px]">
        <div v-for="n in 2" :key="n" class="h-[170px] bg-white rounded-[16px] border border-[#e8edf3] animate-pulse"></div>
      </div>

      <div v-else-if="error" class="bg-white rounded-[16px] border border-[#e8edf3] p-[28px] text-center">
        <p class="text-[#152242] font-semibold m-0">{{ error }}</p>
        <button @click="load" class="mt-[12px] h-[38px] px-[18px] rounded-[10px] bg-[#0468ff] text-white text-[14px] font-semibold">Повторить</button>
      </div>

      <div v-else-if="!visible.length" class="bg-white rounded-[16px] border border-[#e8edf3] p-[32px] text-center">
        <div class="w-[64px] h-[64px] mx-auto rounded-full bg-[#eef4ff] grid place-items-center text-[#0468ff] text-[26px]">
          <font-awesome-icon :icon="['far', tab === 'active' ? 'newspaper' : 'folder-open']" />
        </div>
        <p class="text-[18px] font-bold text-[#152242] mt-[14px] mb-[4px]">{{ tab === 'active' ? 'Нет активных объявлений' : 'Здесь пусто' }}</p>
        <p class="text-[14px] text-[#64748b] m-0">{{ {
          active: 'Разместите первое объявление — это займёт пару минут.',
          sold: 'Когда продадите или сдадите объект, нажмите «Продано» / «Сдано» в карточке — объявление переедет сюда.',
          archived: 'Сюда попадают объявления, снятые с публикации.',
        }[tab] }}</p>
      </div>

      <ul v-else class="list-none p-0 m-0 flex flex-col gap-[12px]">
        <li v-for="a of visible" :key="a.id" class="bg-white rounded-[16px] border border-[#e8edf3] overflow-hidden flex flex-col sm:flex-row">
          <div class="relative sm:w-[220px] sm:shrink-0 aspect-[16/10] sm:aspect-auto sm:h-auto bg-[#e2e8f0]">
            <img v-if="cover(a)" :src="`${apiBase()}/api/image/small/${cover(a).pictures}`" :style="`transform: rotate(${cover(a).rotation || 0}deg)`"
              class="w-full h-full object-cover" :class="a.deleted_at ? 'grayscale opacity-70' : ''" alt="" loading="lazy" />
            <div v-else class="w-full h-full min-h-[140px] grid place-items-center text-[#94a3b8] text-[28px]"><font-awesome-icon :icon="['far', 'image']" /></div>
            <span class="absolute top-[10px] left-[10px] px-[8px] py-[3px] rounded-[8px] text-[12px] font-semibold" :class="status(a).cls" data-test="status">
              {{ status(a).label }}
            </span>
            <span v-if="a.pictures?.length" class="absolute bottom-[8px] right-[8px] px-[7px] py-[1px] rounded-[8px] bg-black/50 text-white text-[11px]">
              <font-awesome-icon :icon="['fas', 'camera']" class="text-[10px]" /> {{ a.pictures.length }}
            </span>
          </div>

          <div class="flex-1 min-w-0 p-[14px] md:p-[16px] flex flex-col">
            <div class="flex items-start justify-between gap-[10px]">
              <div class="text-[20px] font-bold text-[#152242] leading-tight">
                {{ formatNumber(a.price) }} <span class="text-[14px] font-semibold">сомони</span><span class="text-[13px] font-normal text-[#64748b]">{{ priceSuffix(a) }}</span>
              </div>
              <span class="text-[12px] text-[#94a3b8] whitespace-nowrap mt-[4px]">{{ dateLabel(a) }}</span>
            </div>
            <div class="text-[15px] font-semibold text-[#0468ff] mt-[6px] line-clamp-1">{{ a.title || a.type_object }}</div>
            <div class="text-[13px] text-[#64748b] mt-[3px]">{{ facts(a) }}</div>
            <div class="flex items-center gap-[14px] text-[13px] text-[#64748b] mt-[8px]">
              <span class="flex items-center gap-[5px]"><font-awesome-icon :icon="['far', 'heart']" class="text-[#ef4444]" /> {{ a.likes_count }} {{ plural(a.likes_count, ['в избранном', 'в избранном', 'в избранном']) }}</span>
              <span class="px-[8px] py-[1px] rounded-[6px] bg-[#f1f5f9] text-[12px]">{{ dealLabel(a) }}</span>
            </div>

            <div v-if="!a.deleted_at && a.moderation_status === 'rejected'" class="mt-[10px] px-[12px] py-[9px] rounded-[10px] bg-[#fef2f2] text-[13px] text-[#991b1b] leading-[1.45]" data-test="reject-reason">
              <b>Модератор отклонил объявление:</b> {{ a.moderation_reason || 'причина не указана' }}
              <div class="text-[#b91c1c]/80 mt-[2px]">Исправьте его и сохраните — оно снова уйдёт на проверку.</div>
            </div>
            <div v-else-if="!a.deleted_at && a.moderation_status === 'pending'" class="mt-[10px] px-[12px] py-[8px] rounded-[10px] bg-[#fffbeb] text-[13px] text-[#92400e]">
              <font-awesome-icon :icon="['far', 'clock']" class="mr-[4px]" /> Объявление появится в поиске после проверки модератором
            </div>

            <div v-if="a.sold_at && !a.deleted_at" class="mt-[10px] px-[12px] py-[8px] rounded-[10px] bg-[#eef2ff] text-[13px] text-[#3730a3]">
              <font-awesome-icon :icon="['fas', 'circle-check']" class="mr-[4px]" />
              {{ soldWord(a) }} {{ timeAgo(a.sold_at) }} — объявление скрыто из поиска
            </div>

            <!-- Продвижение (ТОП/VIP) -->
            <template v-if="!a.deleted_at && !a.sold_at">
              <div v-if="a.promo_active" class="mt-[10px] px-[12px] py-[8px] rounded-[10px] text-[13px] font-semibold" data-test="promo-active"
                :class="a.promo_active === 'vip' ? 'promo-badge-vip text-[#5b3a00]' : 'bg-[#eef4ff] text-[#0468ff]'">
                <font-awesome-icon :icon="['fas', a.promo_active === 'vip' ? 'crown' : 'arrow-up']" class="mr-[4px]" />
                {{ a.promo_active === 'vip' ? 'VIP' : 'ТОП' }} до {{ formatDate(a.promo_until) }} — объявление выше обычных в поиске
              </div>
              <div v-if="promos[a.id]?.status === 'pending'" class="mt-[10px] px-[12px] py-[8px] rounded-[10px] bg-[#fffbeb] text-[13px] text-[#92400e]" data-test="promo-pending">
                <font-awesome-icon :icon="['far', 'clock']" class="mr-[4px]" />
                Заявка на {{ promos[a.id].plan === 'vip' ? 'VIP' : 'ТОП' }} ждёт проверки оплаты администратором
              </div>
              <div v-else-if="promos[a.id]?.status === 'rejected' && !a.promo_active" class="mt-[10px] px-[12px] py-[8px] rounded-[10px] bg-[#fef2f2] text-[13px] text-[#991b1b]">
                <b>Заявка на продвижение отклонена:</b> {{ promos[a.id].reason }}
              </div>
            </template>

            <div class="mt-auto pt-[12px] flex flex-wrap gap-[8px]">
              <button v-if="!a.deleted_at" @click="open(a)" class="h-[36px] px-[12px] rounded-[10px] bg-[#eef4ff] text-[#0468ff] text-[14px] font-semibold hover:bg-[#dbe8ff]">
                Открыть
              </button>
              <template v-if="a.sold_at && !a.deleted_at">
                <button @click="act('unsold', a)" :disabled="busy === a.id" data-test="unsold"
                  class="h-[36px] px-[12px] rounded-[10px] bg-[#ecfdf5] text-[#047857] text-[14px] font-semibold hover:bg-[#d1fae5]">
                  <font-awesome-icon v-if="busy === a.id" :icon="['fas', 'spinner']" spin-pulse /> Сделка сорвалась — вернуть в поиск
                </button>
                <button @click="ask('destroy', a)" :disabled="busy === a.id"
                  class="h-[36px] px-[12px] rounded-[10px] border border-[#fecaca] text-[#dc2626] text-[14px] font-semibold hover:bg-[#fef2f2]">
                  Удалить навсегда
                </button>
              </template>
              <template v-else>
              <NuxtLink :to="{ path: '/update-announ', query: { id: a.id } }" class="h-[36px] px-[12px] rounded-[10px] border border-[#e2e8f0] text-[#152242] text-[14px] font-semibold flex items-center gap-[6px] no-underline hover:border-[#0468ff]">
                <font-awesome-icon :icon="['fas', 'pen']" class="text-[12px]" /> Редактировать
              </NuxtLink>
              <template v-if="!a.deleted_at">
                <button v-if="a.moderation_status === 'approved' && promos[a.id]?.status !== 'pending'" @click="openPromo(a)" data-test="promote"
                  class="promo-badge-vip h-[36px] px-[12px] rounded-[10px] text-[#5b3a00] text-[14px] font-bold hover:brightness-105">
                  <font-awesome-icon :icon="['fas', 'rocket']" class="mr-[4px]" /> {{ a.promo_active ? 'Продлить' : 'Продвинуть' }}
                </button>
                <button @click="ask('sold', a)" :disabled="busy === a.id" data-test="mark-sold"
                  class="h-[36px] px-[12px] rounded-[10px] bg-[#eef2ff] text-[#4338ca] text-[14px] font-semibold hover:bg-[#e0e7ff]">
                  <font-awesome-icon :icon="['fas', 'handshake']" class="mr-[4px]" /> {{ soldWord(a) }}
                </button>
                <button @click="ask('unpublish', a)" :disabled="busy === a.id"
                  class="h-[36px] px-[12px] rounded-[10px] border border-[#e2e8f0] text-[#475569] text-[14px] font-semibold hover:border-[#f59e0b] hover:text-[#b45309]">
                  Снять с публикации
                </button>
              </template>
              <template v-else>
                <button @click="act('publish', a)" :disabled="busy === a.id"
                  class="h-[36px] px-[12px] rounded-[10px] bg-[#ecfdf5] text-[#047857] text-[14px] font-semibold hover:bg-[#d1fae5]">
                  <font-awesome-icon v-if="busy === a.id" :icon="['fas', 'spinner']" spin-pulse /> Опубликовать снова
                </button>
                <button @click="ask('destroy', a)" :disabled="busy === a.id"
                  class="h-[36px] px-[12px] rounded-[10px] border border-[#fecaca] text-[#dc2626] text-[14px] font-semibold hover:bg-[#fef2f2]">
                  Удалить навсегда
                </button>
              </template>
              </template>
            </div>
          </div>
        </li>
      </ul>
    </div>

    <!-- Подтверждение -->
    <div v-if="confirm" class="fixed inset-0 z-[500] bg-black/45 flex items-center justify-center p-[16px]" @click.self="confirm = null">
      <div class="w-full max-w-[420px] bg-white rounded-[16px] p-[20px] shadow-xl">
        <h3 class="text-[18px] font-bold text-[#152242] m-0">{{ confirmText.title }}</h3>
        <p class="text-[14px] text-[#475569] mt-[8px] mb-0">{{ confirmText.body }}</p>
        <div class="flex justify-end gap-[8px] mt-[18px]">
          <button @click="confirm = null" class="h-[38px] px-[16px] rounded-[10px] border border-[#e2e8f0] text-[14px] font-semibold text-[#152242]">Отмена</button>
          <button @click="runConfirm" class="h-[38px] px-[16px] rounded-[10px] text-white text-[14px] font-semibold"
            :class="confirm.action === 'destroy' ? 'bg-[#dc2626] hover:bg-[#b91c1c]' : 'bg-[#0468ff] hover:bg-[#0353cc]'">
            {{ confirmText.button }}
          </button>
        </div>
      </div>
    </div>

    <!-- Продвижение: выбор тарифа, реквизиты, номер перевода -->
    <div v-if="promo" class="fixed inset-0 z-[500] bg-black/50 flex items-end sm:items-center justify-center" @click.self="closePromo" data-test="promo-modal">
      <div class="w-full sm:w-[480px] max-h-[92vh] overflow-y-auto bg-white rounded-t-[20px] sm:rounded-[18px] p-[20px] shadow-xl">
        <div class="flex items-start justify-between gap-[10px]">
          <div class="min-w-0">
            <h3 class="m-0 text-[19px] font-bold text-[#152242]">Продвинуть объявление</h3>
            <p class="m-0 mt-[4px] text-[13px] text-[#64748b] line-clamp-1">{{ promo.announ.title || promo.announ.type_object }}</p>
          </div>
          <button type="button" @click="closePromo" aria-label="Закрыть" class="w-[34px] h-[34px] shrink-0 rounded-full grid place-items-center text-[#64748b] hover:bg-[#f1f5f9]">
            <font-awesome-icon :icon="['fas', 'xmark']" class="text-[18px]" />
          </button>
        </div>

        <div v-if="!plans" class="py-[30px] text-center text-[#94a3b8]">
          <span v-if="promo.error" class="text-[#dc2626] text-[14px]">{{ promo.error }}</span>
          <font-awesome-icon v-else :icon="['fas', 'spinner']" spin-pulse />
        </div>
        <template v-else>
          <div class="grid grid-cols-2 gap-[10px] mt-[16px]">
            <button v-for="key of ['vip', 'top']" :key="key" type="button" @click="promo.plan = key" :data-test="`plan-${key}`"
              class="relative text-left p-[14px] rounded-[14px] border-2 transition-colors"
              :class="promo.plan === key ? (key === 'vip' ? 'border-[#f5b83d] bg-[#fffbeb]' : 'border-[#0468ff] bg-[#eef4ff]') : 'border-[#e2e8f0] bg-white hover:border-[#cbd5e1]'">
              <span class="inline-flex items-center px-[8px] py-[2px] rounded-[8px] text-[12px] font-extrabold"
                :class="key === 'vip' ? 'promo-badge-vip text-[#5b3a00]' : 'bg-[#0468ff] text-white'">
                <font-awesome-icon :icon="['fas', key === 'vip' ? 'crown' : 'arrow-up']" class="text-[10px] mr-[3px]" />{{ plans[key].label }}
              </span>
              <div class="mt-[8px] text-[20px] font-bold text-[#152242]">{{ formatNumber(plans[key].price) }} <span class="text-[13px] font-semibold">сомони</span></div>
              <div class="text-[12.5px] text-[#64748b]">на {{ plans[key].days }} {{ plural(plans[key].days, ['день', 'дня', 'дней']) }}</div>
              <div class="mt-[6px] text-[12.5px] text-[#334155] leading-[1.4]">{{ plans[key].text }}</div>
            </button>
          </div>

          <div class="mt-[16px] p-[14px] rounded-[14px] bg-[#f8fafc] border border-[#e8edf3]">
            <div class="text-[14px] font-semibold text-[#152242]">1. Переведите {{ formatNumber(plans[promo.plan].price) }} сомони</div>
            <p v-if="requisites" class="m-0 mt-[6px] text-[14px] text-[#334155] whitespace-pre-line" data-test="requisites">{{ requisites }}</p>
            <p v-else class="m-0 mt-[6px] text-[13px] text-[#b45309]">Реквизиты для оплаты ещё не указаны — напишите администратору сайта.</p>
          </div>
          <label class="block mt-[12px]">
            <span class="text-[14px] font-semibold text-[#152242]">2. Номер перевода или с какой карты оплатили</span>
            <input v-model="promo.note" maxlength="300" data-test="promo-note" placeholder="Например: Alif, перевод №123456, карта *4821"
              class="mt-[6px] w-full h-[44px] px-[12px] rounded-[10px] border border-[#e2e8f0] bg-white text-[15px] text-[#152242] outline-none focus:border-[#0468ff]" />
          </label>
          <p v-if="promo.error" class="m-0 mt-[10px] text-[13px] text-[#dc2626]">{{ promo.error }}</p>
          <button type="button" @click="sendPromo" :disabled="promo.sending || promo.note.trim().length < 3" data-test="promo-send"
            class="mt-[14px] w-full h-[46px] rounded-[12px] bg-[#0468ff] hover:bg-[#0353cc] text-white text-[15px] font-bold disabled:opacity-50">
            <font-awesome-icon v-if="promo.sending" :icon="['fas', 'spinner']" spin-pulse class="mr-[6px]" />Я оплатил — отправить заявку
          </button>
          <p class="m-0 mt-[10px] text-[12px] text-[#94a3b8] text-center">Администратор проверит оплату и включит продвижение — придёт уведомление</p>
        </template>
      </div>
    </div>

    <div v-if="toast" class="fixed bottom-[20px] left-1/2 -translate-x-1/2 z-[500] px-[16px] py-[10px] rounded-[12px] bg-[#152242] text-white text-[14px] shadow-lg">
      {{ toast }}
    </div>
  </section>
</template>

<script setup>
useHead({ title: 'Мои объявления' })

const route = useRoute()
const router = useRouter()
const { user } = getUserByServer(getToken())

const items = ref([])
const loading = ref(true)
const error = ref('')
const tab = ref('active')
const busy = ref(null)
const confirm = ref(null)
const toast = ref(route.query.saved ? 'Изменения сохранены и отправлены на проверку'
  : route.query.created ? 'Объявление отправлено на проверку — сообщим, когда его опубликуют' : '')
let toastTimer = null

const active = computed(() => items.value.filter((a) => !a.deleted_at && !a.sold_at))
const sold = computed(() => items.value.filter((a) => !a.deleted_at && a.sold_at))
const archived = computed(() => items.value.filter((a) => a.deleted_at))
const visible = computed(() => ({ active: active.value, sold: sold.value, archived: archived.value }[tab.value]))
const tabs = computed(() => [
  { value: 'active', label: 'Активные', count: active.value.length },
  { value: 'sold', label: 'Продано / сдано', count: sold.value.length },
  { value: 'archived', label: 'Сняты с публикации', count: archived.value.length },
])

const isRent = (a) => a.deal_type === 'Аренда'
const soldWord = (a) => (isRent(a) ? 'Сдано' : 'Продано')

const confirmText = computed(() => {
  const a = confirm.value?.announ
  return {
    destroy: { title: 'Удалить навсегда?', body: 'Объявление, его фото и отметки «в избранном» будут удалены без возможности восстановления.', button: 'Удалить' },
    unpublish: { title: 'Снять с публикации?', body: 'Объявление перестанут видеть в поиске. Вернуть его можно в любой момент во вкладке «Сняты с публикации».', button: 'Снять' },
    sold: a && { title: isRent(a) ? 'Квартира сдана?' : 'Объект продан?',
      body: 'Объявление уберём из поиска, звонить и писать по нему перестанут. Оно останется во вкладке «Продано / сдано» — если сделка сорвётся, его можно вернуть.',
      button: `Да, ${soldWord(a).toLowerCase()}` },
  }[confirm.value?.action] || {}
})

const headers = () => ({ Accept: 'application/json', Authorization: 'Bearer ' + getToken() })

function showToast(text) {
  toast.value = text
  clearTimeout(toastTimer)
  toastTimer = setTimeout(() => { toast.value = '' }, 3000)
}

async function load() {
  loading.value = true
  error.value = ''
  try {
    const res = await fetch(`${apiBase()}/api/my/announs`, { headers: headers() })
    if (!res.ok) throw new Error()
    items.value = (await res.json()).data
  } catch (e) {
    error.value = 'Не удалось загрузить объявления'
  } finally {
    loading.value = false
  }
}

function ask(action, a) {
  confirm.value = { action, announ: a }
}

async function runConfirm() {
  const { action, announ } = confirm.value
  confirm.value = null
  await act(action, announ)
}

async function act(action, a) {
  busy.value = a.id
  const request = {
    unpublish: [`/api/my/announs/${a.id}/unpublish`, 'POST', 'Объявление снято с публикации'],
    publish: [`/api/my/announs/${a.id}/publish`, 'POST', 'Объявление снова опубликовано'],
    destroy: [`/api/my/announs/${a.id}`, 'DELETE', 'Объявление удалено'],
    sold: [`/api/my/announs/${a.id}/sold`, 'POST', 'Объявление убрано из поиска'],
    unsold: [`/api/my/announs/${a.id}/unsold`, 'POST', 'Объявление снова в поиске'],
  }[action]
  try {
    const res = await fetch(apiBase() + request[0], { method: request[1], headers: headers() })
    if (!res.ok) throw new Error()
    if (action === 'destroy') items.value = items.value.filter((i) => i.id !== a.id)
    else if (action === 'sold' || action === 'unsold') a.sold_at = action === 'sold' ? new Date().toISOString() : null
    else a.deleted_at = action === 'unpublish' ? new Date().toISOString() : null
    // сервер сам говорит, вернулось ли объявление в поиск сразу или ждёт проверки
    showToast((await res.json().catch(() => ({}))).message || request[2])
  } catch (e) {
    showToast('Не получилось, попробуйте ещё раз')
  } finally {
    busy.value = null
  }
}

// --- Продвижение (ТОП/VIP): тарифы с сервера, последняя заявка по каждому объявлению
const promos = ref({})
const plans = ref(null)
const requisites = ref('')
const promo = ref(null)

async function loadPromos() {
  try {
    const res = await fetch(`${apiBase()}/api/my/promotions`, { headers: headers() })
    if (res.ok) promos.value = await res.json()
  } catch (e) { /* без статусов заявок кнопка всё равно работает */ }
}

async function openPromo(a) {
  promo.value = { announ: a, plan: 'vip', note: '', sending: false, error: '' }
  if (plans.value) return
  try {
    const res = await fetch(`${apiBase()}/api/promo/plans`, { headers: { Accept: 'application/json' } })
    if (!res.ok) throw new Error()
    const data = await res.json()
    plans.value = data.plans
    requisites.value = data.requisites
  } catch (e) {
    if (promo.value) promo.value.error = 'Не удалось загрузить тарифы, попробуйте ещё раз'
  }
}
function closePromo() {
  if (!promo.value?.sending) promo.value = null
}

async function sendPromo() {
  const p = promo.value
  p.sending = true
  p.error = ''
  try {
    const res = await fetch(`${apiBase()}/api/my/announs/${p.announ.id}/promote`, {
      method: 'POST',
      headers: { ...headers(), 'Content-Type': 'application/json' },
      body: JSON.stringify({ plan: p.plan, payment_note: p.note }),
    })
    const json = await res.json().catch(() => ({}))
    if (!res.ok) {
      p.error = json.message || 'Не удалось отправить заявку'
      return
    }
    promos.value = { ...promos.value, [p.announ.id]: json.request }
    promo.value = null
    showToast(json.message)
  } catch (e) {
    p.error = 'Нет связи с сервером'
  } finally {
    p.sending = false
  }
}

const formatDate = (d) => (d ? new Date(d).toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '')

function open(a) {
  // по номеру: страница сама загрузит объявление (владелец видит его и на проверке, и снятым)
  router.push(`/show_announ-${a.id}`)
}

const cover = (a) => a.pictures?.[0]

function status(a) {
  if (a.deleted_at) return { label: 'Снято с публикации', cls: 'bg-[#f1f5f9] text-[#475569]' }
  if (a.sold_at) return { label: soldWord(a), cls: 'bg-[#eef2ff] text-[#4338ca]' }
  if (a.moderation_status === 'pending') return { label: 'На проверке', cls: 'bg-[#fffbeb] text-[#b45309]' }
  if (a.moderation_status === 'rejected') return { label: 'Отклонено', cls: 'bg-[#fef2f2] text-[#dc2626]' }
  return { label: 'Опубликовано', cls: 'bg-[#ecfdf5] text-[#047857]' }
}

function dealLabel(a) {
  if (a.deal_type === 'Аренда') return a.rental_type === 'Посуточно' ? 'Посуточно' : 'Аренда'
  return a.deal_type || 'Объявление'
}

function facts(a) {
  const parts = [a.type_object]
  if (a.quantity_room) parts.push(`${a.quantity_room}-комн.`)
  const area = a.total_area || a.house_area || a.building_area
  if (area) parts.push(`${area} м²`)
  if (a.land_area) parts.push(`${a.land_area} сот.`)
  if (a.floor && a.floor_in_house) parts.push(`${a.floor}/${a.floor_in_house} эт.`)
  return parts.filter(Boolean).join(' · ')
}

function dateLabel(a) {
  if (a.deleted_at) return `снято ${timeAgo(a.deleted_at)}`
  const updated = a.updated_at && a.updated_at !== a.created_at
  return updated ? `обновлено ${timeAgo(a.updated_at)}` : `размещено ${timeAgo(a.created_at)}`
}

onMounted(() => {
  load()
  loadPromos()
  if (toast.value) {
    toastTimer = setTimeout(() => { toast.value = '' }, 3000)
    router.replace({ query: {} })
  }
})
onUnmounted(() => clearTimeout(toastTimer))
</script>

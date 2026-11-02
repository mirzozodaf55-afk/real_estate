<template>
  <section class="flex-1 min-w-0 p-[16px] md:p-[24px]">
    <div class="flex items-end justify-between gap-[12px] mb-[16px]">
      <div>
        <h1 class="m-0 text-[22px] font-bold text-[#152242]">Избранное</h1>
        <p v-if="likes.length" class="m-0 mt-[2px] text-[14px] text-[#64748b]">{{ likes.length }} {{ plural(likes.length, ['объявление', 'объявления', 'объявлений']) }}</p>
      </div>
    </div>

    <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-[14px]">
      <div v-for="n in 3" :key="n" class="h-[320px] rounded-[16px] bg-[#e2e8f0] animate-pulse"></div>
    </div>

    <p v-else-if="error" class="m-0 bg-white rounded-[16px] border border-[#fecaca] p-[18px] text-[14px] text-[#dc2626]">{{ error }}</p>

    <div v-else-if="!likes.length" class="bg-white rounded-[16px] border border-[#e8edf3] p-[32px] text-center" data-test="favorites-empty">
      <div class="w-[56px] h-[56px] mx-auto rounded-full bg-[#fef2f2] text-[#ef4444] grid place-items-center text-[22px]">
        <font-awesome-icon :icon="['far', 'heart']" />
      </div>
      <p class="m-0 mt-[12px] text-[16px] font-semibold text-[#152242]">В избранном пока пусто</p>
      <p class="m-0 mt-[4px] text-[14px] text-[#64748b]">Нажимайте на сердечко в объявлениях — они появятся здесь</p>
      <NuxtLink to="/list-announ" class="inline-flex items-center gap-[8px] mt-[14px] h-[42px] px-[18px] rounded-[10px] bg-[#0468ff] text-white text-[14px] font-semibold no-underline">
        Смотреть объявления
      </NuxtLink>
    </div>

    <ul v-else class="list-none m-0 p-0 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-[14px]" data-test="favorites">
      <template v-for="a of likes" :key="a.id">
      <!-- объявление стало недоступно (продано, снято, на проверке после правки): содержимое не показываем, можно только убрать -->
      <li v-if="a.available === false" class="bg-white rounded-[16px] border border-dashed border-[#cbd5e1] p-[16px] flex flex-col justify-between gap-[12px] min-h-[160px]" data-test="favorite-unavailable">
        <div class="flex items-start gap-[12px]">
          <span class="w-[44px] h-[44px] shrink-0 rounded-[12px] bg-[#f1f5f9] text-[#94a3b8] grid place-items-center text-[18px]"><font-awesome-icon :icon="['far', 'eye-slash']" /></span>
          <div class="min-w-0">
            <div class="text-[15px] font-semibold text-[#334155]">{{ a.unavailable_reason }}</div>
            <div class="text-[13px] text-[#94a3b8] mt-[2px]">{{ [dealLabel(a), a.type_object].filter(Boolean).join(' · ') }} — объявление больше не показывается в поиске</div>
          </div>
        </div>
        <button type="button" @click="removeLike(a)" :disabled="busy === a.id" class="self-start h-[36px] px-[14px] rounded-[10px] border border-[#e2e8f0] text-[14px] font-semibold text-[#475569] hover:border-[#ef4444] hover:text-[#dc2626]">
          <font-awesome-icon :icon="['fas', busy === a.id ? 'spinner' : 'heart-crack']" :spin-pulse="busy === a.id" class="mr-[4px]" /> Убрать из избранного
        </button>
      </li>
      <li v-else @click="openAnnoun(a)"
        class="group bg-white rounded-[16px] border overflow-hidden cursor-pointer transition-shadow hover:shadow-[0_10px_30px_rgba(15,23,42,0.1)]"
        :class="a.promo_active === 'vip' ? 'promo-vip border-[#f5c451]' : 'border-[#e8edf3]'">
        <div class="relative aspect-[4/3] bg-[#e2e8f0] overflow-hidden" @mouseenter="a.warm = true">
          <!-- лента фото: плавный сдвиг, как в списке объявлений -->
          <div v-if="photos(a).length" class="flex h-full transition-transform duration-[400ms] ease-[cubic-bezier(.22,.61,.36,1)]"
            :style="`transform: translateX(-${(a.photoIndex || 0) * 100}%)`">
            <img v-for="(p, idx) of photos(a)" :key="p.id || idx" :src="idx <= (a.photoIndex || 0) + (a.warm ? 1 : 0) ? `${apiBase()}/api/image/medium/${p.pictures}` : undefined"
              :style="`transform: rotate(${p.rotation || 0}deg)`" :loading="idx ? 'eager' : 'lazy'" draggable="false"
              class="w-full h-full shrink-0 object-cover" alt="" />
          </div>
          <div v-else class="w-full h-full grid place-items-center text-[#94a3b8] text-[30px]"><font-awesome-icon :icon="['far', 'image']" /></div>

          <div class="absolute top-[10px] left-[10px] flex gap-[6px]">
            <span v-if="a.promo_active === 'vip'" class="promo-badge-vip px-[7px] py-[2px] rounded-[8px] text-[11.5px] font-extrabold text-[#5b3a00] shadow-sm"><font-awesome-icon :icon="['fas', 'crown']" class="text-[9px] mr-[3px]" />VIP</span>
            <span v-else-if="a.promo_active === 'top'" class="px-[7px] py-[2px] rounded-[8px] bg-[#0468ff] text-[11.5px] font-extrabold text-white shadow-sm"><font-awesome-icon :icon="['fas', 'arrow-up']" class="text-[9px] mr-[3px]" />ТОП</span>
            <span class="px-[8px] py-[2px] rounded-[8px] bg-white text-[12px] font-semibold text-[#152242] shadow-sm">{{ dealLabel(a) }}</span>
          </div>
          <button type="button" @click.stop="removeLike(a)" :disabled="busy === a.id" aria-label="Убрать из избранного" title="Убрать из избранного" data-test="unlike"
            class="absolute top-[10px] right-[10px] w-[36px] h-[36px] rounded-full bg-white grid place-items-center shadow-sm hover:scale-105 transition-transform">
            <font-awesome-icon :icon="['fas', busy === a.id ? 'spinner' : 'heart']" :spin-pulse="busy === a.id" class="text-[#ef4444]" />
          </button>

          <template v-if="photos(a).length > 1">
            <button @click.stop="shift(a, -1)" aria-label="Предыдущее фото"
              class="absolute left-[8px] top-1/2 -translate-y-1/2 w-[30px] h-[30px] rounded-full bg-black/45 text-white grid place-items-center md:opacity-0 md:group-hover:opacity-100 transition-opacity">
              <font-awesome-icon :icon="['fas', 'chevron-left']" class="text-[12px]" />
            </button>
            <button @click.stop="shift(a, 1)" aria-label="Следующее фото"
              class="absolute right-[8px] top-1/2 -translate-y-1/2 w-[30px] h-[30px] rounded-full bg-black/45 text-white grid place-items-center md:opacity-0 md:group-hover:opacity-100 transition-opacity">
              <font-awesome-icon :icon="['fas', 'chevron-right']" class="text-[12px]" />
            </button>
            <span class="absolute bottom-[8px] right-[10px] px-[7px] py-[1px] rounded-[8px] bg-black/50 text-white text-[11px]">
              {{ (a.photoIndex || 0) + 1 }}/{{ photos(a).length }}
            </span>
          </template>
        </div>

        <div class="p-[14px]">
          <div class="text-[19px] font-bold text-[#152242]">
            {{ formatNumber(a.price) }} <span class="text-[13px] font-semibold">сомони</span><span class="text-[12px] font-normal text-[#64748b]">{{ priceSuffix(a) }}</span>
          </div>
          <div class="text-[14.5px] font-semibold text-[#0468ff] line-clamp-1 mt-[2px]">{{ a.title || a.type_object }}</div>
          <div v-if="announFacts(a)" class="text-[13px] text-[#334155] truncate mt-[4px]">{{ announFacts(a) }}</div>
          <div v-if="a.address" class="text-[12.5px] text-[#94a3b8] truncate mt-[4px]">
            <font-awesome-icon :icon="['fas', 'location-dot']" class="mr-[3px]" />{{ a.address }}
          </div>
          <p v-if="a.description" class="m-0 mt-[8px] text-[13px] text-[#64748b] leading-[1.45] line-clamp-2">{{ a.description }}</p>
        </div>
      </li>
      </template>
    </ul>

    <div v-if="toast" class="fixed bottom-[20px] left-1/2 -translate-x-1/2 z-[500] px-[16px] py-[10px] rounded-[12px] bg-[#152242] text-white text-[14px] shadow-lg flex items-center gap-[12px]">
      {{ toast.text }}
      <button v-if="toast.undo" @click="undoRemove" class="text-[#93c5fd] font-semibold">Вернуть</button>
    </div>
  </section>
</template>

<script setup>
useHead({ title: 'Избранное' })

const router = useRouter()
const likes = ref([])
const loading = ref(true)
const error = ref('')
const busy = ref(null)
const toast = ref(null)
let toastTimer = null

const headers = () => ({ Accept: 'application/json', 'Content-Type': 'application/json', Authorization: 'Bearer ' + getToken() })
const photos = (a) => a.images || a.pictures || []

async function load() {
  loading.value = true
  error.value = ''
  try {
    if (!getToken()) throw new Error('auth')
    const res = await fetch(`${apiBase()}/api/favorite`, { headers: headers() })
    if (res.status === 401) throw new Error('auth')
    if (!res.ok) throw new Error()
    likes.value = (await res.json()).map((a) => ({ ...a, photoIndex: 0, warm: false }))
  } catch (e) {
    error.value = e.message === 'auth' ? 'Войдите в аккаунт, чтобы видеть избранное' : 'Не удалось загрузить избранное'
  } finally {
    loading.value = false
  }
}

function shift(a, step) {
  a.warm = true
  const n = photos(a).length
  a.photoIndex = ((a.photoIndex || 0) + step + n) % n
}

function openAnnoun(a) {
  router.push(`/show_announ-${a.id}`)
}

function showToast(t) {
  toast.value = t
  clearTimeout(toastTimer)
  toastTimer = setTimeout(() => { toast.value = null }, 4000)
}

// сервер переключает отметку (есть → убрать, нет → поставить) — одним и тем же запросом и убираем, и возвращаем
const toggleLike = (id) => fetch(`${apiBase()}/api/likes`, { method: 'POST', headers: headers(), body: JSON.stringify({ announ_id: id }) })

async function removeLike(a) {
  busy.value = a.id
  try {
    const res = await toggleLike(a.id)
    if (!res.ok) throw new Error()
    const index = likes.value.indexOf(a)
    likes.value = likes.value.filter((x) => x.id !== a.id)
    showToast({ text: 'Убрано из избранного', undo: { item: a, index } })
  } catch (e) {
    showToast({ text: 'Не получилось, попробуйте ещё раз' })
  } finally {
    busy.value = null
  }
}

async function undoRemove() {
  const { item, index } = toast.value.undo
  toast.value = null
  try {
    const res = await toggleLike(item.id)
    if (!res.ok) throw new Error()
    const list = [...likes.value]
    list.splice(Math.min(index, list.length), 0, item)
    likes.value = list
  } catch (e) {
    showToast({ text: 'Не получилось вернуть, попробуйте ещё раз' })
  }
}

onMounted(load)
onUnmounted(() => clearTimeout(toastTimer))
</script>

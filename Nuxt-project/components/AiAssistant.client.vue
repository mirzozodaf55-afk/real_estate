<template>
  <div v-if="visible">
    <button v-if="!open" @click="open = true" aria-label="Открыть Domly-помощника" data-test="ai-open"
      class="fixed bottom-[20px] right-[20px] z-[250] h-[54px] pl-[16px] pr-[18px] rounded-full bg-gradient-to-r from-[#0468ff] to-[#7c3aed] text-white shadow-[0_10px_30px_rgba(4,104,255,0.35)] flex items-center gap-[8px] font-semibold text-[15px] hover:brightness-110 transition">
      <SiteLogo light :size="30" compact class="sm:hidden" />
      <SiteLogo light :size="30" suffix="помощник" :word-size="18" class="hidden sm:inline-flex" />
    </button>

    <section v-else role="dialog" aria-label="Domly-помощник" data-test="ai-panel"
      class="fixed z-[400] bg-white flex flex-col shadow-[0_20px_60px_rgba(15,23,42,0.25)]
             inset-0 sm:inset-auto sm:bottom-[20px] sm:right-[20px] sm:w-[400px] sm:h-[min(640px,calc(100vh-110px))] sm:rounded-[20px] overflow-hidden">
      <header class="flex items-center gap-[10px] px-[16px] py-[12px] bg-gradient-to-r from-[#0468ff] to-[#7c3aed] text-white">
        <div class="flex-1 min-w-0">
          <SiteLogo light :size="34" suffix="помощник" :word-size="19" />
          <div class="text-[12px] text-white/80 mt-[1px] pl-[43px] truncate">Найду жильё, помогу подать объявление</div>
        </div>
        <button v-if="messages.length" @click="reset" title="Начать заново" class="w-[34px] h-[34px] rounded-full hover:bg-white/15 grid place-items-center">
          <font-awesome-icon :icon="['fas', 'rotate-right']" />
        </button>
        <button @click="open = false" aria-label="Закрыть" class="w-[34px] h-[34px] rounded-full hover:bg-white/15 grid place-items-center">
          <font-awesome-icon :icon="['fas', 'xmark']" class="text-[18px]" />
        </button>
      </header>

      <div ref="scroller" class="flex-1 overflow-y-auto chat-doodles px-[12px] py-[14px] flex flex-col gap-[10px]">
        <div v-if="!messages.length" class="text-center px-[8px] pt-[10px]">
          <div class="text-[15px] font-semibold text-[#152242]">Здравствуйте! Что ищете?</div>
          <p class="text-[13px] text-[#64748b] mt-[4px] mb-[14px]">Опишите своими словами, что ищете, — я найду объявления. Ещё могу помочь подать ваше объявление и ответить на вопросы о сайте.</p>
          <div class="flex flex-col gap-[8px]">
            <button v-for="s of SUGGESTIONS" :key="s" @click="send(s)"
              class="text-left px-[12px] py-[9px] rounded-[12px] bg-white border border-[#e2e8f0] text-[13.5px] text-[#334155] hover:border-[#0468ff] hover:text-[#0468ff] transition-colors">
              {{ s }}
            </button>
          </div>
        </div>

        <template v-for="(m, i) of messages" :key="i">
          <div v-if="m.role === 'user'" class="self-end max-w-[85%] px-[12px] py-[8px] rounded-[16px] rounded-br-[4px] bg-[#0468ff] text-white text-[14px] whitespace-pre-line break-words">
            {{ m.content }}
          </div>
          <div v-else class="self-start max-w-[92%] w-full flex flex-col gap-[8px]">
            <div class="px-[12px] py-[8px] rounded-[16px] rounded-bl-[4px] text-[14px] leading-[1.5] whitespace-pre-line break-words"
              :class="m.error ? 'bg-[#fef2f2] text-[#b91c1c]' : 'bg-white text-[#152242] border border-[#eef2f7]'" data-test="ai-reply">
              {{ m.content }}
            </div>
            <!-- Черновик объявления: ИИ заполнил, человек проверяет, добавляет фото и публикует -->
            <div v-if="m.draft" class="bg-white rounded-[14px] border-2 overflow-hidden" :class="m.published ? 'border-[#bbf7d0]' : 'border-[#c7d2fe]'" data-test="ai-draft">
              <div class="px-[12px] py-[8px] text-[12px] font-bold uppercase tracking-[0.05em]" :class="m.published ? 'bg-[#f0fdf4] text-[#047857]' : 'bg-[#eef2ff] text-[#4338ca]'">
                {{ m.published ? `Отправлено на проверку · №${m.published}` : 'Черновик объявления' }}
              </div>
              <div class="p-[12px]">
                <div class="text-[17px] font-bold text-[#152242]">{{ formatNumber(m.draft.price) }} сомони<span class="text-[12px] font-normal text-[#64748b]">{{ { 'Аренда': ' / мес', 'Посуточно': ' / сутки' }[m.draft.deal] || '' }}</span></div>
                <div class="text-[14px] font-semibold text-[#0468ff] mt-[2px] leading-[1.3]">{{ m.draft.title }}</div>
                <div class="flex flex-wrap gap-[4px] mt-[6px]">
                  <span v-for="f of draftFacts(m.draft)" :key="f" class="px-[7px] py-[1px] rounded-[6px] bg-[#f1f5f9] text-[11.5px] text-[#334155]">{{ f }}</span>
                </div>
                <p class="text-[12.5px] text-[#475569] leading-[1.45] mt-[6px] mb-0 whitespace-pre-line">{{ m.draft.description }}</p>

                <template v-if="!m.published">
                  <div class="mt-[10px] flex flex-wrap gap-[6px]">
                    <div v-for="(p, pi) of photosFor(i)" :key="p.url" class="relative w-[58px] h-[58px] rounded-[8px] overflow-hidden bg-[#e2e8f0]">
                      <img :src="p.url" class="w-full h-full object-cover" alt="" />
                      <button @click="removePhoto(i, pi)" aria-label="Убрать фото" class="absolute top-[2px] right-[2px] w-[18px] h-[18px] rounded-full bg-black/60 text-white text-[10px] grid place-items-center">
                        <font-awesome-icon :icon="['fas', 'xmark']" />
                      </button>
                    </div>
                    <label class="w-[58px] h-[58px] rounded-[8px] border-2 border-dashed border-[#cbd5e1] text-[#64748b] hover:border-[#0468ff] hover:text-[#0468ff] grid place-items-center cursor-pointer" title="Добавить фото">
                      <font-awesome-icon :icon="['fas', 'camera']" />
                      <input type="file" accept="image/*" multiple class="hidden" @change="addPhotos(i, $event)" data-test="ai-draft-photos" />
                    </label>
                  </div>
                  <p v-if="m.publishError" class="text-[12px] text-[#b91c1c] mt-[6px] mb-0">{{ m.publishError }}</p>
                  <button @click="publishDraft(m, i)" :disabled="!photosFor(i).length || publishing === i" data-test="ai-publish"
                    class="mt-[10px] w-full h-[40px] rounded-[10px] text-white text-[14px] font-semibold transition-colors"
                    :class="photosFor(i).length ? 'bg-[#16a34a] hover:bg-[#15803d]' : 'bg-[#cbd5e1]'">
                    <font-awesome-icon v-if="publishing === i" :icon="['fas', 'spinner']" spin-pulse />
                    {{ photosFor(i).length ? 'Опубликовать' : 'Добавьте хотя бы одно фото' }}
                  </button>
                  <p class="text-[11px] text-[#94a3b8] mt-[6px] mb-0 text-center">Что-то не так — напишите в чат, помощник поправит</p>
                </template>
                <button v-else @click="goMyAnnouns" class="mt-[10px] w-full h-[36px] rounded-[10px] bg-[#eef4ff] text-[#0468ff] text-[13.5px] font-semibold hover:bg-[#dbe8ff]">
                  Открыть «Мои объявления»
                </button>
              </div>
            </div>
            <div v-if="m.listings?.length" class="flex gap-[8px] overflow-x-auto pb-[4px] -mr-[12px] pr-[12px]" data-test="ai-listings">
              <button v-for="a of m.listings" :key="a.id" @click="openAnnoun(a)"
                class="shrink-0 w-[170px] text-left bg-white rounded-[12px] border border-[#e8edf3] overflow-hidden hover:shadow-[0_6px_18px_rgba(15,23,42,0.1)] transition-shadow">
                <div class="h-[100px] bg-[#e2e8f0]">
                  <img v-if="a.pictures?.[0]" :src="`${apiBase()}/api/image/small/${a.pictures[0].pictures}`" class="w-full h-full object-cover" alt="" loading="lazy" />
                </div>
                <div class="p-[8px]">
                  <div class="text-[14px] font-bold text-[#152242]">{{ formatNumber(a.price) }} с.<span class="text-[11.5px] font-normal text-[#64748b]">{{ priceSuffix(a) }}</span></div>
                  <div class="text-[12.5px] text-[#334155] line-clamp-2 leading-[1.3] mt-[2px]">{{ a.title || a.type_object }}</div>
                  <div class="text-[11.5px] text-[#94a3b8] mt-[2px] truncate">{{ [a.quantity_room && `${a.quantity_room}-комн.`, (a.total_area || a.house_area) && `${a.total_area || a.house_area} м²`].filter(Boolean).join(' · ') }}</div>
                </div>
              </button>
            </div>
          </div>
        </template>

        <div v-if="loading" class="self-start px-[14px] py-[10px] rounded-[16px] rounded-bl-[4px] bg-white border border-[#eef2f7] flex gap-[4px]" aria-label="Помощник печатает">
          <span v-for="n in 3" :key="n" class="w-[7px] h-[7px] rounded-full bg-[#94a3b8] animate-bounce" :style="`animation-delay:${n * 120}ms`"></span>
        </div>
      </div>

      <form @submit.prevent="send()" class="flex items-end gap-[8px] p-[10px] border-t border-[#eef2f7] bg-white">
        <textarea ref="input" v-model="draft" @keydown.enter.exact.prevent="send()" rows="1" maxlength="1000" data-test="ai-input"
          placeholder="Например: двушка до 4000 сомони, можно с детьми"
          class="flex-1 resize-none max-h-[110px] min-h-[42px] px-[14px] py-[10px] rounded-[14px] bg-[#f1f5f9] outline-none text-[14px] text-[#152242] leading-[1.4]"></textarea>
        <button type="submit" :disabled="!draft.trim() || loading" aria-label="Отправить" data-test="ai-send"
          class="w-[42px] h-[42px] shrink-0 rounded-full grid place-items-center text-white transition-colors"
          :class="draft.trim() && !loading ? 'bg-[#0468ff] hover:bg-[#0353cc]' : 'bg-[#cbd5e1]'">
          <font-awesome-icon :icon="['fas', 'paper-plane']" class="text-[14px]" />
        </button>
      </form>
      <p class="text-[11px] text-[#94a3b8] text-center pb-[8px] m-0 bg-white">ИИ может ошибаться — проверяйте детали в объявлении</p>
    </section>
  </div>
</template>

<script setup>
const SUGGESTIONS = [
  'Снять 2-комнатную квартиру до 4000 сомони в месяц',
  'Хочу подать объявление',
  'Как узнавать о новых объявлениях?',
]
const STORAGE_KEY = 'aiAssistantChat'

const route = useRoute()
const router = useRouter()
const enabled = useAiStatus()

// В админке и в чате своя вёрстка на весь экран — там кнопка мешала бы;
// на карте она закрывала бы логотип Яндекса в углу, а его по правилам Яндекса перекрывать нельзя
// на шагах подачи объявления — кнопка перекрывала бы «Далее» (на телефоне панель кнопок прилипает к низу)
const WIZARD = ['/announ', '/map', '/pictures', '/feature', '/technicsandfurniture', '/description', '/price', '/office']
const visible = computed(() => enabled.value && !route.path.startsWith('/admin') && !route.path.startsWith('/chat-')
  && route.path !== '/yandex-map' && !WIZARD.includes(route.path))

const open = ref(false)
const draft = ref('')
const loading = ref(false)
const messages = ref(restore())
const scroller = ref(null)
const input = ref(null)

// переписка живёт до закрытия вкладки — переходы по объявлениям её не сбрасывают
function restore() {
  try {
    return JSON.parse(sessionStorage.getItem(STORAGE_KEY)) || []
  } catch (e) {
    return []
  }
}
watch(messages, (v) => {
  try { sessionStorage.setItem(STORAGE_KEY, JSON.stringify(v.slice(-20))) } catch (e) { /* приватный режим */ }
}, { deep: true })

async function scrollDown() {
  await nextTick()
  if (scroller.value) scroller.value.scrollTop = scroller.value.scrollHeight
}
watch(open, (v) => { if (v) { scrollDown(); nextTick(() => input.value?.focus()) } })

async function send(text) {
  const content = (text ?? draft.value).trim()
  if (!content || loading.value) return
  draft.value = ''
  messages.value.push({ role: 'user', content })
  loading.value = true
  scrollDown()
  try {
    // серверу — только текст последних реплик, без карточек и ошибок
    const history = messages.value.filter((m) => !m.error).slice(-10).map(({ role, content }) => ({ role, content }))
    const res = await aiChat(history)
    messages.value.push({ role: 'assistant', content: res.reply, listings: res.listings, draft: res.draft || null })
  } catch (e) {
    messages.value.push({ role: 'assistant', content: e.message, error: true })
  } finally {
    loading.value = false
    scrollDown()
  }
}

function reset() {
  messages.value = []
  Object.keys(photos).forEach((k) => removeAllPhotos(k))
}

// --- Черновик объявления: фото держим в памяти (File нельзя сохранить в sessionStorage)
const photos = reactive({}) // { [индекс сообщения]: [{ file, url }] }
const publishing = ref(null)
const photosFor = (i) => photos[i] || []

function addPhotos(i, event) {
  const files = [...event.target.files].filter((f) => f.type.startsWith('image/'))
  photos[i] = [...photosFor(i), ...files.map((file) => ({ file, url: URL.createObjectURL(file) }))].slice(0, 20)
  event.target.value = ''
}
function removePhoto(i, pi) {
  URL.revokeObjectURL(photos[i][pi].url)
  photos[i].splice(pi, 1)
}
function removeAllPhotos(i) {
  photosFor(i).forEach((p) => URL.revokeObjectURL(p.url))
  delete photos[i]
}

async function publishDraft(m, i) {
  publishing.value = i
  m.publishError = ''
  try {
    const form = new FormData()
    form.append('draft', JSON.stringify(m.draft))
    photosFor(i).forEach((p) => form.append('photos[]', p.file))
    const res = await fetch(`${apiBase()}/api/ai/publish`, {
      method: 'POST',
      headers: { Accept: 'application/json', Authorization: 'Bearer ' + getToken() },
      body: form,
    })
    const json = await res.json().catch(() => ({}))
    if (!res.ok) throw new Error(json.message || 'Не удалось опубликовать, попробуйте ещё раз')
    m.published = json.id
    removeAllPhotos(i)
    messages.value.push({ role: 'assistant', content: `Готово! Объявление №${json.id} отправлено на проверку модератору. Когда его одобрят, придёт уведомление и письмо, а объявление появится в поиске.` })
    scrollDown()
  } catch (e) {
    m.publishError = e.message
  } finally {
    publishing.value = null
  }
}

function goMyAnnouns() {
  if (window.innerWidth < 640) open.value = false
  router.push('/personal_area/my_announ')
}

function draftFacts(d) {
  return [
    d.deal === 'Продажа' ? 'Продажа' : d.deal === 'Посуточно' ? 'Посуточно' : 'Аренда',
    d.type_object,
    d.rooms != null && `${d.rooms}-комн.`,
    d.total_area && `${d.total_area} м²`,
    d.land_area && `${d.land_area} сот.`,
    d.floor && (d.floors_in_house ? `${d.floor}/${d.floors_in_house} эт.` : `${d.floor} эт.`),
    d.repair,
  ].filter(Boolean)
}

function openAnnoun(a) {
  // на телефоне панель на весь экран — закрываем, чтобы было видно объявление
  if (window.innerWidth < 640) open.value = false
  router.push(`/show_announ-${a.id}`)
}
</script>

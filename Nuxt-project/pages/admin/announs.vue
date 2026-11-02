<template>
  <AdminShell>
    <div class="flex flex-wrap items-end justify-between gap-[12px] mb-[14px]">
      <div>
        <h1 class="text-[24px] font-bold text-[#152242] m-0">Модерация объявлений</h1>
        <p class="text-[14px] text-[#64748b] m-0 mt-[2px]">{{ STATUSES.find((s) => s.value === status).hint }}</p>
      </div>
      <form @submit.prevent="search" class="relative w-full sm:w-[300px]">
        <font-awesome-icon :icon="['fas', 'magnifying-glass']" class="absolute left-[12px] top-1/2 -translate-y-1/2 text-[#94a3b8] text-[13px]" />
        <input v-model="qInput" type="search" placeholder="№, текст, почта или телефон владельца" data-test="admin-search"
          class="w-full h-[40px] pl-[34px] pr-[12px] rounded-[10px] border border-[#e2e8f0] bg-white outline-none focus:border-[#0468ff] text-[14px]" />
      </form>
    </div>

    <div class="inline-flex bg-[#e9eef5] rounded-[10px] p-[3px] mb-[14px]">
      <button v-for="s of STATUSES" :key="s.value" @click="setStatus(s.value)" :data-test="`tab-${s.value}`"
        class="px-[14px] h-[34px] rounded-[8px] text-[14px] font-semibold transition-colors"
        :class="status === s.value ? 'bg-white text-[#0468ff] shadow-sm' : 'text-[#475569] hover:text-[#152242]'">
        {{ s.label }}
      </button>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-[380px_1fr] gap-[16px] items-start">
      <!-- Список -->
      <div class="bg-white rounded-[16px] border border-[#e8edf3] overflow-hidden" :class="{ 'hidden xl:block': selectedId }">
        <div v-if="listLoading" class="p-[12px] flex flex-col gap-[8px]">
          <div v-for="n in 4" :key="n" class="h-[72px] rounded-[12px] bg-[#f1f5f9] animate-pulse"></div>
        </div>
        <div v-else-if="listError" class="p-[24px] text-center text-[14px] text-[#b91c1c]">{{ listError }}</div>
        <div v-else-if="!items.length" class="p-[32px] text-center" data-test="queue-empty">
          <div class="w-[56px] h-[56px] mx-auto rounded-full bg-[#ecfdf5] grid place-items-center text-[#047857] text-[22px]">
            <font-awesome-icon :icon="['fas', status === 'pending' ? 'mug-hot' : 'inbox']" />
          </div>
          <p class="text-[16px] font-bold text-[#152242] mt-[12px] mb-[2px]">{{ status === 'pending' && !q ? 'Очередь пуста' : 'Ничего не найдено' }}</p>
          <p class="text-[13px] text-[#64748b] m-0">{{ status === 'pending' && !q ? 'Все объявления проверены.' : 'Попробуйте изменить запрос.' }}</p>
        </div>
        <ul v-else class="list-none m-0 p-[6px] max-h-[calc(100vh-260px)] overflow-y-auto">
          <li v-for="a of items" :key="a.id">
            <button @click="select(a.id)" :data-test="`item-${a.id}`"
              class="w-full text-left flex gap-[10px] p-[8px] rounded-[12px] transition-colors"
              :class="selectedId === a.id ? 'bg-[#eef4ff]' : 'hover:bg-[#f8fafc]'">
              <div class="w-[76px] h-[58px] rounded-[8px] overflow-hidden bg-[#e2e8f0] shrink-0">
                <img v-if="a.pictures?.[0]" :src="`${apiBase()}/api/image/xsmall/${a.pictures[0].pictures}`" class="w-full h-full object-cover" alt="" loading="lazy" />
              </div>
              <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between gap-[6px]">
                  <span class="text-[14.5px] font-bold text-[#152242] truncate">{{ formatNumber(a.price) }} с.</span>
                  <span class="text-[11.5px] text-[#94a3b8] whitespace-nowrap">№{{ a.id }}</span>
                </div>
                <div class="text-[13px] text-[#334155] truncate">
                  <span v-if="photoVerdict(a) !== 'ok' && photoVerdict(a)" data-test="photo-flag"
                    class="mr-[4px] px-[5px] rounded-[5px] text-[11px] font-bold" :class="PHOTO[photoVerdict(a)].cls">⚠ Фото</span>{{ a.title || a.type_object }}
                </div>
                <div class="text-[12px] text-[#94a3b8] truncate">
                  {{ status === 'pending' ? `ждёт ${timeAgo(a.updated_at).replace(' назад', '')}`
                    : a.moderated_at ? `решение ${timeAgo(a.moderated_at)}` : 'опубликовано до модерации' }}
                  · {{ ownerName(a.owner) }}
                </div>
              </div>
            </button>
          </li>
        </ul>
        <div v-if="lastPage > 1" class="flex items-center justify-between px-[12px] py-[8px] border-t border-[#eef2f7] text-[13px]">
          <button :disabled="page <= 1" @click="loadList(page - 1)" class="px-[10px] h-[30px] rounded-[8px] disabled:opacity-40 hover:bg-[#f1f5f9]">← Назад</button>
          <span class="text-[#64748b]">{{ page }} из {{ lastPage }} · всего {{ total }}</span>
          <button :disabled="page >= lastPage" @click="loadList(page + 1)" class="px-[10px] h-[30px] rounded-[8px] disabled:opacity-40 hover:bg-[#f1f5f9]">Далее →</button>
        </div>
      </div>

      <!-- Карточка объявления -->
      <div v-if="!selectedId" class="hidden xl:grid bg-white rounded-[16px] border border-dashed border-[#cbd5e1] min-h-[320px] place-items-center text-[14px] text-[#94a3b8]">
        Выберите объявление слева
      </div>
      <div v-else class="bg-white rounded-[16px] border border-[#e8edf3] overflow-hidden" data-test="detail">
        <div v-if="detailLoading" class="p-[16px]">
          <div class="h-[300px] rounded-[12px] bg-[#f1f5f9] animate-pulse"></div>
        </div>
        <div v-else-if="detailError" class="p-[24px] text-center text-[14px] text-[#b91c1c]">{{ detailError }}</div>
        <template v-else-if="detail">
          <div class="flex items-center gap-[8px] px-[16px] pt-[14px]">
            <button @click="select(null)" class="xl:hidden h-[32px] px-[10px] whitespace-nowrap rounded-[8px] text-[13px] font-semibold text-[#475569] hover:bg-[#f1f5f9]">← К списку</button>
            <span class="px-[8px] py-[2px] rounded-[8px] text-[12px] font-semibold" :class="statusBadge(detail).cls">{{ statusBadge(detail).label }}</span>
            <span v-if="detail.deleted_at" class="px-[8px] py-[2px] rounded-[8px] text-[12px] font-semibold bg-[#f1f5f9] text-[#475569]">Снято владельцем</span>
            <span v-if="detail.sold_at" class="px-[8px] py-[2px] rounded-[8px] text-[12px] font-semibold bg-[#eef2ff] text-[#4338ca]">{{ detail.deal_type === 'Аренда' ? 'Сдано' : 'Продано' }} {{ timeAgo(detail.sold_at) }}</span>
            <span class="ml-auto text-[12.5px] text-[#94a3b8]">№{{ detail.id }} · изменено {{ timeAgo(detail.updated_at) }}</span>
          </div>

          <!-- Фото -->
          <div class="p-[16px] pb-0">
            <div class="relative rounded-[12px] overflow-hidden bg-[#0f172a] aspect-[16/9]">
              <img v-if="photo" :src="`${apiBase()}/api/image/medium/${photo.pictures}`" :style="`transform: rotate(${photo.rotation || 0}deg)`"
                class="w-full h-full object-contain" alt="" />
              <div v-else class="w-full h-full grid place-items-center text-[#64748b]">Нет фото</div>
              <template v-if="detail.pictures.length > 1">
                <button @click="photoIndex = (photoIndex - 1 + detail.pictures.length) % detail.pictures.length" aria-label="Предыдущее фото"
                  class="absolute left-[10px] top-1/2 -translate-y-1/2 w-[36px] h-[36px] rounded-full bg-white/85 hover:bg-white grid place-items-center">
                  <font-awesome-icon :icon="['fas', 'chevron-left']" />
                </button>
                <button @click="photoIndex = (photoIndex + 1) % detail.pictures.length" aria-label="Следующее фото"
                  class="absolute right-[10px] top-1/2 -translate-y-1/2 w-[36px] h-[36px] rounded-full bg-white/85 hover:bg-white grid place-items-center">
                  <font-awesome-icon :icon="['fas', 'chevron-right']" />
                </button>
                <span class="absolute bottom-[10px] right-[10px] px-[8px] py-[2px] rounded-[8px] bg-black/55 text-white text-[12px]">{{ photoIndex + 1 }} / {{ detail.pictures.length }}</span>
              </template>
            </div>
            <div v-if="detail.pictures.length > 1" class="flex gap-[6px] mt-[8px] overflow-x-auto pb-[2px]">
              <button v-for="(p, i) of detail.pictures" :key="p.id" @click="photoIndex = i"
                class="relative w-[64px] h-[46px] rounded-[6px] overflow-hidden shrink-0 border-2"
                :class="photoNote(p)?.ok === false ? 'border-[#dc2626]' : i === photoIndex ? 'border-[#0468ff]' : 'border-transparent opacity-70'">
                <img :src="`${apiBase()}/api/image/xsmall/${p.pictures}`" class="w-full h-full object-cover" alt="" />
                <span v-if="photoNote(p)?.ok === false" class="absolute inset-x-0 bottom-0 bg-[#dc2626] text-white text-[9px] font-bold leading-[13px]">не то</span>
              </button>
            </div>

            <!-- Проверка фото ИИ: сравнивает снимки с данными объявления -->
            <div v-if="aiEnabled" class="mt-[10px] rounded-[12px] border p-[10px]" :class="detail.photo_check ? PHOTO[detail.photo_check.verdict]?.box : 'border-[#e8edf3]'" data-test="photo-check">
              <div class="flex flex-wrap items-center gap-[8px]">
                <span class="text-[12px] font-bold uppercase tracking-[0.05em] text-[#7c3aed]"><font-awesome-icon :icon="['fas', 'wand-magic-sparkles']" /> Фото глазами ИИ</span>
                <span v-if="detail.photo_check" class="px-[8px] py-[1px] rounded-full text-[11.5px] font-bold" :class="PHOTO[detail.photo_check.verdict]?.cls">{{ PHOTO[detail.photo_check.verdict]?.label }}</span>
                <button @click="runPhotoCheck" :disabled="photoChecking" data-test="photo-recheck"
                  class="ml-auto h-[30px] px-[10px] rounded-[8px] border border-[#ddd6fe] bg-white text-[#6d28d9] hover:bg-[#f5f3ff] text-[12.5px] font-semibold disabled:opacity-60">
                  <font-awesome-icon v-if="photoChecking" :icon="['fas', 'spinner']" spin-pulse />
                  {{ detail.photo_check ? 'Перепроверить' : 'Проверить фото' }}
                </button>
              </div>
              <p v-if="photoCheckError" class="text-[12.5px] text-[#b91c1c] mt-[6px] mb-0">{{ photoCheckError }}</p>
              <template v-if="detail.photo_check">
                <p class="text-[13px] text-[#334155] mt-[6px] mb-0">{{ detail.photo_check.summary }}</p>
                <ul class="list-none m-0 mt-[4px] p-0 text-[12.5px] leading-[1.5]">
                  <li v-for="(p, i) of detail.photo_check.photos" :key="i" :class="p.ok ? 'text-[#475569]' : 'text-[#b91c1c] font-semibold'">
                    {{ p.ok ? '✓' : '✗' }} Фото {{ i + 1 }}: {{ p.what }}
                  </li>
                </ul>
                <div class="text-[11px] text-[#94a3b8] mt-[4px]">
                  Проверено {{ timeAgo(detail.photo_check_at) }}<span v-if="detail.pictures.length > detail.photo_check.checked_photos"> · смотрели первые {{ detail.photo_check.checked_photos }} фото</span>
                </div>
              </template>
              <p v-else-if="!photoChecking" class="text-[12.5px] text-[#94a3b8] mt-[4px] mb-0">Ещё не проверялись — новые объявления ИИ проверяет сам через несколько секунд после подачи.</p>
            </div>
          </div>

          <div class="p-[16px] grid grid-cols-1 lg:grid-cols-[1fr_260px] gap-[16px]">
            <div class="min-w-0">
              <div class="text-[24px] font-bold text-[#152242]">{{ formatNumber(detail.price) }} сомони<span class="text-[14px] font-normal text-[#64748b]">{{ priceSuffix(detail) }}</span></div>
              <div class="text-[17px] font-semibold text-[#0468ff] mt-[2px]">{{ detail.title || 'Без заголовка' }}</div>
              <div class="flex flex-wrap gap-[6px] mt-[10px]">
                <span v-for="f of facts" :key="f" class="px-[9px] py-[3px] rounded-[8px] bg-[#f1f5f9] text-[13px] text-[#334155]">{{ f }}</span>
              </div>
              <p class="text-[14.5px] text-[#334155] leading-[1.6] mt-[12px] mb-0 whitespace-pre-line break-words">{{ detail.description || 'Описание не заполнено' }}</p>
              <div v-if="detail.conditions?.length || detail.infrastructure?.length" class="mt-[12px] text-[13px] text-[#64748b]">
                <div v-if="detail.conditions?.length"><b class="text-[#334155]">Удобства:</b> {{ detail.conditions.join(', ') }}</div>
                <div v-if="detail.infrastructure?.length" class="mt-[4px]"><b class="text-[#334155]">Рядом:</b> {{ detail.infrastructure.join(', ') }}</div>
              </div>
              <a v-if="detail.link_video" :href="detail.link_video" target="_blank" rel="noopener noreferrer" class="inline-block mt-[10px] text-[14px] text-[#0468ff]">Видео по ссылке ↗</a>
            </div>

            <div class="flex flex-col gap-[12px]">
              <div v-if="aiEnabled" class="rounded-[12px] border border-[#e9d5ff] bg-gradient-to-b from-[#faf5ff] to-white p-[12px]" data-test="ai-review">
                <div class="flex items-center justify-between gap-[8px]">
                  <div class="text-[12px] font-bold uppercase tracking-[0.06em] text-[#7c3aed]">
                    <font-awesome-icon :icon="['fas', 'wand-magic-sparkles']" /> Проверка ИИ
                  </div>
                  <span v-if="aiReview" class="px-[8px] py-[1px] rounded-full text-[11.5px] font-bold" :class="RISK[aiReview.risk]?.cls">{{ RISK[aiReview.risk]?.label }}</span>
                </div>
                <button v-if="!aiReview" @click="runAiReview" :disabled="aiLoading" data-test="ai-review-run"
                  class="mt-[8px] w-full h-[36px] rounded-[10px] bg-[#7c3aed] hover:bg-[#6d28d9] disabled:opacity-60 text-white text-[13.5px] font-semibold">
                  <font-awesome-icon v-if="aiLoading" :icon="['fas', 'spinner']" spin-pulse /> {{ aiLoading ? 'Проверяю…' : 'Проверить объявление' }}
                </button>
                <p v-if="aiErr" class="text-[12.5px] text-[#b91c1c] mt-[6px] mb-0">{{ aiErr }}</p>
                <template v-if="aiReview">
                  <p class="text-[13px] text-[#334155] leading-[1.45] mt-[8px] mb-0">{{ aiReview.summary }}</p>
                  <ul v-if="aiReview.issues.length" class="text-[12.5px] text-[#475569] leading-[1.45] mt-[6px] mb-0 pl-[16px]">
                    <li v-for="(issue, i) of aiReview.issues" :key="i">{{ issue }}</li>
                  </ul>
                  <div v-if="aiReview.market?.['медиана']" class="text-[11.5px] text-[#94a3b8] mt-[6px]">
                    Похожих: {{ aiReview.market['похожих_объявлений'] }}, медиана {{ formatNumber(aiReview.market['медиана']) }} с.
                  </div>
                  <button v-if="aiReview.suggested_reason && detail.moderation_status !== 'rejected'" @click="useAiReason" data-test="ai-use-reason"
                    class="mt-[8px] w-full h-[32px] rounded-[9px] border border-[#fecaca] bg-white text-[#dc2626] hover:bg-[#fef2f2] text-[12.5px] font-semibold">
                    Использовать как причину отказа
                  </button>
                  <p class="text-[11px] text-[#94a3b8] mt-[6px] mb-0">Подсказка, а не решение: фото ИИ не видит.</p>
                </template>
              </div>

              <div class="rounded-[12px] border border-[#e8edf3] p-[12px]">
                <div class="text-[12px] font-bold uppercase tracking-[0.06em] text-[#94a3b8] mb-[8px]">Владелец</div>
                <div class="flex items-center gap-[10px]">
                  <div class="w-[40px] h-[40px] rounded-full bg-[#e2e8f0] bg-cover bg-center shrink-0" :style="`background-image: url('${apiBase()}/api/avatar/${detail.owner?.avatar}')`"></div>
                  <div class="min-w-0">
                    <div class="text-[14px] font-semibold text-[#152242] truncate">{{ ownerName(detail.owner) }}</div>
                    <div class="text-[12.5px] text-[#64748b] truncate">{{ detail.owner?.email || detail.owner?.phone_number }}</div>
                  </div>
                </div>
                <div class="text-[12.5px] text-[#64748b] mt-[8px] leading-[1.6]">
                  На сайте {{ timeAgo(detail.owner?.created_at).replace(' назад', '') }}<br />
                  Объявлений: {{ detail.owner_stats.total }}
                  <span v-if="detail.owner_stats.rejected" class="text-[#dc2626]">· отклонено {{ detail.owner_stats.rejected }}</span>
                </div>
              </div>

              <div v-if="detail.moderation_reason" class="rounded-[12px] bg-[#fef2f2] p-[12px] text-[13px] text-[#991b1b]">
                <b>Причина отказа:</b> {{ detail.moderation_reason }}
              </div>

              <div v-if="detail.history?.length" class="rounded-[12px] border border-[#e8edf3] p-[12px]">
                <div class="text-[12px] font-bold uppercase tracking-[0.06em] text-[#94a3b8] mb-[6px]">История</div>
                <ul class="list-none m-0 p-0 flex flex-col gap-[6px]">
                  <li v-for="h of detail.history" :key="h.id" class="text-[12.5px] text-[#475569] leading-[1.4]">
                    <span :class="h.action === 'approve' ? 'text-[#047857]' : 'text-[#dc2626]'" class="font-semibold">{{ h.action === 'approve' ? 'Одобрено' : 'Отклонено' }}</span>
                    · {{ ownerName(h.moderator) }} · {{ timeAgo(h.created_at) }}
                    <div v-if="h.reason" class="text-[#94a3b8]">«{{ h.reason }}»</div>
                  </li>
                </ul>
              </div>
            </div>
          </div>

          <!-- Решение -->
          <div class="border-t border-[#eef2f7] bg-[#fafbfd] p-[16px]">
            <div v-if="rejecting">
              <label class="text-[14px] font-semibold text-[#152242]" for="reject-reason">Причина отказа — её увидит владелец</label>
              <div class="flex flex-wrap gap-[6px] mt-[8px]">
                <button v-for="r of REASONS" :key="r" type="button" @click="reason = r"
                  class="px-[10px] h-[30px] rounded-full border text-[12.5px] transition-colors"
                  :class="reason === r ? 'border-[#dc2626] bg-[#fef2f2] text-[#b91c1c]' : 'border-[#e2e8f0] bg-white text-[#475569] hover:border-[#94a3b8]'">{{ r }}</button>
              </div>
              <textarea id="reject-reason" v-model="reason" rows="3" maxlength="500" data-test="reason"
                class="w-full mt-[8px] p-[10px] rounded-[10px] border border-[#e2e8f0] bg-white outline-none focus:border-[#dc2626] text-[14px] resize-y"
                placeholder="Например: на фото другой объект, уточните адрес и этаж"></textarea>
              <div class="flex flex-wrap items-center gap-[8px] mt-[8px]">
                <button @click="decide('reject')" :disabled="busy || reason.trim().length < 5" data-test="confirm-reject"
                  class="h-[40px] px-[18px] rounded-[10px] bg-[#dc2626] hover:bg-[#b91c1c] disabled:opacity-50 text-white text-[14px] font-semibold">
                  <font-awesome-icon v-if="busy" :icon="['fas', 'spinner']" spin-pulse /> Отклонить
                </button>
                <button @click="rejecting = false" class="h-[40px] px-[14px] rounded-[10px] text-[14px] font-semibold text-[#475569] hover:bg-[#f1f5f9]">Отмена</button>
                <span v-if="reason && reason.trim().length < 5" class="text-[12.5px] text-[#94a3b8]">минимум 5 символов</span>
              </div>
            </div>
            <div v-else class="flex flex-wrap items-center gap-[8px]">
              <button v-if="detail.moderation_status !== 'approved'" @click="decide('approve')" :disabled="busy" data-test="approve"
                class="h-[42px] px-[20px] rounded-[10px] bg-[#16a34a] hover:bg-[#15803d] disabled:opacity-50 text-white text-[14.5px] font-semibold">
                <font-awesome-icon :icon="['fas', busy ? 'spinner' : 'check']" :spin-pulse="busy" class="mr-[6px]" /> Одобрить
              </button>
              <button v-if="detail.moderation_status !== 'rejected'" @click="startReject" :disabled="busy" data-test="reject"
                class="h-[42px] px-[18px] rounded-[10px] border border-[#fecaca] bg-white text-[#dc2626] hover:bg-[#fef2f2] text-[14.5px] font-semibold">
                <font-awesome-icon :icon="['fas', 'xmark']" class="mr-[6px]" /> {{ detail.moderation_status === 'approved' ? 'Снять с публикации' : 'Отклонить' }}
              </button>
              <span class="text-[12.5px] text-[#94a3b8] ml-auto">Владелец получит уведомление и письмо</span>
            </div>
          </div>
        </template>
      </div>
    </div>

    <!-- в углу и без pointer-events: не должен перекрывать кнопки решения под собой -->
    <div v-if="toast" class="fixed top-[84px] right-[20px] z-[500] max-w-[calc(100vw-40px)] px-[16px] py-[10px] rounded-[12px] bg-[#152242] text-white text-[14px] shadow-lg pointer-events-none" role="status">
      {{ toast }}
    </div>
  </AdminShell>
</template>

<script setup>
useHead({ title: 'Модерация объявлений' })

const STATUSES = [
  { value: 'pending', label: 'На проверке', hint: 'Скрыты из поиска, пока вы их не проверите. Сначала — давно ждущие.' },
  { value: 'approved', label: 'Одобренные', hint: 'Видны в поиске. Можно снять с публикации, если нашлась проблема.' },
  { value: 'rejected', label: 'Отклонённые', hint: 'Ждут исправления владельцем — после правки вернутся в очередь.' },
]
const REASONS = [
  'Фото не соответствуют объекту',
  'Подозрительно низкая цена',
  'Мало информации в описании',
  'Контакты или реклама в тексте',
  'Дубликат объявления',
]

const route = useRoute()
const router = useRouter()
const pending = usePendingCount()

// состояние в URL: ссылку на конкретное объявление можно переслать коллеге
const status = computed(() => (STATUSES.some((s) => s.value === route.query.status) ? route.query.status : 'pending'))
const q = computed(() => route.query.q || '')
const selectedId = computed(() => (route.query.id ? Number(route.query.id) : null))
const qInput = ref(q.value)

const items = ref([])
const page = ref(1)
const lastPage = ref(1)
const total = ref(0)
const listLoading = ref(true)
const listError = ref('')

const detail = ref(null)
const detailLoading = ref(false)
const detailError = ref('')
const photoIndex = ref(0)
const photo = computed(() => detail.value?.pictures?.[photoIndex.value])

const rejecting = ref(false)
const reason = ref('')
const busy = ref(false)
const toast = ref('')
let toastTimer = null

function setQuery(patch) {
  const next = { ...route.query, ...patch }
  Object.keys(next).forEach((k) => (next[k] === null || next[k] === '' || next[k] === undefined) && delete next[k])
  router.replace({ query: next })
}
const setStatus = (value) => setQuery({ status: value === 'pending' ? null : value, id: null })
const search = () => setQuery({ q: qInput.value.trim(), id: null })
const select = (id) => setQuery({ id })

async function loadList(p = 1) {
  listLoading.value = true
  listError.value = ''
  try {
    const params = new URLSearchParams({ status: status.value, page: p })
    if (q.value) params.set('q', q.value)
    const res = await adminApi(`/announs?${params}`)
    items.value = res.data
    page.value = res.current_page
    lastPage.value = res.last_page
    total.value = res.total
    if (status.value === 'pending' && !q.value) pending.value = res.total
  } catch (e) {
    listError.value = 'Не удалось загрузить список: ' + e.message
  } finally {
    listLoading.value = false
  }
}

// --- Подсказка ИИ (по кнопке, а не автоматически: каждый запрос к OpenAI стоит денег)
const aiEnabled = useAiStatus()
const aiReview = ref(null)
const aiLoading = ref(false)
const aiErr = ref('')
const RISK = {
  low: { label: 'Низкий риск', cls: 'bg-[#ecfdf5] text-[#047857]' },
  medium: { label: 'Есть вопросы', cls: 'bg-[#fffbeb] text-[#b45309]' },
  high: { label: 'Высокий риск', cls: 'bg-[#fef2f2] text-[#dc2626]' },
}

// --- Проверка фото ИИ (результат приходит вместе с объявлением — announs.photo_check)
const PHOTO = {
  ok: { label: 'Фото в порядке', cls: 'bg-[#ecfdf5] text-[#047857]', box: 'border-[#bbf7d0] bg-[#f0fdf4]' },
  suspicious: { label: 'Сомнительные фото', cls: 'bg-[#fffbeb] text-[#b45309]', box: 'border-[#fde68a] bg-[#fffbeb]' },
  mismatch: { label: 'Фото не соответствуют', cls: 'bg-[#fef2f2] text-[#dc2626]', box: 'border-[#fecaca] bg-[#fef2f2]' },
}
const photoVerdict = (a) => a.photo_check?.verdict || null
const photoNote = (p) => detail.value?.photo_check?.photos?.find((x) => x.picture === p.pictures)
const photoChecking = ref(false)
const photoCheckError = ref('')

async function runPhotoCheck() {
  photoChecking.value = true
  photoCheckError.value = ''
  const id = detail.value.id
  try {
    const res = await adminApi(`/announs/${id}/photo-check`, { method: 'POST' })
    if (detail.value?.id === id) {
      detail.value.photo_check = res.photo_check
      detail.value.photo_check_at = new Date().toISOString()
      const item = items.value.find((x) => x.id === id)
      if (item) item.photo_check = res.photo_check
    }
  } catch (e) {
    photoCheckError.value = e.message
  } finally {
    photoChecking.value = false
  }
}

async function runAiReview() {
  aiLoading.value = true
  aiErr.value = ''
  const id = detail.value.id
  try {
    const res = await adminApi(`/announs/${id}/ai-review`, { method: 'POST' })
    if (detail.value?.id === id) aiReview.value = res // модератор мог уже перейти к другому объявлению
  } catch (e) {
    aiErr.value = e.message
  } finally {
    aiLoading.value = false
  }
}

function useAiReason() {
  startReject()
  reason.value = aiReview.value.suggested_reason
}

async function loadDetail(id) {
  rejecting.value = false
  reason.value = ''
  photoIndex.value = 0
  aiReview.value = null
  aiErr.value = ''
  photoCheckError.value = ''
  if (!id) {
    detail.value = null
    return
  }
  detailLoading.value = true
  detailError.value = ''
  try {
    detail.value = (await adminApi(`/announs/${id}`)).data
  } catch (e) {
    detail.value = null
    detailError.value = e.status === 404 ? 'Объявление не найдено — возможно, владелец его удалил' : 'Не удалось загрузить: ' + e.message
  } finally {
    detailLoading.value = false
  }
}

function startReject() {
  rejecting.value = true
  nextTick(() => document.getElementById('reject-reason')?.focus())
}

async function decide(action) {
  busy.value = true
  try {
    const res = await adminApi(`/announs/${detail.value.id}/${action}`, {
      method: 'POST',
      body: action === 'reject' ? { reason: reason.value.trim() } : undefined,
    })
    showToast(res.message)
    // в очереди сразу открываем следующее — модератору не нужно возвращаться к списку
    const nextId = status.value === 'pending' ? res.next_id : detail.value.id
    await loadList(page.value)
    if (nextId === selectedId.value) await loadDetail(nextId)
    else select(nextId)
  } catch (e) {
    showToast(e.message)
  } finally {
    busy.value = false
  }
}

function showToast(text) {
  toast.value = text
  clearTimeout(toastTimer)
  toastTimer = setTimeout(() => { toast.value = '' }, 3000)
}

const statusBadge = (a) => ({
  pending: { label: 'На проверке', cls: 'bg-[#fffbeb] text-[#b45309]' },
  approved: { label: 'Одобрено', cls: 'bg-[#ecfdf5] text-[#047857]' },
  rejected: { label: 'Отклонено', cls: 'bg-[#fef2f2] text-[#dc2626]' },
}[a.moderation_status] || { label: a.moderation_status, cls: 'bg-[#f1f5f9]' })

const ownerName = (o) => (o ? [o.name, o.last_name].filter(Boolean).join(' ') || o.email || o.phone_number || `#${o.id}` : '—')

const facts = computed(() => {
  const a = detail.value
  if (!a) return []
  const deal = a.deal_type === 'Аренда' ? (a.rental_type === 'Посуточно' ? 'Посуточно' : 'Аренда') : a.deal_type
  return [
    deal, a.type_object,
    a.quantity_room && `${a.quantity_room}-комн.`,
    (a.total_area || a.house_area || a.building_area) && `${a.total_area || a.house_area || a.building_area} м²`,
    a.living_area && `жилая ${a.living_area} м²`,
    a.kitchen_area && `кухня ${a.kitchen_area} м²`,
    a.land_area && `${a.land_area} сот.`,
    a.floor && a.floor_in_house && `${a.floor}/${a.floor_in_house} эт.`,
    a.year_of_construction && `${a.year_of_construction} г.`,
    a.repair, a.house_type,
  ].filter(Boolean)
})

watch([status, q], () => loadList(1), { immediate: true })
watch(selectedId, loadDetail, { immediate: true })
onUnmounted(() => clearTimeout(toastTimer))
</script>

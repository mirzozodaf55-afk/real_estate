<template>
  <AdminShell>
    <div class="flex flex-wrap items-end justify-between gap-[12px] mb-[14px]">
      <div>
        <h1 class="text-[24px] font-bold text-[#152242] m-0">{{ t('Модерация объявлений') }}</h1>
        <p class="text-[14px] text-[#64748b] m-0 mt-[2px]">{{ t(STATUSES.find((s) => s.value === status).hint) }}</p>
      </div>
      <form @submit.prevent="search" class="relative w-full sm:w-[300px]">
        <font-awesome-icon :icon="['fas', 'magnifying-glass']" class="absolute left-[12px] top-1/2 -translate-y-1/2 text-[#94a3b8] text-[13px]" />
        <input v-model="qInput" type="search" :placeholder="t('№, текст, почта или телефон владельца')" data-test="admin-search"
          class="w-full h-[40px] pl-[34px] pr-[12px] rounded-[10px] border border-[#e2e8f0] bg-white outline-none focus:border-[#0468ff] text-[14px]" />
      </form>
    </div>

    <div class="flex flex-wrap items-center gap-[10px] mb-[14px]">
      <div class="inline-flex bg-[#e9eef5] rounded-[10px] p-[3px] overflow-x-auto max-w-full">
        <button v-for="s of STATUSES" :key="s.value" @click="setStatus(s.value)" :data-test="`tab-${s.value}`"
          class="px-[14px] h-[34px] rounded-[8px] text-[14px] font-semibold transition-colors whitespace-nowrap"
          :class="status === s.value ? 'bg-white text-[#0468ff] shadow-sm' : 'text-[#475569] hover:text-[#152242]'">
          {{ t(s.label) }}
        </button>
      </div>
      <!-- риск по мнению ИИ: текст, цена и фото -->
      <div class="flex flex-wrap items-center gap-[6px]" data-test="risk-filter">
        <button v-for="r of RISK_FILTERS" :key="r.value" @click="setQuery({ risk: r.value || null, id: null })"
          class="h-[32px] px-[11px] rounded-full border text-[13px] font-semibold transition-colors"
          :class="risk === r.value ? 'border-[#152242] bg-[#152242] text-white' : 'border-[#e2e8f0] bg-white text-[#475569] hover:border-[#94a3b8]'">
          <span v-if="r.dot" class="inline-block w-[8px] h-[8px] rounded-full mr-[5px]" :class="r.dot"></span>{{ t(r.label) }}
        </button>
      </div>
      <div class="flex items-center gap-[6px] text-[13px] text-[#64748b]" data-test="date-filter">
        <span>{{ t('с') }}</span>
        <input type="date" :value="from" @change="setQuery({ from: $event.target.value, id: null })" class="h-[32px] px-[8px] rounded-[8px] border border-[#e2e8f0] bg-white text-[13px] text-[#152242]" />
        <span>{{ t('по') }}</span>
        <input type="date" :value="to" @change="setQuery({ to: $event.target.value, id: null })" class="h-[32px] px-[8px] rounded-[8px] border border-[#e2e8f0] bg-white text-[13px] text-[#152242]" />
        <button v-if="from || to" @click="setQuery({ from: null, to: null, id: null })" class="h-[32px] px-[8px] rounded-[8px] hover:bg-[#f1f5f9]" :aria-label="t('Сбросить даты')">
          <font-awesome-icon :icon="['fas', 'xmark']" />
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-[380px_1fr] gap-[16px] items-start">
      <!-- Список -->
      <div class="bg-white rounded-[16px] border border-[#e8edf3] overflow-hidden" :class="{ 'hidden xl:block': selectedId }">
        <div v-if="listLoading" class="p-[12px] flex flex-col gap-[8px]">
          <div v-for="n in 4" :key="n" class="h-[72px] rounded-[12px] bg-[#f1f5f9] animate-pulse"></div>
        </div>
        <div v-else-if="listError" class="p-[24px] text-center text-[14px] text-[#b91c1c]">{{ t(listError) }}</div>
        <div v-else-if="!items.length" class="p-[32px] text-center" data-test="queue-empty">
          <div class="w-[56px] h-[56px] mx-auto rounded-full bg-[#ecfdf5] grid place-items-center text-[#047857] text-[22px]">
            <font-awesome-icon :icon="['fas', status === 'pending' ? 'mug-hot' : 'inbox']" />
          </div>
          <p class="text-[16px] font-bold text-[#152242] mt-[12px] mb-[2px]">{{ status === 'pending' && !q ? t('Очередь пуста') : t('Ничего не найдено') }}</p>
          <p class="text-[13px] text-[#64748b] m-0">{{ status === 'pending' && !q ? t('Все объявления проверены.') : t('Попробуйте изменить запрос.') }}</p>
        </div>
        <!-- Массовые действия -->
        <div v-else-if="items.length" class="flex items-center gap-[8px] px-[12px] py-[8px] border-b border-[#eef2f7] text-[13px]" data-test="bulk-bar">
          <label class="flex items-center gap-[8px] cursor-pointer text-[#475569]">
            <input type="checkbox" :checked="allChecked" @change="toggleAll" class="w-[16px] h-[16px] accent-[#0468ff]" />
            {{ checked.length ? t('Выбрано: {n}', { n: checked.length }) : t('Выбрать все') }}
          </label>
          <template v-if="checked.length">
            <button @click="bulk('approve')" :disabled="bulkBusy" data-test="bulk-approve"
              class="ml-auto h-[30px] px-[10px] rounded-[8px] bg-[#16a34a] hover:bg-[#15803d] text-white font-semibold disabled:opacity-50">{{ t('Одобрить') }}</button>
            <button @click="bulkRejecting = !bulkRejecting" :disabled="bulkBusy" data-test="bulk-reject"
              class="h-[30px] px-[10px] rounded-[8px] border border-[#fecaca] bg-white text-[#dc2626] font-semibold">{{ t('Отклонить') }}</button>
          </template>
        </div>
        <div v-if="bulkRejecting && checked.length" class="px-[12px] py-[10px] border-b border-[#eef2f7] bg-[#fafbfd]">
          <ReasonPicker v-model:reason-id="bulkReasonId" v-model:text="bulkReasonText" :reasons="reasons" />
          <button @click="bulk('reject')" :disabled="bulkBusy || !reasonOk(bulkReasonId, bulkReasonText)" data-test="bulk-reject-confirm"
            class="mt-[8px] h-[34px] px-[14px] rounded-[9px] bg-[#dc2626] hover:bg-[#b91c1c] text-white text-[13.5px] font-semibold disabled:opacity-50">
            <font-awesome-icon v-if="bulkBusy" :icon="['fas', 'spinner']" spin-pulse /> {{ t('Отклонить выбранные (') }}{{ checked.length }})
          </button>
        </div>
        <ul v-if="!listLoading && !listError && items.length" class="list-none m-0 p-[6px] max-h-[calc(100vh-300px)] overflow-y-auto">
          <li v-for="a of items" :key="a.id" class="flex items-center gap-[2px]">
            <input type="checkbox" :value="a.id" v-model="checked" class="shrink-0 w-[16px] h-[16px] ml-[6px] accent-[#0468ff]" :aria-label="t('Выбрать №{n}', { n: a.id })" />
            <button @click="select(a.id)" :data-test="`item-${a.id}`"
              class="flex-1 min-w-0 text-left flex gap-[10px] p-[8px] rounded-[12px] transition-colors"
              :class="selectedId === a.id ? 'bg-[#eef4ff]' : 'hover:bg-[#f8fafc]'">
              <div class="w-[76px] h-[58px] rounded-[8px] overflow-hidden bg-[#e2e8f0] shrink-0">
                <img v-if="a.pictures?.[0]" :src="`${apiBase()}/api/image/xsmall/${a.pictures[0].pictures}`" class="w-full h-full object-cover" alt="" loading="lazy" />
              </div>
              <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between gap-[6px]">
                  <span class="text-[14.5px] font-bold text-[#152242] truncate">{{ formatNumber(a.price) }} {{ t('с.') }}</span>
                  <span class="flex items-center gap-[6px] shrink-0">
                    <span class="px-[6px] rounded-[6px] text-[11px] font-bold" :class="riskInfo(a.ai_risk).cls" :data-test="`risk-${a.id}`">{{ t(riskInfo(a.ai_risk).short) }}</span>
                    <span class="text-[11.5px] text-[#94a3b8] whitespace-nowrap">№{{ a.id }}</span>
                  </span>
                </div>
                <div class="text-[13px] text-[#334155] truncate">
                  <span v-if="photoVerdict(a) !== 'ok' && photoVerdict(a)" data-test="photo-flag"
                    class="mr-[4px] px-[5px] rounded-[5px] text-[11px] font-bold" :class="PHOTO[photoVerdict(a)].cls">{{ t('⚠ Фото') }}</span>{{ a.title || t(a.type_object) }}
                </div>
                <div class="text-[12px] text-[#94a3b8] truncate">
                  {{ status === 'pending' ? t('ждёт {x}', { x: timeAgo(a.updated_at, false) })
                    : a.moderated_at ? t('решение {x}', { x: timeAgo(a.moderated_at) }) : t('опубликовано до модерации') }}
                  · {{ ownerName(a.owner) }}
                </div>
              </div>
            </button>
          </li>
        </ul>
        <div v-if="lastPage > 1" class="flex items-center justify-between px-[12px] py-[8px] border-t border-[#eef2f7] text-[13px]">
          <button :disabled="page <= 1" @click="loadList(page - 1)" class="px-[10px] h-[30px] rounded-[8px] disabled:opacity-40 hover:bg-[#f1f5f9]">{{ t('← Назад') }}</button>
          <span class="text-[#64748b]">{{ page }} {{ t('из') }} {{ lastPage }} {{ t('· всего') }} {{ total }}</span>
          <button :disabled="page >= lastPage" @click="loadList(page + 1)" class="px-[10px] h-[30px] rounded-[8px] disabled:opacity-40 hover:bg-[#f1f5f9]">{{ t('Далее →') }}</button>
        </div>
      </div>

      <!-- Карточка объявления -->
      <div v-if="!selectedId" class="hidden xl:grid bg-white rounded-[16px] border border-dashed border-[#cbd5e1] min-h-[320px] place-items-center text-[14px] text-[#94a3b8]">
        {{ t('Выберите объявление слева') }}
      </div>
      <div v-else class="bg-white rounded-[16px] border border-[#e8edf3] overflow-hidden" data-test="detail">
        <div v-if="detailLoading" class="p-[16px]">
          <div class="h-[300px] rounded-[12px] bg-[#f1f5f9] animate-pulse"></div>
        </div>
        <div v-else-if="detailError" class="p-[24px] text-center text-[14px] text-[#b91c1c]">{{ t(detailError) }}</div>
        <template v-else-if="detail">
          <div class="flex items-center gap-[8px] px-[16px] pt-[14px]">
            <button @click="select(null)" class="xl:hidden h-[32px] px-[10px] whitespace-nowrap rounded-[8px] text-[13px] font-semibold text-[#475569] hover:bg-[#f1f5f9]">{{ t('← К списку') }}</button>
            <span class="px-[8px] py-[2px] rounded-[8px] text-[12px] font-semibold" :class="statusBadge(detail).cls">{{ t(statusBadge(detail).label) }}</span>
            <span v-if="detail.deleted_at" class="px-[8px] py-[2px] rounded-[8px] text-[12px] font-semibold bg-[#f1f5f9] text-[#475569]">{{ t('Снято владельцем') }}</span>
            <span v-if="detail.sold_at" class="px-[8px] py-[2px] rounded-[8px] text-[12px] font-semibold bg-[#eef2ff] text-[#4338ca]">{{ detail.deal_type === 'Аренда' ? t('Сдано') : t('Продано') }} {{ timeAgo(detail.sold_at) }}</span>
            <span class="ml-auto text-[12.5px] text-[#94a3b8]">№{{ detail.id }} {{ t('· изменено') }} {{ timeAgo(detail.updated_at) }}</span>
          </div>

          <!-- Фото -->
          <div class="p-[16px] pb-0">
            <!-- лента фото: плавный сдвиг, свайп на телефоне — как в галерее объявления -->
            <div class="group relative rounded-[12px] overflow-hidden bg-[#0f172a] aspect-[16/9]" @touchstart.passive="swipeStart" @touchend="swipeEnd">
              <div v-if="detail.pictures.length" class="flex h-full transition-transform duration-[450ms] ease-[cubic-bezier(.22,.61,.36,1)]"
                :style="`transform: translateX(-${photoIndex * 100}%)`">
                <div v-for="(p, i) of detail.pictures" :key="p.id" class="w-full h-full shrink-0">
                  <img v-if="Math.abs(i - photoIndex) <= 1 || seen.has(i)" :src="`${apiBase()}/api/image/medium/${p.pictures}`" :style="`transform: rotate(${p.rotation || 0}deg)`"
                    class="w-full h-full object-contain" alt="" draggable="false" />
                </div>
              </div>
              <div v-else class="w-full h-full grid place-items-center text-[#64748b]">{{ t('Нет фото') }}</div>
              <template v-if="detail.pictures.length > 1">
                <button @click="shiftPhoto(-1)" :aria-label="t('Предыдущее фото')"
                  class="absolute left-[10px] top-1/2 -translate-y-1/2 w-[38px] h-[38px] rounded-full bg-black/50 hover:bg-black/70 text-white grid place-items-center backdrop-blur-sm transition-colors">
                  <font-awesome-icon :icon="['fas', 'chevron-left']" />
                </button>
                <button @click="shiftPhoto(1)" :aria-label="t('Следующее фото')"
                  class="absolute right-[10px] top-1/2 -translate-y-1/2 w-[38px] h-[38px] rounded-full bg-black/50 hover:bg-black/70 text-white grid place-items-center backdrop-blur-sm transition-colors">
                  <font-awesome-icon :icon="['fas', 'chevron-right']" />
                </button>
                <span class="absolute bottom-[10px] right-[10px] px-[8px] py-[2px] rounded-[8px] bg-black/55 text-white text-[12px]">{{ photoIndex + 1 }} / {{ detail.pictures.length }}</span>
              </template>
              <span v-if="photo && photoNote(photo)?.ok === false" class="absolute top-[10px] left-[10px] px-[8px] py-[3px] rounded-[8px] bg-[#dc2626] text-white text-[12px] font-semibold max-w-[70%] truncate">
                <font-awesome-icon :icon="['fas', 'triangle-exclamation']" class="mr-[4px]" />{{ aiText(photoNote(photo), 'what') }}
              </span>
            </div>
            <div v-if="detail.pictures.length > 1" ref="thumbs" class="flex gap-[8px] mt-[8px] overflow-x-auto p-[3px]">
              <button v-for="(p, i) of detail.pictures" :key="p.id" @click="photoIndex = i" :data-thumb="i"
                class="relative w-[68px] h-[48px] rounded-[8px] overflow-hidden shrink-0 border-2 transition-all"
                :class="[photoNote(p)?.ok === false ? 'border-[#dc2626]' : 'border-transparent',
                  i === photoIndex ? 'ring-2 ring-[#0468ff] ring-offset-2 ring-offset-white' : 'opacity-60 hover:opacity-100']">
                <img :src="`${apiBase()}/api/image/xsmall/${p.pictures}`" class="w-full h-full object-cover" alt="" />
                <span v-if="photoNote(p)?.ok === false" class="absolute inset-x-0 bottom-0 bg-[#dc2626] text-white text-[9px] font-bold leading-[13px]">{{ t('не то') }}</span>
              </button>
            </div>

            <!-- Проверка фото ИИ: сравнивает снимки с данными объявления -->
            <div v-if="aiEnabled" class="mt-[10px] rounded-[12px] border p-[10px]" :class="detail.photo_check ? PHOTO[detail.photo_check.verdict]?.box : 'border-[#e8edf3]'" data-test="photo-check">
              <div class="flex flex-wrap items-center gap-[8px]">
                <span class="text-[12px] font-bold uppercase tracking-[0.05em] text-[#7c3aed]"><font-awesome-icon :icon="['fas', 'wand-magic-sparkles']" /> {{ t('Фото глазами ИИ') }}</span>
                <span v-if="detail.photo_check" class="px-[8px] py-[1px] rounded-full text-[11.5px] font-bold" :class="PHOTO[detail.photo_check.verdict]?.cls">{{ t(PHOTO[detail.photo_check.verdict]?.label) }}</span>
                <button @click="runPhotoCheck" :disabled="photoChecking" data-test="photo-recheck"
                  class="ml-auto h-[30px] px-[10px] rounded-[8px] border border-[#ddd6fe] bg-white text-[#6d28d9] hover:bg-[#f5f3ff] text-[12.5px] font-semibold disabled:opacity-60">
                  <font-awesome-icon v-if="photoChecking" :icon="['fas', 'spinner']" spin-pulse />
                  {{ detail.photo_check ? t('Перепроверить') : t('Проверить фото') }}
                </button>
              </div>
              <p v-if="photoCheckError" class="text-[12.5px] text-[#b91c1c] mt-[6px] mb-0">{{ t(photoCheckError) }}</p>
              <template v-if="detail.photo_check">
                <p class="text-[13px] text-[#334155] mt-[6px] mb-0">{{ aiText(detail.photo_check, 'summary') }}</p>
                <ul class="list-none m-0 mt-[4px] p-0 text-[12.5px] leading-[1.5]">
                  <li v-for="(p, i) of detail.photo_check.photos" :key="i" :class="p.ok ? 'text-[#475569]' : 'text-[#b91c1c] font-semibold'">
                    {{ p.ok ? '✓' : '✗' }} {{ t('Фото') }} {{ i + 1 }}: {{ aiText(p, 'what') }}
                  </li>
                </ul>
                <div class="text-[11px] text-[#94a3b8] mt-[4px]">
                  {{ t('Проверено') }} {{ timeAgo(detail.photo_check_at) }}<span v-if="detail.pictures.length > detail.photo_check.checked_photos"> {{ t('· смотрели первые') }} {{ detail.photo_check.checked_photos }} {{ t('фото') }}</span>
                </div>
              </template>
              <p v-else-if="!photoChecking" class="text-[12.5px] text-[#94a3b8] mt-[4px] mb-0">{{ t('Ещё не проверялись — новые объявления ИИ проверяет сам через несколько секунд после подачи.') }}</p>
            </div>
          </div>

          <div class="p-[16px] grid grid-cols-1 lg:grid-cols-[1fr_260px] gap-[16px]">
            <div class="min-w-0">
              <div class="text-[24px] font-bold text-[#152242]">{{ formatNumber(detail.price) }} {{ t('сомони') }}<span class="text-[14px] font-normal text-[#64748b]">{{ priceSuffix(detail) }}</span></div>
              <div class="text-[17px] font-semibold text-[#0468ff] mt-[2px]">{{ detail.title || t('Без заголовка') }}</div>
              <div class="flex flex-wrap gap-[6px] mt-[10px]">
                <span v-for="f of facts" :key="f" class="px-[9px] py-[3px] rounded-[8px] bg-[#f1f5f9] text-[13px] text-[#334155]">{{ f }}</span>
              </div>
              <p class="text-[14.5px] text-[#334155] leading-[1.6] mt-[12px] mb-0 whitespace-pre-line break-words">{{ detail.description || t('Описание не заполнено') }}</p>
              <div v-if="detail.conditions?.length || detail.infrastructure?.length" class="mt-[12px] text-[13px] text-[#64748b]">
                <div v-if="detail.conditions?.length"><b class="text-[#334155]">{{ t('Удобства:') }}</b> {{ detail.conditions.join(', ') }}</div>
                <div v-if="detail.infrastructure?.length" class="mt-[4px]"><b class="text-[#334155]">{{ t('Рядом:') }}</b> {{ detail.infrastructure.join(', ') }}</div>
              </div>
              <a v-if="detail.link_video" :href="detail.link_video" target="_blank" rel="noopener noreferrer" class="inline-block mt-[10px] text-[14px] text-[#0468ff]">{{ t('Видео по ссылке ↗') }}</a>
            </div>

            <div class="flex flex-col gap-[12px]">
              <div v-if="aiEnabled" class="rounded-[12px] border border-[#e9d5ff] bg-gradient-to-b from-[#faf5ff] to-white p-[12px]" data-test="ai-review">
                <div class="flex items-center justify-between gap-[8px]">
                  <div class="text-[12px] font-bold uppercase tracking-[0.06em] text-[#7c3aed]">
                    <font-awesome-icon :icon="['fas', 'wand-magic-sparkles']" /> {{ t('Проверка ИИ') }}
                  </div>
                  <span v-if="aiReview" class="px-[8px] py-[1px] rounded-full text-[11.5px] font-bold" :class="RISK[aiReview.risk]?.cls">{{ t(RISK[aiReview.risk]?.label) }}</span>
                </div>
                <button v-if="!aiReview" @click="runAiReview(false)" :disabled="aiLoading" data-test="ai-review-run"
                  class="mt-[8px] w-full h-[36px] rounded-[10px] bg-[#7c3aed] hover:bg-[#6d28d9] disabled:opacity-60 text-white text-[13.5px] font-semibold">
                  <font-awesome-icon v-if="aiLoading" :icon="['fas', 'spinner']" spin-pulse /> {{ aiLoading ? t('Проверяю…') : t('Проверить объявление') }}
                </button>
                <p v-if="!aiReview && !aiLoading" class="text-[11.5px] text-[#94a3b8] mt-[6px] mb-0">{{ t('Новые и изменённые объявления ИИ проверяет сам через несколько секунд после подачи.') }}</p>
                <p v-if="aiErr" class="text-[12.5px] text-[#b91c1c] mt-[6px] mb-0">{{ t(aiErr) }}</p>
                <template v-if="aiReview">
                  <p class="text-[13px] text-[#334155] leading-[1.45] mt-[8px] mb-0">{{ aiText(aiReview, 'summary') }}</p>
                  <ul v-if="aiText(aiReview, 'issues').length" class="text-[12.5px] text-[#475569] leading-[1.45] mt-[6px] mb-0 pl-[16px]">
                    <li v-for="(issue, i) of aiText(aiReview, 'issues')" :key="i">{{ issue }}</li>
                  </ul>
                  <div v-if="aiReview.market?.['медиана']" class="text-[11.5px] text-[#94a3b8] mt-[6px]">
                    {{ t('Похожих:') }} {{ aiReview.market['похожих_объявлений'] }}{{ t(', медиана') }} {{ formatNumber(aiReview.market['медиана']) }} {{ t('с.') }}
                  </div>
                  <button v-if="aiReview.suggested_reason && detail.moderation_status !== 'rejected'" @click="useAiReason" data-test="ai-use-reason"
                    class="mt-[8px] w-full h-[32px] rounded-[9px] border border-[#fecaca] bg-white text-[#dc2626] hover:bg-[#fef2f2] text-[12.5px] font-semibold">
                    {{ t('Использовать как причину отказа') }}
                  </button>
                  <div class="flex items-center justify-between gap-[8px] mt-[6px]">
                    <p class="text-[11px] text-[#94a3b8] m-0">{{ t('Подсказка, а не решение: фото ИИ здесь не видит.') }}<span v-if="detail.ai_review_at" class="ml-[3px]">{{ t('Проверено') }} {{ timeAgo(detail.ai_review_at) }}.</span></p>
                    <button @click="runAiReview(true)" :disabled="aiLoading" class="shrink-0 text-[11.5px] font-semibold text-[#7c3aed] hover:underline disabled:opacity-50">
                      <font-awesome-icon v-if="aiLoading" :icon="['fas', 'spinner']" spin-pulse /> {{ t('Перепроверить') }}
                    </button>
                  </div>
                </template>
              </div>

              <div v-if="detail.duplicates?.photos?.length || detail.duplicates?.text?.length" class="rounded-[12px] border border-[#fde68a] bg-[#fffbeb] p-[12px]" data-test="duplicates">
                <div class="text-[12px] font-bold uppercase tracking-[0.06em] text-[#b45309] mb-[6px]">
                  <font-awesome-icon :icon="['far', 'copy']" /> {{ t('Возможный дубликат') }}
                </div>
                <ul class="list-none m-0 p-0 flex flex-col gap-[6px]">
                  <li v-for="d of [...detail.duplicates.photos, ...detail.duplicates.text]" :key="d.id + d.match" class="text-[12.5px] leading-[1.4]">
                    <a :href="`/admin/announs?status=${d.status === 'withdrawn' || d.status === 'sold' ? 'approved' : d.status}&id=${d.id}`" target="_blank"
                      class="font-semibold text-[#0468ff] hover:underline">№{{ d.id }} {{ d.title }}</a>
                    <div class="text-[#92400e]">{{ dupMatch(d) }}{{ d.owner_id === detail.owner_id ? t(' · тот же владелец') : t(' · другой владелец') }} · {{ t(DUP_STATUS[d.status] || d.status) }}</div>
                  </li>
                </ul>
              </div>

              <div class="rounded-[12px] border border-[#e8edf3] p-[12px]">
                <div class="text-[12px] font-bold uppercase tracking-[0.06em] text-[#94a3b8] mb-[8px]">{{ t('Владелец') }}</div>
                <div class="flex items-center gap-[10px]">
                  <div class="w-[40px] h-[40px] rounded-full bg-[#e2e8f0] bg-cover bg-center shrink-0" :style="`background-image: url('${apiBase()}/api/avatar/${detail.owner?.avatar}')`"></div>
                  <div class="min-w-0">
                    <div class="text-[14px] font-semibold text-[#152242] truncate">{{ ownerName(detail.owner) }}</div>
                    <div class="text-[12.5px] text-[#64748b] truncate">{{ detail.owner?.email || detail.owner?.phone_number }}</div>
                  </div>
                </div>
                <div class="text-[12.5px] text-[#64748b] mt-[8px] leading-[1.6]">
                  {{ t('На сайте') }} {{ timeAgo(detail.owner?.created_at, false) }}<br />
                  {{ t('Объявлений:') }} {{ detail.owner_stats.total }}
                  <span v-if="detail.owner_stats.rejected" class="text-[#dc2626]">{{ t('· отклонено') }} {{ detail.owner_stats.rejected }}</span>
                </div>
              </div>

              <div v-if="detail.moderation_reason" class="rounded-[12px] p-[12px] text-[13px]"
                :class="detail.moderation_status === 'needs_changes' ? 'bg-[#fffbeb] text-[#92400e]' : 'bg-[#fef2f2] text-[#991b1b]'">
                <b>{{ detail.moderation_status === 'needs_changes' ? t('Что поправить:') : t('Причина отказа:') }}</b> {{ detail.moderation_reason }}
              </div>

              <div v-if="detail.history?.length" class="rounded-[12px] border border-[#e8edf3] p-[12px]">
                <div class="text-[12px] font-bold uppercase tracking-[0.06em] text-[#94a3b8] mb-[6px]">{{ t('История') }}</div>
                <ul class="list-none m-0 p-0 flex flex-col gap-[6px]">
                  <li v-for="h of detail.history" :key="h.id" class="text-[12.5px] text-[#475569] leading-[1.4]">
                    <span :class="{ approve: 'text-[#047857]', needs_changes: 'text-[#b45309]' }[h.action] || 'text-[#dc2626]'" class="font-semibold">{{ { approve: t('Одобрено'), needs_changes: t('На доработку') }[h.action] || t('Отклонено') }}</span>
                    · {{ h.moderator_id ? ownerName(h.moderator) : t('Автомодерация (ИИ)') }} · {{ timeAgo(h.created_at) }}
                    <div v-if="h.reason" class="text-[#94a3b8]">«{{ h.reason }}»</div>
                  </li>
                </ul>
              </div>
            </div>
          </div>

          <!-- Решение -->
          <div class="border-t border-[#eef2f7] bg-[#fafbfd] p-[16px]">
            <div v-if="rejecting">
              <div class="text-[14px] font-semibold text-[#152242]">{{ t('Причина отказа — её увидит владелец') }}</div>
              <ReasonPicker class="mt-[8px]" v-model:reason-id="reasonId" v-model:text="reason" :reasons="reasons" input-id="reject-reason" />
              <div class="flex flex-wrap items-center gap-[8px] mt-[8px]">
                <button @click="decide('reject')" :disabled="busy || !reasonOk(reasonId, reason)" data-test="confirm-reject"
                  class="h-[40px] px-[18px] rounded-[10px] bg-[#dc2626] hover:bg-[#b91c1c] disabled:opacity-50 text-white text-[14px] font-semibold">
                  <font-awesome-icon v-if="busy" :icon="['fas', 'spinner']" spin-pulse /> {{ t('Отклонить') }}
                </button>
                <button @click="rejecting = false" class="h-[40px] px-[14px] rounded-[10px] text-[14px] font-semibold text-[#475569] hover:bg-[#f1f5f9]">{{ t('Отмена') }}</button>
              </div>
            </div>
            <div v-else-if="changing">
              <label class="text-[14px] font-semibold text-[#152242]" for="changes-comment">{{ t('Что нужно исправить — увидит владелец') }}</label>
              <textarea id="changes-comment" v-model="comment" rows="3" maxlength="500" data-test="changes-comment"
                class="w-full mt-[8px] p-[10px] rounded-[10px] border border-[#e2e8f0] bg-white outline-none focus:border-[#d97706] text-[14px] resize-y"
                :placeholder="t('Например: добавьте фото кухни и санузла, укажите этаж')"></textarea>
              <div class="flex flex-wrap items-center gap-[8px] mt-[8px]">
                <button @click="decide('needs-changes')" :disabled="busy || comment.trim().length < 5" data-test="confirm-changes"
                  class="h-[40px] px-[18px] rounded-[10px] bg-[#d97706] hover:bg-[#b45309] disabled:opacity-50 text-white text-[14px] font-semibold">
                  <font-awesome-icon v-if="busy" :icon="['fas', 'spinner']" spin-pulse /> {{ t('Отправить на доработку') }}
                </button>
                <button @click="changing = false" class="h-[40px] px-[14px] rounded-[10px] text-[14px] font-semibold text-[#475569] hover:bg-[#f1f5f9]">{{ t('Отмена') }}</button>
                <span v-if="comment && comment.trim().length < 5" class="text-[12.5px] text-[#94a3b8]">{{ t('минимум 5 символов') }}</span>
              </div>
            </div>
            <div v-else class="flex flex-wrap items-center gap-[8px]">
              <button v-if="detail.moderation_status !== 'approved'" @click="decide('approve')" :disabled="busy" data-test="approve"
                class="h-[42px] px-[20px] rounded-[10px] bg-[#16a34a] hover:bg-[#15803d] disabled:opacity-50 text-white text-[14.5px] font-semibold">
                <font-awesome-icon :icon="['fas', busy ? 'spinner' : 'check']" :spin-pulse="busy" class="mr-[6px]" /> {{ t('Одобрить') }}
              </button>
              <button v-if="!['needs_changes', 'approved'].includes(detail.moderation_status)" @click="startChanges" :disabled="busy" data-test="needs-changes"
                class="h-[42px] px-[18px] rounded-[10px] border border-[#fde68a] bg-white text-[#b45309] hover:bg-[#fffbeb] text-[14.5px] font-semibold">
                <font-awesome-icon :icon="['fas', 'pen']" class="mr-[6px]" /> {{ t('На доработку') }}
              </button>
              <button v-if="detail.moderation_status !== 'rejected'" @click="startReject" :disabled="busy" data-test="reject"
                class="h-[42px] px-[18px] rounded-[10px] border border-[#fecaca] bg-white text-[#dc2626] hover:bg-[#fef2f2] text-[14.5px] font-semibold">
                <font-awesome-icon :icon="['fas', 'xmark']" class="mr-[6px]" /> {{ detail.moderation_status === 'approved' ? t('Снять с публикации') : t('Отклонить') }}
              </button>
              <span class="text-[12.5px] text-[#94a3b8] ml-auto">{{ t('Владелец получит уведомление и письмо') }}</span>
            </div>
          </div>
        </template>
      </div>
    </div>

    <!-- в углу и без pointer-events: не должен перекрывать кнопки решения под собой -->
    <div v-if="toast" class="fixed top-[84px] right-[20px] z-[500] max-w-[calc(100vw-40px)] px-[16px] py-[10px] rounded-[12px] bg-[#152242] text-white text-[14px] shadow-lg pointer-events-none" role="status">
      {{ t(toast) }}
    </div>
  </AdminShell>
</template>

<script setup>
useHead({ title: () => t('Модерация объявлений') })

const STATUSES = [
  { value: 'pending', label: 'На проверке', hint: 'Скрыты из поиска, пока вы их не проверите. Сначала — высокий риск по мнению ИИ, затем давно ждущие.' },
  { value: 'needs_changes', label: 'На доработке', hint: 'Вернули владельцу с комментарием — после правки вернутся в очередь.' },
  { value: 'approved', label: 'Одобренные', hint: 'Видны в поиске. Можно снять с публикации, если нашлась проблема.' },
  { value: 'rejected', label: 'Отклонённые', hint: 'Ждут исправления владельцем — после правки вернутся в очередь.' },
]
const RISK_FILTERS = [
  { value: '', label: 'Любой риск' },
  { value: 'high', label: 'Высокий', dot: 'bg-[#dc2626]' },
  { value: 'medium', label: 'Средний', dot: 'bg-[#f59e0b]' },
  { value: 'low', label: 'Низкий', dot: 'bg-[#16a34a]' },
  { value: 'none', label: 'Не проверено', dot: 'bg-[#94a3b8]' },
]
// чем похож дубликат (текст с сервера): «то же фото», «текст совпадает на 92%» — на языке сайта
const dupMatch = (d) => {
  const m = /^текст совпадает на (\d+)%$/.exec(d.match)
  return m ? t('текст совпадает на {n}%', { n: m[1] }) : t(d.match)
}
const DUP_STATUS = { pending: 'на проверке', approved: 'опубликовано', rejected: 'отклонено', needs_changes: 'на доработке', sold: 'продано', withdrawn: 'снято' }

const route = useRoute()
const router = useRouter()
const pending = usePendingCount()

// состояние в URL: ссылку на конкретное объявление можно переслать коллеге
const status = computed(() => (STATUSES.some((s) => s.value === route.query.status) ? route.query.status : 'pending'))
const q = computed(() => route.query.q || '')
const risk = computed(() => (RISK_FILTERS.some((r) => r.value && r.value === route.query.risk) ? route.query.risk : ''))
const from = computed(() => route.query.from || '')
const to = computed(() => route.query.to || '')
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

// Ответы ИИ (проверка фото, текста и цены) хранятся на двух языках: поле и поле_en.
// Показываем на языке сайта; у старых проверок английского нет — тогда русский (обновится по «Перепроверить»)
const { lang } = useLang()
function aiText(obj, field) {
  if (!obj) return field === 'issues' ? [] : ''
  const en = obj[field + '_en']
  const hasEn = Array.isArray(en) ? en.length > 0 : !!en
  return lang.value === 'en' && hasEn ? en : (obj[field] ?? (field === 'issues' ? [] : ''))
}

// --- Галерея: плавная лента, свайп, уже показанные фото не грузим заново
const seen = ref(new Set([0]))
const thumbs = ref(null)
watch(photoIndex, (i) => {
  seen.value = new Set([...seen.value, i])
  nextTick(() => thumbs.value?.querySelector(`[data-thumb="${i}"]`)?.scrollIntoView({ block: 'nearest', inline: 'nearest', behavior: 'smooth' }))
})
function shiftPhoto(step) {
  const n = detail.value?.pictures?.length || 0
  if (n) photoIndex.value = (photoIndex.value + step + n) % n
}
let touchX = null
const swipeStart = (e) => { touchX = e.touches[0].clientX }
function swipeEnd(e) {
  if (touchX === null) return
  const dx = e.changedTouches[0].clientX - touchX
  touchX = null
  if (Math.abs(dx) >= 40) shiftPhoto(dx < 0 ? 1 : -1)
}

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
    if (risk.value) params.set('risk', risk.value)
    if (from.value) params.set('from', from.value)
    if (to.value) params.set('to', to.value)
    const res = await adminApi(`/announs?${params}`)
    items.value = res.data
    checked.value = checked.value.filter((id) => res.data.some((a) => a.id === id))
    page.value = res.current_page
    lastPage.value = res.last_page
    total.value = res.total
    if (status.value === 'pending' && !q.value && !risk.value && !from.value && !to.value) pending.value = res.total
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
  low: { label: 'Низкий риск', short: 'Низкий', cls: 'bg-[#ecfdf5] text-[#047857]' },
  medium: { label: 'Есть вопросы', short: 'Средний', cls: 'bg-[#fffbeb] text-[#b45309]' },
  high: { label: 'Высокий риск', short: 'Высокий', cls: 'bg-[#fef2f2] text-[#dc2626]' },
}
const riskInfo = (r) => RISK[r] || { short: 'Не проверено', cls: 'bg-[#f1f5f9] text-[#64748b]' }

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

async function runAiReview(force) {
  aiLoading.value = true
  aiErr.value = ''
  const id = detail.value.id
  try {
    const res = await adminApi(`/announs/${id}/ai-review${force ? '?force=1' : ''}`, { method: 'POST' })
    if (detail.value?.id === id) { // модератор мог уже перейти к другому объявлению
      aiReview.value = res
      detail.value.ai_review_at = new Date().toISOString()
      // общий риск пересчитан на сервере — обновим список, чтобы очередь пересортировалась
      loadList(page.value)
    }
  } catch (e) {
    aiErr.value = e.message
  } finally {
    aiLoading.value = false
  }
}

function useAiReason() {
  startReject()
  reasonId.value = null
  reason.value = aiReview.value.suggested_reason // владельцу — по-русски, даже если модератор видит английский вывод ИИ
}

async function loadDetail(id) {
  rejecting.value = false
  changing.value = false
  reason.value = ''
  reasonId.value = null
  comment.value = ''
  photoIndex.value = 0
  seen.value = new Set([0])
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
    // ИИ уже проверил текст и цену при подаче — показываем сразу, без кнопки
    aiReview.value = detail.value.ai_review
  } catch (e) {
    detail.value = null
    detailError.value = e.status === 404 ? 'Объявление не найдено — возможно, владелец его удалил' : 'Не удалось загрузить: ' + e.message
  } finally {
    detailLoading.value = false
  }
}

function startReject() {
  changing.value = false
  rejecting.value = true
  loadReasons()
}
function startChanges() {
  rejecting.value = false
  changing.value = true
  nextTick(() => document.getElementById('changes-comment')?.focus())
}

// --- Готовые причины отказа (справочник в админке → «Причины отказа»)
const reasons = ref([])
const reasonId = ref(null)
const changing = ref(false)
const comment = ref('')
let reasonsLoaded = false
async function loadReasons() {
  if (reasonsLoaded) return
  try {
    reasons.value = await adminApi('/rejection-reasons')
    reasonsLoaded = true
  } catch (e) { /* без справочника можно написать причину самому */ }
}
// готовая причина или свой текст от 5 символов («Другое»)
const reasonOk = (id, text) => !!id || (text || '').trim().length >= 5
// готовую причину владелец получит по-русски (сервер берёт text, не text_en) — на каком языке ни работал бы модератор
const reasonBody = (id, text) => ({ reason_id: id || undefined, reason: (text || '').trim() || undefined })

// --- Массовые действия
const checked = ref([])
const bulkBusy = ref(false)
const bulkRejecting = ref(false)
const bulkReasonId = ref(null)
const bulkReasonText = ref('')
const allChecked = computed(() => items.value.length > 0 && items.value.every((a) => checked.value.includes(a.id)))
function toggleAll() {
  checked.value = allChecked.value ? [] : items.value.map((a) => a.id)
}
watch(bulkRejecting, (on) => on && loadReasons())
async function bulk(action) {
  bulkBusy.value = true
  try {
    const res = await adminApi('/announs/bulk', {
      method: 'POST',
      body: { ids: checked.value, action, ...(action === 'reject' ? reasonBody(bulkReasonId.value, bulkReasonText.value) : {}) },
    })
    showToast(t(action === 'approve' ? 'Одобрено: {n}' : 'Отклонено: {n}', { n: res.count }))
    if (checked.value.includes(selectedId.value)) await loadDetail(selectedId.value)
    checked.value = []
    bulkRejecting.value = false
    bulkReasonId.value = null
    bulkReasonText.value = ''
    await loadList(page.value)
  } catch (e) {
    showToast(e.message)
  } finally {
    bulkBusy.value = false
  }
}

async function decide(action) {
  busy.value = true
  try {
    const res = await adminApi(`/announs/${detail.value.id}/${action}`, {
      method: 'POST',
      body: action === 'reject' ? reasonBody(reasonId.value, reason.value)
        : action === 'needs-changes' ? { comment: comment.value.trim() } : undefined,
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
  needs_changes: { label: 'На доработке', cls: 'bg-[#fff7ed] text-[#ea580c]' },
}[a.moderation_status] || { label: a.moderation_status, cls: 'bg-[#f1f5f9]' })

const ownerName = (o) => (o ? [o.name, o.last_name].filter(Boolean).join(' ') || o.email || o.phone_number || `#${o.id}` : '—')

const facts = computed(() => {
  const a = detail.value
  if (!a) return []
  const deal = a.deal_type === 'Аренда' ? (a.rental_type === 'Посуточно' ? 'Посуточно' : 'Аренда') : a.deal_type
  return [
    t(deal), t(a.type_object),
    a.quantity_room && t('{n}-комн.', { n: a.quantity_room }),
    (a.total_area || a.house_area || a.building_area) && `${a.total_area || a.house_area || a.building_area} ${t('м²')}`,
    a.living_area && t('жилая {n} м²', { n: a.living_area }),
    a.kitchen_area && t('кухня {n} м²', { n: a.kitchen_area }),
    a.land_area && t('{n} сот.', { n: a.land_area }),
    a.floor && a.floor_in_house && t('{a}/{b} эт.', { a: a.floor, b: a.floor_in_house }),
    a.year_of_construction && t('{n} г.', { n: a.year_of_construction }),
    a.repair && t(a.repair), a.house_type && t(a.house_type),
  ].filter(Boolean)
})

watch([status, q, risk, from, to], () => { checked.value = []; loadList(1) }, { immediate: true })
watch(selectedId, loadDetail, { immediate: true })
onUnmounted(() => clearTimeout(toastTimer))
</script>

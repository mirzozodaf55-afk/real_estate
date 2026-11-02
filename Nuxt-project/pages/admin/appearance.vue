<template>
  <AdminShell v-slot="{ user: me }">
    <div v-if="me.role !== 'admin'" class="bg-white rounded-[16px] border border-[#e8edf3] p-[28px] text-center text-[15px] text-[#475569]">
      Оформление сайта меняет только администратор.
    </div>
    <template v-else>
      <div class="mb-[14px]">
        <h1 class="text-[24px] font-bold text-[#152242] m-0">Оформление</h1>
        <p class="text-[14px] text-[#64748b] m-0 mt-[2px]">Фото фона главного блока на главной странице</p>
      </div>

      <!-- Предпросмотр: так блок будет выглядеть на главной -->
      <div class="relative rounded-[16px] overflow-hidden h-[300px] sm:h-[340px] bg-gradient-to-br from-[#0b1f4d] via-[#0a3a9e] to-[#0468ff]" data-test="hero-preview">
        <template v-if="previewUrl">
          <div class="absolute inset-0 bg-cover bg-center" :style="{ backgroundImage: `url('${previewUrl}')` }"></div>
          <div class="absolute inset-0" :style="{ background: heroOverlayStyle(overlay) }"></div>
        </template>
        <div class="relative h-full flex flex-col justify-center px-[24px] sm:px-[40px]">
          <div class="text-white font-bold text-[22px] sm:text-[30px] leading-[1.15] max-w-[520px]">Найдите жильё, которое подходит именно вам</div>
          <div class="text-white/80 text-[13px] sm:text-[15px] mt-[8px] max-w-[460px]">Квартиры, дома и коммерческая недвижимость — купить, снять надолго или посуточно</div>
          <div class="mt-[18px] h-[48px] max-w-[620px] rounded-[12px] bg-white shadow-lg flex items-center px-[14px] gap-[10px] text-[13px] text-[#94a3b8]">
            <span class="text-[#152242] font-semibold">Квартира</span><span>·</span><span>Любое количество</span><span>·</span><span>Цена</span>
            <span class="ml-auto h-[34px] px-[16px] rounded-[9px] bg-[#0468ff] text-white font-bold grid place-items-center">Найти</span>
          </div>
        </div>
        <span v-if="file" class="absolute top-[12px] right-[12px] px-[10px] py-[3px] rounded-full bg-[#f59e0b] text-white text-[12px] font-bold">Не сохранено</span>
      </div>

      <div class="mt-[14px] grid grid-cols-1 lg:grid-cols-[1fr_320px] gap-[14px]">
        <div class="bg-white rounded-[16px] border border-[#e8edf3] p-[16px]">
          <div class="text-[15px] font-bold text-[#152242]">Фото фона</div>
          <p class="text-[13px] text-[#64748b] mt-[2px] mb-[12px]">Лучше горизонтальное фото от 1920 px в ширину: дом, интерьер, вид на город. Текст стоит слева — важное на фото держите справа или по центру.</p>

          <label class="flex flex-col items-center justify-center gap-[6px] h-[120px] rounded-[12px] border-2 border-dashed cursor-pointer transition-colors"
            :class="dragOver ? 'border-[#0468ff] bg-[#eef4ff]' : 'border-[#cbd5e1] hover:border-[#0468ff] hover:bg-[#f8fafc]'"
            @dragover.prevent="dragOver = true" @dragleave="dragOver = false" @drop.prevent="onDrop">
            <font-awesome-icon :icon="['fas', 'cloud-arrow-up']" class="text-[24px] text-[#0468ff]" />
            <span class="text-[14px] font-semibold text-[#152242]">{{ file ? file.name : 'Выберите или перетащите фото' }}</span>
            <span class="text-[12px] text-[#94a3b8]">JPG, PNG или WEBP, до 20 МБ</span>
            <input type="file" accept="image/*" class="hidden" @change="pick($event.target.files[0]); $event.target.value = ''" data-test="hero-file" />
          </label>

          <div class="flex flex-wrap gap-[8px] mt-[12px]">
            <button @click="save" :disabled="saving || !dirty" data-test="hero-save"
              class="h-[40px] px-[18px] rounded-[10px] bg-[#0468ff] hover:bg-[#0353cc] disabled:opacity-50 text-white text-[14px] font-semibold">
              <font-awesome-icon v-if="saving" :icon="['fas', 'spinner']" spin-pulse /> Сохранить
            </button>
            <button v-if="file" @click="cancelFile" class="h-[40px] px-[14px] rounded-[10px] text-[14px] font-semibold text-[#475569] hover:bg-[#f1f5f9]">Отменить выбор</button>
            <button v-if="settings.hero_image && !file" @click="remove" :disabled="saving" data-test="hero-delete"
              class="h-[40px] px-[14px] rounded-[10px] border border-[#fecaca] text-[#dc2626] hover:bg-[#fef2f2] text-[14px] font-semibold ml-auto">
              Убрать фото — вернуть синий фон
            </button>
          </div>
          <p v-if="error" class="text-[13px] text-[#b91c1c] mt-[8px] mb-0">{{ error }}</p>
        </div>

        <div class="bg-white rounded-[16px] border border-[#e8edf3] p-[16px]">
          <div class="flex items-center justify-between">
            <span class="text-[15px] font-bold text-[#152242]">Затемнение</span>
            <span class="text-[14px] font-semibold text-[#0468ff]">{{ overlay }}%</span>
          </div>
          <p class="text-[13px] text-[#64748b] mt-[2px] mb-[12px]">Чем светлее фото, тем сильнее затемнение — чтобы белый заголовок легко читался.</p>
          <input v-model.number="overlay" type="range" min="0" max="85" step="5" class="w-full accent-[#0468ff]" :disabled="!previewUrl" data-test="hero-overlay" />
          <div class="flex justify-between text-[11.5px] text-[#94a3b8]"><span>без затемнения</span><span>сильное</span></div>
          <p v-if="!previewUrl" class="text-[12px] text-[#94a3b8] mt-[8px] mb-0">Сначала выберите фото.</p>
        </div>
      </div>
    </template>

    <div v-if="toast" class="fixed top-[84px] right-[20px] z-[600] px-[16px] py-[10px] rounded-[12px] bg-[#152242] text-white text-[14px] shadow-lg pointer-events-none" role="status">{{ toast }}</div>
  </AdminShell>
</template>

<script setup>
useHead({ title: 'Админка — оформление' })

const { settings, reload } = useSiteSettings()
const file = ref(null)
const localUrl = ref('')
const overlay = ref(settings.value.hero_overlay)
const saving = ref(false)
const error = ref('')
const toast = ref('')
const dragOver = ref(false)

// настройки могли догрузиться уже после открытия страницы
watch(() => settings.value.hero_overlay, (v) => { if (!dirty.value) overlay.value = v })

const previewUrl = computed(() => localUrl.value || settings.value.hero_image)
const dirty = computed(() => !!file.value || overlay.value !== settings.value.hero_overlay)

function pick(f) {
  error.value = ''
  if (!f) return
  if (!f.type.startsWith('image/')) return (error.value = 'Нужна фотография (JPG, PNG, WEBP)')
  if (f.size > 20 * 1024 * 1024) return (error.value = 'Фото больше 20 МБ')
  cancelFile()
  file.value = f
  localUrl.value = URL.createObjectURL(f)
}
function onDrop(e) {
  dragOver.value = false
  pick(e.dataTransfer.files[0])
}
function cancelFile() {
  if (localUrl.value) URL.revokeObjectURL(localUrl.value)
  localUrl.value = ''
  file.value = null
}

async function send(method, body) {
  const res = await fetch(`${apiBase()}/api/admin/settings/hero`, {
    method,
    headers: { Accept: 'application/json', Authorization: 'Bearer ' + getToken() },
    body,
  })
  const json = await res.json().catch(() => ({}))
  if (!res.ok) throw new Error(json.message || 'Не удалось сохранить')
  settings.value = { ...json, loaded: true }
}

async function save() {
  saving.value = true
  error.value = ''
  try {
    const form = new FormData()
    if (file.value) form.append('image', file.value)
    form.append('overlay', overlay.value)
    await send('POST', form)
    cancelFile()
    showToast('Сохранено — фон уже на главной')
  } catch (e) {
    error.value = e.message
  } finally {
    saving.value = false
  }
}

async function remove() {
  if (!window.confirm('Убрать фото с главной? Вернётся синий фон.')) return
  saving.value = true
  try {
    await send('DELETE')
    showToast('Фото убрано')
  } catch (e) {
    error.value = e.message
  } finally {
    saving.value = false
  }
}

function showToast(text) {
  toast.value = text
  setTimeout(() => { toast.value = '' }, 3000)
}

onMounted(() => reload())
onUnmounted(cancelFile)
</script>

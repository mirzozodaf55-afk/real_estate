<template>
  <div>
    <label class="flex flex-col items-center justify-center gap-[6px] min-h-[130px] px-[16px] rounded-[14px] border-2 border-dashed cursor-pointer transition-colors text-center"
      :class="dragOver ? 'border-[#0468ff] bg-[#eef4ff]' : invalid && !pictures.length ? 'border-[#fca5a5] bg-[#fef2f2]/40' : 'border-[#cbd5e1] hover:border-[#0468ff] hover:bg-[#f8fafc]'"
      @dragover.prevent="dragOver = true" @dragleave="dragOver = false" @drop.prevent="onDrop">
      <font-awesome-icon :icon="['fas', 'camera']" class="text-[26px] text-[#0468ff]" />
      <span class="text-[15px] font-semibold text-[#152242]">{{ pictures.length ? 'Добавить ещё фото' : 'Выберите или перетащите фото' }}</span>
      <span class="text-[12.5px] text-[#94a3b8]">JPG, PNG, WEBP · от 800×400 px · до {{ MAX }} фото · свои снимки без водяных знаков</span>
      <input type="file" multiple accept=".jpg,.jpeg,.png,.webp" class="hidden" @change="addFiles($event.target.files); $event.target.value = ''" data-test="photos-input" />
    </label>

    <p v-for="(e, i) of rejected" :key="i" class="wz-err">{{ e }}</p>
    <p v-if="invalid && !pictures.length" class="wz-err">Добавьте хотя бы одно фото — без фото объявление не опубликовать</p>

    <div v-if="pictures.length" class="mt-[12px] grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-[10px]" data-test="photos-grid">
      <div v-for="(img, i) of pictures" :key="img.url" draggable="true"
        @dragstart="dragIndex = i" @dragover.prevent @drop.prevent.stop="dropOn(i)" @dragend="dragIndex = null"
        class="group relative aspect-[4/3] rounded-[12px] overflow-hidden bg-[#0f172a] cursor-grab"
        :class="dragIndex === i ? 'opacity-40' : ''">
        <img :src="img.url" :style="`transform: rotate(${img.rotation}deg)`" class="w-full h-full object-cover transition-transform" alt="" />
        <span v-if="i === 0" class="absolute left-[8px] top-[8px] px-[8px] py-[2px] rounded-full bg-[#0468ff] text-white text-[11.5px] font-bold">Обложка</span>
        <div class="absolute inset-x-0 bottom-0 p-[6px] flex justify-end gap-[6px] bg-gradient-to-t from-black/60 to-transparent">
          <button v-if="i > 0" type="button" @click="makeCover(i)" title="Сделать обложкой" class="w-[30px] h-[30px] rounded-full bg-white/90 hover:bg-white text-[#152242] grid place-items-center text-[12px]">
            <font-awesome-icon :icon="['fas', 'star']" />
          </button>
          <button type="button" @click="rotate(img)" title="Повернуть" class="w-[30px] h-[30px] rounded-full bg-white/90 hover:bg-white text-[#152242] grid place-items-center text-[12px]">
            <font-awesome-icon :icon="['fas', 'rotate-right']" />
          </button>
          <button type="button" @click="remove(i)" title="Удалить" class="w-[30px] h-[30px] rounded-full bg-white/90 hover:bg-white text-[#dc2626] grid place-items-center text-[12px]">
            <font-awesome-icon :icon="['fas', 'trash']" />
          </button>
        </div>
      </div>
    </div>
    <p v-if="pictures.length > 1" class="wz-hint">Перетащите фото, чтобы поменять порядок. Первое — обложка, его видят в поиске.</p>
  </div>
</template>

<script setup>
// Фото объявления. Список общий (getData().images): его отправляют шаг «Фото и цена» и форма коммерческой недвижимости.
// Элемент: { url (превью), file, position, rotation }
defineProps({ invalid: Boolean })

const MAX = 20
const MIN_W = 800
const MIN_H = 400
const { images } = getData()
// вернулись на шаг — показываем уже выбранные фото, а не пустой список
const pictures = ref(Array.isArray(images.value) ? images.value : [])
const rejected = ref([])
const dragOver = ref(false)
const dragIndex = ref(null)

function sync() {
  pictures.value.forEach((p, i) => { p.position = i })
  images.value = pictures.value
}

function addFiles(fileList) {
  rejected.value = []
  const files = [...fileList].filter((f) => /^image\/(jpeg|png|webp)$/.test(f.type))
  if (files.length < fileList.length) rejected.value.push('Часть файлов пропущена: подходят только JPG, PNG и WEBP')
  for (const file of files) {
    if (pictures.value.length >= MAX) {
      rejected.value.push(`Можно загрузить не больше ${MAX} фото`)
      break
    }
    const url = URL.createObjectURL(file)
    const probe = new Image()
    probe.onload = () => {
      if (probe.naturalWidth >= MIN_W && probe.naturalHeight >= MIN_H) {
        pictures.value.push({ url, file, position: pictures.value.length, rotation: 0 })
        sync()
      } else {
        URL.revokeObjectURL(url)
        rejected.value.push(`«${file.name}» слишком маленькое (${probe.naturalWidth}×${probe.naturalHeight}) — нужно от ${MIN_W}×${MIN_H} px`)
      }
    }
    probe.src = url
  }
}

function onDrop(e) {
  dragOver.value = false
  if (e.dataTransfer?.files?.length) addFiles(e.dataTransfer.files)
}

function dropOn(target) {
  const from = dragIndex.value
  dragIndex.value = null
  if (from === null || from === target) return
  const [moved] = pictures.value.splice(from, 1)
  pictures.value.splice(target, 0, moved)
  sync()
}

function makeCover(i) {
  const [moved] = pictures.value.splice(i, 1)
  pictures.value.unshift(moved)
  sync()
}

function rotate(img) {
  img.rotation = (img.rotation + 90) % 360
}

function remove(i) {
  const [removed] = pictures.value.splice(i, 1)
  if (removed?.url?.startsWith('blob:')) URL.revokeObjectURL(removed.url)
  rejected.value = []
  sync()
}
</script>

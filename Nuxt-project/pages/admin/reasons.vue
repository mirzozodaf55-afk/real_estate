<template>
  <AdminShell v-slot="{ user: me }">
    <div v-if="me.role !== 'admin'" class="bg-white rounded-[16px] border border-[#e8edf3] p-[28px] text-center text-[15px] text-[#475569]">
      {{ t('Причины отказа редактирует только администратор.') }}
    </div>
    <template v-else>
      <div class="mb-[14px]">
        <h1 class="text-[24px] font-bold text-[#152242] m-0">{{ t('Причины отказа') }}</h1>
        <p class="text-[14px] text-[#64748b] m-0 mt-[2px]">{{ t('Готовые причины, которые модератор выбирает при отказе. Текст получит владелец в уведомлении и письме.') }}</p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-[1fr_340px] gap-[14px] items-start">
        <div class="bg-white rounded-[16px] border border-[#e8edf3] overflow-hidden">
          <div v-if="loading" class="p-[14px] flex flex-col gap-[8px]">
            <div v-for="n in 3" :key="n" class="h-[64px] rounded-[12px] bg-[#f1f5f9] animate-pulse"></div>
          </div>
          <p v-else-if="error" class="m-0 p-[18px] text-[14px] text-[#dc2626]">{{ t(error) }}</p>
          <p v-else-if="!reasons.length" class="m-0 p-[24px] text-center text-[14px] text-[#64748b]">{{ t('Причин пока нет — добавьте первую справа') }}</p>
          <ul v-else class="list-none m-0 p-0" data-test="reasons">
            <li v-for="r of reasons" :key="r.id" class="p-[14px] border-b border-[#eef2f7] last:border-0" :class="{ 'opacity-60': !r.active }">
              <template v-if="editing === r.id">
                <input v-model="edit.title" maxlength="100" class="w-full h-[38px] px-[10px] rounded-[10px] border border-[#e2e8f0] bg-white text-[14px] font-semibold outline-none focus:border-[#0468ff]" />
                <textarea v-model="edit.text" rows="3" maxlength="500" class="w-full mt-[6px] px-[10px] py-[8px] rounded-[10px] border border-[#e2e8f0] bg-white text-[14px] outline-none focus:border-[#0468ff]"></textarea>
                <div class="mt-[8px] text-[12px] font-semibold text-[#64748b]">English</div>
                <input v-model="edit.title_en" maxlength="100" placeholder="Title" class="w-full mt-[4px] h-[38px] px-[10px] rounded-[10px] border border-[#e2e8f0] bg-white text-[14px] font-semibold outline-none focus:border-[#0468ff]" />
                <textarea v-model="edit.text_en" rows="3" maxlength="500" placeholder="Text for the owner" class="w-full mt-[6px] px-[10px] py-[8px] rounded-[10px] border border-[#e2e8f0] bg-white text-[14px] outline-none focus:border-[#0468ff]"></textarea>
                <div class="flex gap-[8px] mt-[6px]">
                  <button @click="save(r)" :disabled="busy" class="h-[34px] px-[14px] rounded-[9px] bg-[#0468ff] text-white text-[13.5px] font-semibold disabled:opacity-50">{{ t('Сохранить') }}</button>
                  <button @click="editing = null" class="h-[34px] px-[12px] rounded-[9px] text-[13.5px] text-[#64748b] hover:bg-[#f1f5f9]">{{ t('Отмена') }}</button>
                </div>
              </template>
              <template v-else>
                <div class="flex items-start justify-between gap-[10px]">
                  <div class="min-w-0">
                    <div class="text-[15px] font-semibold text-[#152242]">{{ r.title }} <span v-if="!r.active" class="ml-[4px] text-[12px] font-normal text-[#94a3b8]">{{ t('выключена') }}</span></div>
                    <div class="text-[13.5px] text-[#475569] mt-[2px] leading-[1.45]">{{ r.text }}</div>
                    <div v-if="r.title_en || r.text_en" class="mt-[6px] pl-[10px] border-l-2 border-[#e2e8f0] text-[13px] text-[#64748b] leading-[1.45]">
                      <span class="font-semibold text-[#334155]">EN · {{ r.title_en }}</span><br />{{ r.text_en }}
                    </div>
                    <div v-else class="mt-[6px] text-[12px] text-[#b45309]">{{ t('Нет перевода на английский — в английском режиме покажется русский текст') }}</div>
                  </div>
                  <div class="flex items-center gap-[4px] shrink-0">
                    <button @click="startEdit(r)" class="w-[32px] h-[32px] rounded-[8px] text-[#64748b] hover:bg-[#f1f5f9]" :aria-label="t('Изменить')"><font-awesome-icon :icon="['fas', 'pen']" class="text-[12px]" /></button>
                    <button @click="toggle(r)" :disabled="busy" class="w-[32px] h-[32px] rounded-[8px] text-[#64748b] hover:bg-[#f1f5f9]" :aria-label="r.active ? t('Выключить') : t('Включить')" :title="r.active ? t('Выключить — модератор не увидит её в списке') : t('Включить')">
                      <font-awesome-icon :icon="['far', r.active ? 'eye' : 'eye-slash']" class="text-[13px]" />
                    </button>
                    <button @click="remove(r)" :disabled="busy" class="w-[32px] h-[32px] rounded-[8px] text-[#dc2626] hover:bg-[#fef2f2]" :aria-label="t('Удалить')"><font-awesome-icon :icon="['far', 'trash-can']" class="text-[13px]" /></button>
                  </div>
                </div>
              </template>
            </li>
          </ul>
        </div>

        <form @submit.prevent="add" class="bg-white rounded-[16px] border border-[#e8edf3] p-[16px]" data-test="reason-add">
          <div class="text-[16px] font-bold text-[#152242]">{{ t('Новая причина') }}</div>
          <label class="block mt-[10px]">
            <span class="text-[13px] font-semibold text-[#334155]">{{ t('Название — для модератора') }}</span>
            <input v-model="form.title" maxlength="100" :placeholder="t('Например: Мало фото')" class="mt-[4px] w-full h-[38px] px-[10px] rounded-[10px] border border-[#e2e8f0] bg-white text-[14px] outline-none focus:border-[#0468ff]" />
          </label>
          <label class="block mt-[10px]">
            <span class="text-[13px] font-semibold text-[#334155]">{{ t('Текст — получит владелец') }}</span>
            <textarea v-model="form.text" rows="4" maxlength="500" :placeholder="t('Например: Добавьте не меньше трёх фото: комнаты, кухня, санузел.')" class="mt-[4px] w-full px-[10px] py-[8px] rounded-[10px] border border-[#e2e8f0] bg-white text-[14px] outline-none focus:border-[#0468ff]"></textarea>
          </label>
          <div class="mt-[12px] text-[13px] font-semibold text-[#334155]">{{ t('По-английски — необязательно') }}</div>
          <input v-model="form.title_en" maxlength="100" placeholder="E.g.: Too few photos" class="mt-[4px] w-full h-[38px] px-[10px] rounded-[10px] border border-[#e2e8f0] bg-white text-[14px] outline-none focus:border-[#0468ff]" />
          <textarea v-model="form.text_en" rows="3" maxlength="500" placeholder="E.g.: Add at least three photos: rooms, kitchen, bathroom." class="mt-[6px] w-full px-[10px] py-[8px] rounded-[10px] border border-[#e2e8f0] bg-white text-[14px] outline-none focus:border-[#0468ff]"></textarea>
          <p v-if="formError" class="m-0 mt-[6px] text-[13px] text-[#dc2626]">{{ t(formError) }}</p>
          <button type="submit" :disabled="busy || form.title.trim().length < 3 || form.text.trim().length < 5"
            class="mt-[10px] w-full h-[40px] rounded-[10px] bg-[#0468ff] hover:bg-[#0353cc] text-white text-[14px] font-semibold disabled:opacity-50">{{ t('Добавить') }}</button>
          <p class="m-0 mt-[10px] text-[12px] text-[#94a3b8]">{{ t('Вариант «Другое» со своим текстом у модератора есть всегда.') }}</p>
        </form>
      </div>
    </template>
  </AdminShell>
</template>

<script setup>
useHead({ title: () => t('Админка — причины отказа') })

const reasons = ref([])
const loading = ref(true)
const error = ref('')
const busy = ref(false)
const editing = ref(null)
const edit = reactive({ title: '', text: '', title_en: '', text_en: '' })
const form = reactive({ title: '', text: '', title_en: '', text_en: '' })
const formError = ref('')

async function load() {
  loading.value = true
  error.value = ''
  try {
    reasons.value = await adminApi('/rejection-reasons?all=1')
  } catch (e) {
    error.value = 'Не удалось загрузить: ' + e.message
  } finally {
    loading.value = false
  }
}

async function run(fn) {
  busy.value = true
  try {
    await fn()
    await load()
  } catch (e) {
    error.value = e.message
  } finally {
    busy.value = false
  }
}

function startEdit(r) {
  editing.value = r.id
  Object.assign(edit, { title: r.title, text: r.text, title_en: r.title_en || '', text_en: r.text_en || '' })
}
const save = (r) => run(async () => {
  await adminApi(`/rejection-reasons/${r.id}`, { method: 'PUT', body: { title: edit.title, text: edit.text, title_en: edit.title_en || null, text_en: edit.text_en || null } })
  editing.value = null
})
const toggle = (r) => run(() => adminApi(`/rejection-reasons/${r.id}`, { method: 'PUT', body: { active: !r.active } }))
const remove = (r) => confirm(t('Удалить причину «{x}»?', { x: r.title })) && run(() => adminApi(`/rejection-reasons/${r.id}`, { method: 'DELETE' }))

async function add() {
  formError.value = ''
  busy.value = true
  try {
    await adminApi('/rejection-reasons', { method: 'POST', body: { title: form.title, text: form.text, title_en: form.title_en || null, text_en: form.text_en || null } })
    Object.assign(form, { title: '', text: '', title_en: '', text_en: '' })
    await load()
  } catch (e) {
    formError.value = e.message
  } finally {
    busy.value = false
  }
}

onMounted(load)
</script>

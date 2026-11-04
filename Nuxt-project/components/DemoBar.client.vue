<template>
  <!-- Публичное демо: над шапкой — что это демо и вход под любой ролью одной кнопкой (composables/useDemo.js) -->
  <div v-if="isDemo()" class="bg-[#0f172a] text-white text-[13px]" data-test="demo-bar">
    <div class="max-w-[1200px] mx-auto px-[16px] py-[6px] flex flex-wrap items-center gap-x-[12px] gap-y-[6px]">
      <span class="font-semibold bg-[#2563eb] rounded-[6px] px-[8px] py-[1px]">{{ t('Демо') }}</span>
      <span class="text-[#cbd5e1]">{{ t('Данные сбрасываются каждую ночь') }}</span>
      <div class="flex items-center gap-[6px] ml-auto max-w-full overflow-x-auto">
        <span class="text-[#94a3b8] whitespace-nowrap">{{ t('Смотреть как:') }}</span>
        <button v-for="r of demoRoles()" :key="r.role" type="button" :title="t(r.hint)" :disabled="!!busy"
          @click="go(r.role)" :data-test="`demo-as-${r.role}`"
          class="whitespace-nowrap rounded-[6px] px-[10px] py-[3px] transition-colors"
          :class="current === r.role ? 'bg-white text-[#0f172a] font-semibold' : 'bg-white/10 hover:bg-white/20'">
          {{ busy === r.role ? '…' : t(r.label) }}
        </button>
      </div>
      <span v-if="error" class="w-full text-[#fca5a5]">{{ t(error) }}</span>
    </div>
  </div>
</template>

<script setup>
const { user } = getUserByServer(getToken())
// демо-аккаунты — <роль>@demo.domly (laravel: App\Support\Demo::ACCOUNTS)
const current = computed(() => {
  const email = user.value?.email || ''
  return email.endsWith('@demo.domly') ? email.split('@')[0] : null
})
const busy = ref('')
const error = ref('')

async function go(role) {
  if (busy.value) return
  busy.value = role
  error.value = ''
  try {
    await demoLogin(role)
  } catch (e) {
    error.value = e.message
    busy.value = ''
  }
}
</script>

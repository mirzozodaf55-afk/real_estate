<template>
  <AdminShell v-slot="{ user: me }">
    <div v-if="me.role !== 'admin'" class="bg-white rounded-[16px] border border-[#e8edf3] p-[28px] text-center text-[15px] text-[#475569]">
      {{ t('Управлять пользователями может только администратор.') }}
    </div>
    <template v-else>
      <div class="flex flex-wrap items-end justify-between gap-[12px] mb-[14px]">
        <div>
          <h1 class="text-[24px] font-bold text-[#152242] m-0">{{ t('Пользователи') }}</h1>
          <p class="text-[14px] text-[#64748b] m-0 mt-[2px]">{{ t('Роли, блокировка и сообщения пользователям') }}</p>
        </div>
        <div class="flex flex-wrap gap-[8px] w-full sm:w-auto">
          <select v-model="filter" class="h-[40px] px-[10px] rounded-[10px] border border-[#e2e8f0] bg-white text-[14px] outline-none focus:border-[#0468ff]" data-test="users-filter">
            <option value="">{{ t('Все') }}</option>
            <option v-for="r of ROLES" :key="r.value" :value="r.value">{{ r.plural }}</option>
            <option value="blocked">{{ t('Заблокированные') }}</option>
          </select>
          <form @submit.prevent="load(1)" class="relative flex-1 sm:w-[260px]">
            <font-awesome-icon :icon="['fas', 'magnifying-glass']" class="absolute left-[12px] top-1/2 -translate-y-1/2 text-[#94a3b8] text-[13px]" />
            <input v-model="q" type="search" :placeholder="t('Имя, почта или телефон')" data-test="users-search"
              class="w-full h-[40px] pl-[34px] pr-[12px] rounded-[10px] border border-[#e2e8f0] bg-white outline-none focus:border-[#0468ff] text-[14px]" />
          </form>
        </div>
      </div>

      <div class="bg-white rounded-[16px] border border-[#e8edf3] overflow-hidden">
        <div v-if="loading" class="p-[12px] flex flex-col gap-[8px]">
          <div v-for="n in 5" :key="n" class="h-[52px] rounded-[10px] bg-[#f1f5f9] animate-pulse"></div>
        </div>
        <div v-else-if="error" class="p-[24px] text-center text-[14px] text-[#b91c1c]">{{ t(error) }}</div>
        <div v-else-if="!users.length" class="p-[32px] text-center text-[14px] text-[#64748b]">{{ t('Никого не нашли') }}</div>
        <div v-else class="overflow-x-auto">
          <table class="w-full text-[14px] min-w-[820px]">
            <thead>
              <tr class="text-left text-[12px] uppercase tracking-[0.06em] text-[#94a3b8] border-b border-[#eef2f7]">
                <th class="font-bold px-[16px] py-[10px]">{{ t('Пользователь') }}</th>
                <th class="font-bold px-[12px] py-[10px]">{{ t('Объявл.') }}</th>
                <th class="font-bold px-[12px] py-[10px]">{{ t('Был на сайте') }}</th>
                <th class="font-bold px-[12px] py-[10px] w-[170px]">{{ t('Роль') }}</th>
                <th class="font-bold px-[16px] py-[10px] text-right">{{ t('Действия') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="u of users" :key="u.id" class="border-b border-[#f1f5f9] last:border-0" :class="u.blocked_at ? 'bg-[#fef2f2]/60' : ''" :data-test="`user-${u.id}`">
                <td class="px-[16px] py-[10px]">
                  <div class="flex items-center gap-[10px]">
                    <div class="w-[36px] h-[36px] rounded-full bg-[#e2e8f0] bg-cover bg-center shrink-0" :class="u.blocked_at ? 'grayscale opacity-60' : ''"
                      :style="`background-image: url('${apiBase()}/api/avatar/${u.avatar}')`"></div>
                    <div class="min-w-0">
                      <div class="font-semibold text-[#152242] truncate">{{ fullName(u) }}
                        <span v-if="u.id === me.id" class="text-[12px] font-normal text-[#94a3b8]">{{ t('(вы)') }}</span></div>
                      <div class="text-[12.5px] text-[#64748b] truncate">{{ u.email || u.phone_number }}</div>
                      <div v-if="u.blocked_at" class="text-[12px] text-[#dc2626] mt-[1px]" data-test="blocked-label">
                        <font-awesome-icon :icon="['fas', 'ban']" /> {{ t('Заблокирован') }} {{ timeAgo(u.blocked_at) }}<span v-if="u.block_reason">: {{ u.block_reason }}</span>
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-[12px] py-[10px] text-[#334155]">{{ u.announs_count }}</td>
                <td class="px-[12px] py-[10px] text-[#64748b] whitespace-nowrap">{{ u.last_seen_at ? timeAgo(u.last_seen_at) : '—' }}</td>
                <td class="px-[12px] py-[10px]">
                  <select :value="u.role" @change="setRole(u, $event)" :disabled="u.id === me.id || saving === u.id" data-test="role-select"
                    :title="u.id === me.id ? t('Свою роль менять нельзя') : ''"
                    class="w-full h-[36px] px-[8px] rounded-[9px] border text-[13.5px] font-semibold outline-none disabled:opacity-60"
                    :class="roleTone(u.role)">
                    <option v-for="r of ROLES" :key="r.value" :value="r.value">{{ t(r.label) }}</option>
                  </select>
                </td>
                <td class="px-[16px] py-[10px]">
                  <div class="flex justify-end gap-[6px]">
                    <button v-if="u.id !== me.id" @click="writeTo(u, me)" :title="t('Написать сообщение')" data-test="write"
                      class="h-[34px] px-[10px] rounded-[9px] bg-[#eef4ff] text-[#0468ff] hover:bg-[#dbe8ff] text-[13px] font-semibold whitespace-nowrap">
                      <font-awesome-icon :icon="['far', 'comment']" /> {{ t('Написать') }}
                    </button>
                    <NuxtLink :to="{ path: '/admin/chats', query: { user_id: u.id } }" :title="t('Переписки пользователя')"
                      class="h-[34px] w-[34px] rounded-[9px] border border-[#e2e8f0] text-[#475569] hover:border-[#0468ff] hover:text-[#0468ff] grid place-items-center">
                      <font-awesome-icon :icon="['far', 'comments']" />
                    </NuxtLink>
                    <template v-if="u.id !== me.id && u.role !== 'admin'">
                      <button v-if="!u.blocked_at" @click="openBlock(u)" :title="t('Заблокировать')" data-test="block"
                        class="h-[34px] w-[34px] rounded-[9px] border border-[#fecaca] text-[#dc2626] hover:bg-[#fef2f2] grid place-items-center">
                        <font-awesome-icon :icon="['fas', 'ban']" />
                      </button>
                      <button v-else @click="unblock(u)" :disabled="saving === u.id" data-test="unblock"
                        class="h-[34px] px-[10px] rounded-[9px] bg-[#ecfdf5] text-[#047857] hover:bg-[#d1fae5] text-[13px] font-semibold whitespace-nowrap">
                        {{ t('Разблокировать') }}
                      </button>
                    </template>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-if="lastPage > 1" class="flex items-center justify-between px-[16px] py-[10px] border-t border-[#eef2f7] text-[13px]">
          <button :disabled="page <= 1" @click="load(page - 1)" class="px-[10px] h-[30px] rounded-[8px] disabled:opacity-40 hover:bg-[#f1f5f9]">{{ t('← Назад') }}</button>
          <span class="text-[#64748b]">{{ page }} {{ t('из') }} {{ lastPage }} {{ t('· всего') }} {{ total }}</span>
          <button :disabled="page >= lastPage" @click="load(page + 1)" class="px-[10px] h-[30px] rounded-[8px] disabled:opacity-40 hover:bg-[#f1f5f9]">{{ t('Далее →') }}</button>
        </div>
      </div>
    </template>

    <!-- Блокировка -->
    <div v-if="blocking" class="fixed inset-0 z-[500] bg-black/45 flex items-center justify-center p-[16px]" @click.self="blocking = null">
      <form @submit.prevent="block" class="w-full max-w-[460px] bg-white rounded-[16px] p-[20px] shadow-xl" data-test="block-dialog">
        <h3 class="text-[18px] font-bold text-[#152242] m-0">{{ t('Заблокировать') }} {{ fullName(blocking) }}?</h3>
        <ul class="text-[13.5px] text-[#475569] mt-[8px] mb-0 pl-[18px] leading-[1.6]">
          <li>{{ t('его сразу выкинет со всех устройств, войти снова он не сможет;') }}</li>
          <li>{{ t('его объявления пропадут из поиска (вернутся после разблокировки).') }}</li>
        </ul>
        <label class="block text-[14px] font-semibold text-[#152242] mt-[14px]" for="block-reason">{{ t('Причина — её увидит пользователь при входе') }}</label>
        <div class="flex flex-wrap gap-[6px] mt-[8px]">
          <button v-for="r of BLOCK_REASONS" :key="r" type="button" @click="blockReason = r"
            class="px-[10px] h-[30px] rounded-full border text-[12.5px]"
            :class="blockReason === r ? 'border-[#dc2626] bg-[#fef2f2] text-[#b91c1c]' : 'border-[#e2e8f0] text-[#475569] hover:border-[#94a3b8]'">{{ t(r) }}</button>
        </div>
        <textarea id="block-reason" v-model="blockReason" rows="2" maxlength="500" data-test="block-reason"
          class="w-full mt-[8px] p-[10px] rounded-[10px] border border-[#e2e8f0] outline-none focus:border-[#dc2626] text-[14px] resize-y"></textarea>
        <div class="flex justify-end gap-[8px] mt-[14px]">
          <button type="button" @click="blocking = null" class="h-[38px] px-[16px] rounded-[10px] border border-[#e2e8f0] text-[14px] font-semibold text-[#152242]">{{ t('Отмена') }}</button>
          <button type="submit" :disabled="blockReason.trim().length < 3 || saving" data-test="confirm-block"
            class="h-[38px] px-[16px] rounded-[10px] bg-[#dc2626] hover:bg-[#b91c1c] disabled:opacity-50 text-white text-[14px] font-semibold">{{ t('Заблокировать') }}</button>
        </div>
      </form>
    </div>

    <div v-if="toast" class="fixed top-[84px] right-[20px] z-[600] max-w-[calc(100vw-40px)] px-[16px] py-[10px] rounded-[12px] bg-[#152242] text-white text-[14px] shadow-lg pointer-events-none" role="status">
      {{ t(toast) }}
    </div>
  </AdminShell>
</template>

<script setup>
useHead({ title: () => t('Админка — пользователи') })

const ROLES = [
  { value: 'user', label: 'Пользователь', plural: 'Пользователи' },
  { value: 'moderator', label: 'Модератор', plural: 'Модераторы' },
  { value: 'admin', label: 'Администратор', plural: 'Администраторы' },
]
const BLOCK_REASONS = ['Мошенничество', 'Спам и реклама', 'Оскорбления в сообщениях', 'Фейковые объявления']

const router = useRouter()
const users = ref([])
const q = ref('')
const filter = ref('')
const page = ref(1)
const lastPage = ref(1)
const total = ref(0)
const loading = ref(true)
const error = ref('')
const saving = ref(null)
const toast = ref('')
const blocking = ref(null)
const blockReason = ref('')
let toastTimer = null

const fullName = (u) => [u.name, u.last_name].filter(Boolean).join(' ') || u.email || u.phone_number || 'Без имени'

async function load(p = 1) {
  loading.value = true
  error.value = ''
  try {
    const params = new URLSearchParams({ page: p })
    if (q.value.trim()) params.set('q', q.value.trim())
    if (filter.value === 'blocked') params.set('blocked', '1')
    else if (filter.value) params.set('role', filter.value)
    const res = await adminApi(`/users?${params}`)
    users.value = res.data
    page.value = res.current_page
    lastPage.value = res.last_page
    total.value = res.total
  } catch (e) {
    error.value = e.status === 403 ? 'Только для администратора' : 'Не удалось загрузить: ' + e.message
  } finally {
    loading.value = false
  }
}

async function setRole(u, event) {
  const next = event.target.value
  const label = ROLES.find((r) => r.value === next).label
  if (!window.confirm(t('Сделать {name} — {role}?', { name: fullName(u), role: t(label).toLowerCase() }))) {
    event.target.value = u.role // вернуть прежнее значение в списке
    return
  }
  saving.value = u.id
  try {
    await adminApi(`/users/${u.id}/role`, { method: 'POST', body: { role: next } })
    u.role = next
    showToast('Роль изменена')
  } catch (e) {
    event.target.value = u.role
    showToast(e.message)
  } finally {
    saving.value = null
  }
}

// Пишем через обычный чат сайта — у пользователя наш собеседник будет с меткой «Администрация»
function writeTo(u, me) {
  router.push(`/chat-${me.id}-${me.uniq_id}-${u.id}-${u.uniq_id}`)
}

function openBlock(u) {
  blocking.value = u
  blockReason.value = ''
  nextTick(() => document.getElementById('block-reason')?.focus())
}

async function block() {
  const u = blocking.value
  saving.value = u.id
  try {
    const res = await adminApi(`/users/${u.id}/block`, { method: 'POST', body: { reason: blockReason.value.trim() } })
    Object.assign(u, res.data)
    blocking.value = null
    showToast('Пользователь заблокирован')
  } catch (e) {
    showToast(e.message)
  } finally {
    saving.value = null
  }
}

async function unblock(u) {
  if (!window.confirm(t('Разблокировать {name}?', { name: fullName(u) }))) return
  saving.value = u.id
  try {
    await adminApi(`/users/${u.id}/unblock`, { method: 'POST' })
    u.blocked_at = null
    u.block_reason = null
    if (filter.value === 'blocked') users.value = users.value.filter((x) => x.id !== u.id)
    showToast('Пользователь разблокирован')
  } catch (e) {
    showToast(e.message)
  } finally {
    saving.value = null
  }
}

function showToast(text) {
  toast.value = text
  clearTimeout(toastTimer)
  toastTimer = setTimeout(() => { toast.value = '' }, 3000)
}

const roleTone = (r) => ({
  admin: 'border-[#c7d2fe] bg-[#eef2ff] text-[#4338ca]',
  moderator: 'border-[#a5f3fc] bg-[#ecfeff] text-[#0e7490]',
}[r] || 'border-[#e2e8f0] bg-white text-[#334155]')

watch(filter, () => load(1))
onMounted(() => load(1))
onUnmounted(() => clearTimeout(toastTimer))
</script>

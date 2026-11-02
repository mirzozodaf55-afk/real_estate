<template>
    <div>
        <div v-if="loading" class="min-h-screen bg-[#f4f6fb] grid place-items-center text-[#94a3b8]">
            <font-awesome-icon :icon="['fas', 'spinner']" spin-pulse class="text-[28px]" />
        </div>
        <!-- key: при переходе с одного объявления на другое страница собирается заново (карта, галерея) -->
        <ShowAnnoun v-else :key="announ?.id || 'none'" :announ="announ" :error="error" />
    </div>
</template>

<script setup>
// /show_announ-30 — объявление по номеру с сервера.
// Раньше в адрес зашивался весь JSON объявления: «/» в типе («Дом/Дача»), адресе или описании ломал путь → 404.
// Такие старые ссылки (закладки, письма) поддерживаем: берём из них id и грузим свежие данные.
const route = useRoute()
const announ = ref(null)
const loading = ref(true)
const error = ref('')

function idFromParam(param) {
    if (/^\d+$/.test(param)) return Number(param)
    try {
        return Number(JSON.parse(param)?.id) || null
    } catch (e) {
        return null
    }
}

async function load() {
    loading.value = true
    error.value = ''
    announ.value = null
    const id = idFromParam(String(route.params.object || ''))
    if (!id) {
        loading.value = false
        return
    }
    try {
        const res = await fetch(`${apiBase()}/api/announs/${id}`, {
            headers: { Accept: 'application/json', ...(getToken() ? { Authorization: 'Bearer ' + getToken() } : {}) },
        })
        const json = await res.json().catch(() => ({}))
        if (res.ok) announ.value = json.data
        else error.value = json.message || ''
    } catch (e) {
        error.value = 'Нет связи с сервером'
    } finally {
        loading.value = false
    }
}

watch(() => route.params.object, load, { immediate: true })
</script>

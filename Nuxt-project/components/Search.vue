<template>
    <li class="mt-[10px] pt-[10px] border-t border-[#e8edf3]">
        <div class="px-[16px] py-[6px] text-[12px] uppercase tracking-[0.08em] text-[#94a3b8] font-bold">{{ t('Разделы') }}</div>
        <ul class="list-none m-0 p-0">
            <li v-for="c of CATEGORY_LINKS" :key="c.label">
                <button type="button" @click="go(c)" class="w-full flex items-center gap-[14px] px-[16px] py-[11px] text-left text-[15.5px] text-[#334155] active:bg-[#f1f5f9]">
                    <font-awesome-icon :icon="['fas', c.icon]" class="w-[20px] text-[#0468ff]" />
                    {{ t(c.label) }}
                </button>
            </li>
        </ul>
    </li>
</template>
<script setup>
const { active } = useSwitch()
function go(c) {
    active.value = false
    document.body.style.overflow = 'auto'
    navigateTo(categoryRoute(c))
}
</script>

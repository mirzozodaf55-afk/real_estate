<template>
    <ul class="list-none m-0 p-0">
        <li v-for="item of MENU" :key="item.label">
            <button type="button" @click="go(item)" class="w-full flex items-center gap-[14px] px-[16px] py-[12px] text-left text-[16px] font-semibold text-[#152242] active:bg-[#f1f5f9]">
                <font-awesome-icon :icon="item.icon" class="w-[20px] text-[18px] text-[#475569]" />
                {{ item.label }}
            </button>
        </li>
        <li>
            <button type="button" @click="toggleTheme" class="w-full flex items-center gap-[14px] px-[16px] py-[12px] text-left text-[16px] font-semibold text-[#152242] active:bg-[#f1f5f9]">
                <font-awesome-icon :icon="theme === 'dark' ? ['fas', 'sun'] : ['far', 'moon']" class="w-[20px] text-[18px] text-[#475569]" />
                {{ theme === 'dark' ? 'Светлая тема' : 'Тёмная тема' }}
            </button>
        </li>
        <Search />
    </ul>
</template>
<script setup>
// Боковое меню (телефон): раньше все пункты были ссылками-заглушками href="#"
const { showMadoal, active } = useSwitch()
const { direction } = getData()
const { user } = getUserByServer(getToken())
const { theme, toggle: toggleTheme } = useTheme()

const MENU = [
    { label: 'Новый поиск', icon: ['fas', 'magnifying-glass'], to: '/list-announ' },
    { label: 'Разместить объявление', icon: ['fas', 'circle-plus'], to: '/announ', auth: true },
    { label: 'Мои объявления', icon: ['far', 'newspaper'], to: '/personal_area/my_announ', auth: true },
    { label: 'Избранное', icon: ['far', 'heart'], to: '/personal_area/favorite', auth: true },
    { label: 'Сообщения', icon: ['far', 'comment'], to: '/listfriend', auth: true },
    { label: 'Объявления на карте', icon: ['fas', 'map-location-dot'], to: '/yandex-map' },
]

function go(item) {
    // закрываем меню так же, как это делает пункт профиля (Navigation.vue)
    active.value = false
    document.body.style.overflow = 'auto'
    if (item.auth && !user.value?.id) {
        direction.value = item.to
        showMadoal.value = true
        return
    }
    navigateTo(item.to)
}
</script>

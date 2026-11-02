<template>
    <div></div>
</template>

<script setup>
// Старые мобильные фильтры (/filters/buy, /filters/commercial…) заменены списком объявлений с фильтрами —
// старые ссылки и закладки ведём туда же, с подходящим фильтром
const route = useRoute()
const QUERY = {
    buy: { dealType: 'Продажа' },
    take_off: { dealType: 'Аренда' },
    daily: { dealType: 'Посуточно' },
    new_buildings: { dealType: 'Продажа', typeObject: 'Квартира в Новостройке' },
    house: { typeObject: 'Дом/Дача' },
    commercial: { typeRealEstate: 'Коммерческая' },
}
await navigateTo({ path: '/list-announ', query: QUERY[route.params.make] || {} }, { replace: true })
</script>

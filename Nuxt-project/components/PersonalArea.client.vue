<template>
    <div class="min-h-[100vh]">
        <div class="w-[100%] min-h-[100vh] bg-[#f4f6fb]" style="flex:1 1 auto;">
            <div class="flex overflow-hidden flex-col sm:flex-col md:flex-row lg:flex-row">
                <aside class="w-full md:w-[280px] md:shrink-0 bg-white border-b md:border-b-0 md:border-r border-[#e8edf3] md:min-h-[calc(100vh-70px)]">
                    <!-- кто вошёл -->
                    <div class="flex items-center gap-[12px] px-[16px] pt-[16px] pb-[12px] md:py-[20px] md:border-b border-[#eef2f7]">
                        <div class="relative w-[52px] h-[52px] shrink-0 rounded-full p-[2px] bg-gradient-to-br from-[#0468ff] to-[#7c3aed]">
                            <img v-if="user" :src="`${apiBase()}/api/avatar/${user.avatar ?? 'avatar.jpg'}`" class="w-full h-full rounded-full object-cover bg-white" alt="" />
                            <span v-if="load" class="absolute inset-0 grid place-items-center text-white"><font-awesome-icon :icon="['fas', 'rotate']" spin /></span>
                        </div>
                        <div class="min-w-0">
                            <div class="text-[16px] font-bold text-[#152242] truncate">{{ [user?.name, user?.last_name].filter(Boolean).join(' ') || t('Личный кабинет') }}</div>
                            <div class="text-[13px] text-[#64748b] truncate">{{ user?.email }}</div>
                        </div>
                    </div>

                    <!-- разделы: на телефоне — строкой с прокруткой, на компьютере — списком -->
                    <nav ref="nav" class="flex md:flex-col gap-[4px] overflow-x-auto md:overflow-visible px-[12px] pb-[12px] md:py-[12px]" data-test="area-menu">
                        <component :is="item.to ? 'NuxtLink' : 'button'" v-for="item of menu" :key="item.label"
                            :to="item.to" type="button" @click="item.name && go(item.name)" :data-name="item.name"
                            class="area-link relative shrink-0 flex items-center gap-[12px] h-[42px] px-[12px] rounded-[10px] text-[15px] whitespace-nowrap no-underline transition-colors text-left"
                            :class="isActive(item) ? 'area-link--active bg-[#eef4ff] text-[#0468ff] font-semibold' : 'text-[#334155] font-medium hover:bg-[#f1f5f9]'"
                            :aria-current="isActive(item) ? 'page' : undefined">
                            <font-awesome-icon :icon="item.icon" class="w-[18px] text-[16px]" :class="isActive(item) ? 'text-[#0468ff]' : 'text-[#64748b]'" />
                            {{ t(item.label) }}
                        </component>
                        <button type="button" @click="logOut" class="md:hidden shrink-0 flex items-center gap-[10px] h-[42px] px-[12px] rounded-[10px] text-[15px] font-medium text-[#334155] whitespace-nowrap">
                            <font-awesome-icon :icon="['fas', 'arrow-right-from-bracket']" class="text-[#64748b] rotate-180" /> {{ t('Выйти') }}
                        </button>
                        <button type="button" @click="askDelete" class="md:hidden shrink-0 flex items-center gap-[10px] h-[42px] px-[12px] rounded-[10px] text-[15px] font-medium text-[#dc2626] whitespace-nowrap">
                            <font-awesome-icon :icon="['fas', 'user-slash']" /> {{ t('Удалить аккаунт') }}
                        </button>
                    </nav>

                    <div class="hidden md:flex flex-col gap-[4px] px-[12px] pb-[12px] pt-[12px] border-t border-[#eef2f7]">
                        <button type="button" @click="logOut" class="shrink-0 flex items-center gap-[12px] h-[42px] px-[12px] rounded-[10px] text-[15px] font-medium text-[#334155] hover:bg-[#f1f5f9] whitespace-nowrap">
                            <font-awesome-icon :icon="['fas', 'arrow-right-from-bracket']" class="w-[18px] text-[#64748b] rotate-180" /> {{ t('Выйти') }}
                        </button>
                        <button type="button" @click="askDelete" data-test="delete-account" class="shrink-0 flex items-center gap-[12px] h-[42px] px-[12px] rounded-[10px] text-[15px] font-medium text-[#dc2626] hover:bg-[#fef2f2] whitespace-nowrap">
                            <font-awesome-icon :icon="['fas', 'user-slash']" class="w-[18px]" /> {{ t('Удалить аккаунт') }}
                        </button>
                    </div>
                </aside>

                <!-- Profile -->
                <Profile v-if="page == `profile`"/>
                <MyAnnouns v-if="page == `my_announ`"/>
                <!-- <Message v-if="showMessage"/> -->
                <Favorite v-if="page == `favorite`"/>
                <Notification v-if="page == `notification`"/>
                <SavedSearches v-if="page == `store_search`"/>
            </div>
        </div>

        <!-- Подтверждение удаления аккаунта -->
        <div v-if="confirmDelete" class="fixed inset-0 z-[300] bg-black/50 flex items-end sm:items-center justify-center p-0 sm:p-[16px]" @click.self="closeDelete" data-test="delete-modal">
            <div class="w-full sm:w-[440px] bg-white rounded-t-[20px] sm:rounded-[18px] p-[22px] shadow-[0_20px_60px_rgba(15,23,42,0.3)]">
                <div class="w-[52px] h-[52px] rounded-full bg-[#fef2f2] text-[#dc2626] grid place-items-center text-[22px] mx-auto">
                    <font-awesome-icon :icon="['fas', 'user-slash']" />
                </div>
                <h3 class="m-0 mt-[14px] text-[19px] font-bold text-[#152242] text-center">{{ t('Вы действительно хотите удалить этот аккаунт?') }}</h3>
                <ul class="mt-[14px] mb-0 pl-[18px] text-[14px] text-[#475569] leading-[1.6]">
                    <li>{{ t('все ваши объявления, фото и переписки будут удалены без возможности восстановления;') }}</li>
                    <li>{{ t('30 дней на почту') }} <b class="text-[#152242]">{{ id?.email }}</b> {{ t('нельзя будет создать новый аккаунт.') }}</li>
                </ul>
                <p v-if="isAdmin" class="mt-[12px] mb-0 px-[12px] py-[10px] rounded-[12px] bg-[#fffbeb] border border-[#fde68a] text-[13.5px] text-[#b45309]">
                    <font-awesome-icon :icon="['fas', 'shield-halved']" class="mr-[4px]" />
                    {{ t('Вы') }} {{ id.role === 'admin' ? t('администратор') : t('модератор') }}{{ t(': вместе с аккаунтом пропадёт доступ к админке.') }}
                    <template v-if="id.role === 'admin'"> {{ t('Если вы единственный администратор, удалить аккаунт не получится — сначала назначьте другого.') }}</template>
                </p>
                <p v-if="deleteError" class="mt-[12px] mb-0 px-[12px] py-[10px] rounded-[12px] bg-[#fef2f2] border border-[#fecaca] text-[13.5px] text-[#b91c1c]" data-test="delete-error">{{ t(deleteError) }}</p>
                <div class="mt-[18px] flex flex-col-reverse sm:flex-row gap-[10px]">
                    <button type="button" @click="closeDelete" class="flex-1 h-[46px] rounded-[12px] bg-[#f1f5f9] hover:bg-[#e2e8f0] text-[15px] font-semibold text-[#152242]">{{ t('Отмена') }}</button>
                    <button type="button" @click="deleteAcount" :disabled="deleting" data-test="delete-confirm"
                        class="flex-1 h-[46px] rounded-[12px] bg-[#dc2626] hover:bg-[#b91c1c] text-white text-[15px] font-bold disabled:opacity-60">
                        <font-awesome-icon v-if="deleting" :icon="['fas', 'spinner']" spin-pulse class="mr-[6px]" />{{ t('Да, удалить') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>

    const  {user} = getUserByServer()
    // const {$axios} = useNuxtApp()
    const load = ref(false)
    const {avatar,loadImage} = getData()
    watch(loadImage,(value)=>load.value =value)
    const router = useRouter();
    const route = useRoute();
    let id = ref()
    // computed, а не константа: при переходе между вкладками компонент переиспользуется и должен перерисоваться
    const page = computed(() => route.params.make ?? 'profile')
    
    id.value = user.value
    watch(user,(value)=>id.value = value)
    const menu = computed(() => [
        { name: 'profile', label: 'Профиль', icon: ['far', 'user'] },
        { name: 'notification', label: 'Уведомления', icon: ['far', 'bell'] },
        { name: 'favorite', label: 'Избранное', icon: ['far', 'heart'] },
        // переписки — отдельная страница со списком чатов
        { to: '/listfriend', label: 'Сообщения', icon: ['far', 'comment'] },
        { name: 'my_announ', label: 'Мои объявления', icon: ['far', 'newspaper'] },
        { name: 'store_search', label: 'Сохранённые поиски', icon: ['far', 'bookmark'] },
        ...(isStaff(id.value) ? [{ to: '/admin', label: 'Админка', icon: ['fas', 'shield-halved'] }] : []),
    ])
    const isActive = (item) => !!item.name && item.name === page.value
    function go(name) {
        router.push(`/personal_area/${name}`)
    }
    // на телефоне строка разделов прокручивается — показываем активный, а не обрезаем его краем экрана
    const nav = ref(null)
    watch(page, () => nextTick(() => {
        nav.value?.querySelector('[aria-current="page"]')?.scrollIntoView({ block: 'nearest', inline: 'center' })
    }))
    onMounted(() => nextTick(() => nav.value?.querySelector('[aria-current="page"]')?.scrollIntoView({ block: 'nearest', inline: 'center' })))

    
    

    async function logOut(){
        
        await fetch(`${apiBase()}/api/delete_acount`, {
            headers:{
                Authorization: 'Bearer ' + getToken()
            }
        })
        .then(r=>{
            if (r.ok) {
                localStorage.removeItem('token')
            }
            return r.json()})
        .then(res=>{
            user.value = null
        })
    }



// --- Удаление аккаунта: сначала окно подтверждения
const confirmDelete = ref(false)
const deleting = ref(false)
const deleteError = ref('')
const isAdmin = computed(() => ['admin', 'moderator'].includes(id.value?.role))

function askDelete() {
    deleteError.value = ''
    confirmDelete.value = true
}
function closeDelete() {
    if (!deleting.value) confirmDelete.value = false
}

async function deleteAcount() {
    deleting.value = true
    deleteError.value = ''
    try {
        const res = await fetch(`${apiBase()}/api/delete-acount`, {
            method: 'POST',
            headers: { Accept: 'application/json', Authorization: 'Bearer ' + getToken() },
        })
        if (!res.ok) {
            // например, последний администратор — сервер объясняет, что сделать сначала
            deleteError.value = (await res.json().catch(() => ({}))).message || 'Не удалось удалить аккаунт, попробуйте ещё раз'
            return
        }
        localStorage.removeItem('owner')
        localStorage.removeItem('token')
        document.location.replace('/')
    } catch (e) {
        deleteError.value = 'Нет связи с сервером, попробуйте ещё раз'
    } finally {
        deleting.value = false
    }
}
onMounted(()=>id.value = user.value)
</script>

<style scoped>
/* активный раздел: на компьютере — ещё и полоска слева */
@media (min-width: 768px) {
    .area-link--active::before {
        content: '';
        position: absolute;
        left: 0;
        top: 9px;
        bottom: 9px;
        width: 3px;
        border-radius: 3px;
        background: #0468ff;
    }
}
</style>



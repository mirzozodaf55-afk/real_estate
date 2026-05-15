<template>
    <div class="border min-h-[100vh]">
        <div class="w-[100%] min-h-[100vh] bg-[#f4f4f4]" style="flex:1 1 auto;">
            <div class="flex overflow-hidden flex-col sm:flex-col md:flex-row lg:flex-row">
                <aside
                    class="flex flex-col px-[10px] py-[20px] w-full sm:w-[100%] md:w-[300px] lg:w-[300px]  h-[100%] overflow-hidden overflow-y-auto border-r ">
                    <div  class="mb-[8px] border-b px-[10px] pb-[20px]">
                        <div @click="showList" class="flex">
                            <a style="background-image: linear-gradient(180deg, rgb(255, 0, 149),rgb(183, 0, 255)); flex: 0 0 56px; user-select: none; text-decoration: none;" class="mr-[15px] rounded-[50%] flex justify-center items-center h-[56px] overflow-hidden"
                                >
                            <span  class="rounded-[50%]
                              w-[92%] h-[52px] relative">
                                <p v-if="load" class="text-[white] " style="position: absolute; left: 50%; top: 50%; transform: translate(-50%,-50%);">
                                    <font-awesome-icon :icon="['fas', 'rotate']" spin />
                                </p>
                                <img v-if="user" :src="`http://127.0.0.1:8000/api/avatar/${user.avatar??'avatar.jpg'}`" class="w-full h-full rounded-[50%]">
                            </span>
                        </a>
                            <div class="flex flex-col justify-center" style="flex: 1 1;">
                                <a style="text-decoration: none;" class="font-normal cursor-pointer text-[#2b87db] lg:flex md:flex  text-[14px]" >
                                    <div class="text-[#121212] md:mx-[2px] font-bold text-[16px] leading-[1.43]">{{ user.name }}</div>
                                    <div class="text-[#121212] md:mx-[2px] font-bold text-[16px] leading-[1.43]">{{ user.last_name }}</div>
                                </a>
                            </div>
                            
                        </div>
                        
                    </div>
                    <div class="overflow-hidden" :class="!isShowList ? 'h-[100px] d-none':''">
                        <ul  class=" m-[0] border-b px-[10px] menu" style="list-style:none;">
                                    <li @click="selectItem" data-name="profile" class="relateive  rounded-[3px]">
                                        <a  data-name="profile" class="flex text-[#121212] text-[16px] font-bold" style="text-decoration: none; padding: 12px 10px 10px 5px; pointer-events: none;">
                                            <div class="text-[#475258]">
                                                <font-awesome-icon :icon="['far', 'user']" />
                                            </div>
                                            <span class="text-[16px] absolute ml-[30px]">Профиль</span>
                                        </a>
                                    </li>
    
                                    <li @click="selectItem" data-name="notification" class="relateive rounded-[3px]">
                                        <a class="flex text-[#121212] text-[16px] font-bold" style="text-decoration: none; padding: 12px 10px 10px 5px; pointer-events: none;">
                                            <div class="text-[#475258]">
                                                <font-awesome-icon :icon="['far', 'bell']" />
                                            </div>
                                            <span class="text-[16px] absolute ml-[30px]">Уведомления</span>
                                        </a>
                                    </li>
                                    <li @click="selectItem" data-name="favorite" class="relateive rounded-[3px]">
                                        <a class="flex text-[#121212] text-[16px] font-bold" style="text-decoration: none; padding: 12px 10px 10px 5px; pointer-events: none;">
                                            <div class="text-[#475258]">
                                                <font-awesome-icon :icon="['far', 'heart']" />
                                            </div>
                                            <span class="text-[16px] absolute ml-[30px]">Избранное</span>
                                        </a>
                                    </li>
    
                                    <li @click="selectItem" data-name="message" class="relateive rounded-[3px]">
                                        <a class="flex text-[#121212] text-[16px] font-bold" style="text-decoration: none; padding: 12px 10px 10px 5px; pointer-events: none;">
                                            <div class="text-[#475258]">
                                                <font-awesome-icon :icon="['far', 'comment']" />
                                            </div>
                                            <span class="text-[16px] absolute ml-[30px]">Сообщения</span>
                                        </a>
                                    </li>
    
                                    
    
                                    <li @click="selectItem" data-name="my_announ" class="relateive rounded-[3px]">
                                        <a class="flex text-[#121212] text-[16px] font-bold" style="text-decoration: none; padding: 12px 10px 10px 5px; pointer-events: none;">
                                            <div class="text-[#475258]">
                                                <font-awesome-icon :icon="['far', 'newspaper']" />
                                            </div>
                                            <span class="text-[16px] absolute ml-[30px]">Мои объявления</span>
                                        </a>
                                    </li>
    
                                    <li @click="selectItem" data-name="store_search" class="relateive rounded-[3px]">
                                        <a class="flex text-[#121212] text-[16px] font-bold" style="text-decoration: none; padding: 12px 10px 10px 5px; pointer-events: none;">
                                            <div class="text-[#475258]">
                                                <img src="../public/search-favorite-1.svg" class="w-[20px]" style="font-weight: bolder;" alt="">
                                            </div>
                                            <span class="text-[16px] absolute ml-[30px]">Сохранённые поиски</span>
                                        </a>
                                    </li>
    
                                    <li @click="selectItem" data-name="hide_announ" class="relateive rounded-[3px]">
                                        <a class="flex text-[#121212] text-[16px] font-bold" style="text-decoration: none; padding: 12px 10px 10px 5px; pointer-events: none;">
                                            <div class="text-[#475258]">
                                                <font-awesome-icon :icon="['far', 'eye-slash']" />
                                            </div>
                                            <span class="text-[16px] absolute ml-[30px]">Скрытые объявления</span>
                                        </a>
                                    </li>
                        </ul>
                        <ul class="mt-[6px] mb-[20px] ml-[-18px] list-none ">
                            <li @click="logOut" style="padding: 12px 10px 10px 0; text-decoration: none;" class=" text-[14px] flex items-center  relative cursor-pointer rounded-[3px] text-[#121212] font-bold leading-[1.29] ">
                                <div class="text-[18px]  absolute ml-[0]" style="transform: rotate(180deg);">
                                    <font-awesome-icon class="text-[#475258]" :icon="['fas', 'arrow-right-from-bracket']" />
                                </div>
                                <span class="absolute left-[31px]">Выйти</span>
                            </li>
                            <li @click="deleteAcount" style="padding: 12px 10px 10px 0; text-decoration: none;" class="mt-[15px] text-[14px] flex items-center  relative cursor-pointer rounded-[3px] text-[red] font-bold leading-[1.29] ">
                                <div class="text-[18px]  absolute ml-[0]">
                                    <font-awesome-icon class="text-[red]" :icon="['fas', 'user-slash']" />
                                </div>
                                <span class="absolute left-[31px]">Удалить аккаунт</span>
                            </li>
                        </ul>
                    </div>
                </aside>

                <!-- Profile -->
                <Profile v-if="page == `profile`"/>
                <MyAnnouns v-if="page == `my_announ`"/>
                <!-- <Message v-if="showMessage"/> -->
                <Favorite v-if="page == `favorite`"/>
                <Notification v-if="page == `notification`"/>
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
    const page = route.params.make ?? 'profile'
    console.log(page);
    
    id.value = user.value
    watch(user,(value)=>id.value = value)
    function selectItem(event){
        router.push(`/personal_area/${event.target.dataset.name}`)
        useHead({title:event.target.textContent})
    }

    
    
    const isShowList = ref(true)
    if (window.innerWidth < 768) {
        isShowList.value = false
    }
    function showList(){
        if (window.innerWidth < 768) {
            isShowList.value = !isShowList.value
        }
    }

    async function logOut(){
        
        await fetch('http://127.0.0.1:8000/api/delete_acount', {
            headers:{
                Authorization: 'Bearer ' + JSON.parse(localStorage.getItem('token'))
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

setTimeout(() => {
    const list = document.querySelectorAll('.menu li')
    list.forEach(el=>{
        el.classList.remove('active')
        if (el.dataset.name == `${route.params.make}`) {
            el.classList.add('active')
        }
    })
    
});

function deleteAcount(){
    fetch('http://127.0.0.1:8000/api/delete-acount',{
        method:'POST',
        headers:{
            "Content-Type":'application/json',
            Authorization: 'Bearer ' + JSON.parse(localStorage.getItem('token')),
        },
        body:JSON.stringify({
            owner_id:id.id
        })
    }).then(r=>r.json())
    .then(responce=>{
        console.log(responce);
        localStorage.removeItem('owner')
        localStorage.removeItem('token')
        document.location.replace('/')
        
    })
    
}
onMounted(()=>id.value = user.value)
</script>

<style scoped>
.active{
    background-color: rgba(0, 0, 0,.1);
}
</style>



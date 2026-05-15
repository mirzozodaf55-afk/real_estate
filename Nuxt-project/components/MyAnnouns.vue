<template>
    <div class="min-h-[100vh] m-0 sm:m-[10px] md:m-[10px] lg:m-[10px]  bg-[white] flex w-full" >
        <Message v-if="showMessage"/>
        <div class="mt-[20px] m-[10px] flex w-full" style="backdrop-filter: 8px;">
            <ul style="list-style: none;" class="m-[0] p-[0] w-full">
            <h2>Мои объявления</h2>
            <div class="flex w-full flex-wrap sm:justify-start md:sm:justify-start lg:sm:justify-start justify-center">

                <li class="flex-wrap my-[15px] flex 
                 overflow-hidden mx-[10px]
                justify-center rounded-[6px] w-[340px]
                 p-[8px] bg-[white] h-[450px]" v-for="(announ,i) of myAnnoun" :key="i" style="box-shadow:0 0 10px silver;">
                    <div class="flex flex-wrap m-0 p-0 sm:flex-wrap 
                    md:flex-wrap lg:flex-wrap flex-col">
                    <!-- <UpdateAnnoun :announ="announ" class="absolute z-[100] h-[100vh] botom-0 left-0 right-0 bg-[white]" /> -->
                    <div class="h-[250px] relative items-center text-[white] flex overflow-hidden bg-[black] m-[0]">
                        <div class="flex absolute flex-col top-[8px] left-[5px] z-[20]">

                            <span class="w-[max-content] mb-[5px] text-[silver] text-[14px] flex items-center h-[20px]">
                                <div class="text-[16px] text-[red]">
                                    <font-awesome-icon :icon="['fas', 'heart']" />
                                </div>
                                &nbsp;<span class="text-[white]">{{ announ.likes }}</span>
                            </span>
                            <span class="w-[max-content] text-[silver] text-[14px] flex items-center h-[20px]">
                                <div class="text-[15px] text-silver">
                                    <font-awesome-icon :icon="['far', 'eye']" />
                                </div>
                                &nbsp;<span class="text-[white]">0</span>
                            </span>
                            </div>
                        <div :id="`carouselExampleIndicators-${i}`" class="carousel slide" data-bs-ride="carousel" style="overflow: hidden; height: 100%;">
                                    <!-- <div class="carousel-indicators">
                                        <button v-for="(elem, index) in announ.image" :key="`indicator-${i}-${index}`"  
                                            type="button" :data-bs-target="`#carouselExampleIndicators-${i}`" 
                                            :data-bs-slide-to="index" :class="{ 'active': index === 0 }"
                                            :aria-current="index === 0 ? 'true' : false"
                                            :aria-label="`Slide 0`">
                                        </button> 
                                    </div>  -->
                                    <div class="h-full md:w-full">
                                        <div v-for="(elem, index) in announ.image" :key="`slide-${i}-${index}`"
                                        style="height: 100%;" 
                                            :class="{ 'carousel-item': true, 'active': index === 0 }" class="relative">
                                                <div class="flex absolute right-[5px] top-[5px] text-[white] 
                                                bg-[black]/40 px-[5px] z-[30]">
                                                    <button>
                                                        <font-awesome-icon :icon="['fas', 'camera']" />
                                                    </button>
                                                    <div><span>&nbsp;{{ index+1 }}</span> / <span>{{ announ.image.length }}</span></div>
                                                </div>
                                            <img :style="`transform:rotate(${elem.rotation}deg);object-fit:cover; `"
                                            :src="`http://127.0.0.1:8000/api/image/medium/${elem.pictures}`"
                                            class="h-full w-full" >
                                        </div>
                                    </div>
                                    <button class="carousel-control-prev" type="button" :data-bs-target="`#carouselExampleIndicators-${i}`" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span> 
                                        <span class="sr-only">Previous</span>
                                    </button> 
                                    <button class="carousel-control-next" type="button" :data-bs-target="`#carouselExampleIndicators-${i}`" data-bs-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span> 
                                        <span class="sr-only">Next</span> 
                                    </button>
                                </div>
                        
                        <!-- <img v-for="image of announ.image" class="w-full h-full" :src="`http://127.0.0.1:8000/api/image/medium/${image.pictures?image.pictures:'deafault.jpg'}`" 
                         style="object-fit: cover;"
                         :alt="image.pictures"
                        > -->

                    </div>
                    <div class="flex flex-col bg-[white] h-[max-content] mt-[10px]">
                        <h3 class="flex justify-between items-start flex-col w-[100%] text-[16px]">
                            <span class="text-[#0661ec] font-bold">
                                {{ announ.quantity_room+'-комн' }},
                                {{ announ.type_object }}.,
                                {{announ.total_area}}м<sup class="text-[13px]">2</sup>,
                                {{announ.floor}}/{{announ.floor_in_house}}
                                этаж
                                </span>&nbsp;
                        <span class="text-[black] font-bold
                         truncate mr-[5px] text-[19px]"
                        >{{announ.price}} сомон</span>
                        </h3>
                        <div class="w-full max-w-[300px] py-[7px] text-[14px] text-[gray] h-[max-content]">
                            {{announ.description}}
                        </div>
                        <div class="flex items-start
                         bottom-[0] static justify-between
                         flex-col
                         h-[40px] bg-[white] mt-[5px] pr-[8px]">
                            <div class="flex">
                                <div class="flex ">
                                    <button class="rounded-[3px] px-[10px] py-[2px]
                                    text-primary" 
                                    style="border: 1px solid;" 
                                    @click="updateAnnoun(announ)">Изменить</button>
                                </div>
                                <div v-if="announ.deleted_at == null" 
                                class="flex h-[max-content] ml-[10px]">
                                    <button class="rounded-[3px]
                                    px-[10px] text-danger py-[2px] h-[max-content]"
                                    style="border: 1px solid;"
                                    @click="deleteAnnoun(announ)">Удалить</button>
                                </div>
                                <div v-if="announ.deleted_at !== null" class="flex h-[max-content] ml-[10px]">
                                    <button class="rounded-[3px]
                                    px-[10px] text-success h-[max-content]"
                                    style="border: 1px solid;"
                                    @click="restore(announ)">Восстановить</button>
                                </div>
                            </div>
                            <div class="my-[10px] w-[max-content] bg-[pink] ">
                                <span class="text-[14px] font-bold"><span class="font-normal text-[silver]">{{announ.created_at}}</span></span>
                                
                            </div> 
                        </div>        
                    </div>
                </div>
                </li>
            </div>
            </ul>
        </div>
    </div>
</template>

<script setup>
const {updateAnnounData}= getData()
const {showMessage} = useSwitch()
function updateAnnoun(announ){
    navigateTo('/update-announ')
    sessionStorage.setItem('update',JSON.stringify(announ))
}
function deleteAnnoun(announ){
    showMessage.value = true
    document.body.style.overflowY ='hidden'
    sessionStorage.setItem('delete',JSON.stringify(announ))
}

async function restore(announ){
    await fetch('http://127.0.0.1:8000/api/restore',{
        method:'post',
        headers:{
            "Content-Type":'application/json',
            Authorization:'Bearer '+JSON.parse(localStorage.getItem('token'))
            
        },
        body:JSON.stringify({announ:announ})
    }).then(res=>{
        if (res.ok) {
            window.location.replace('/personal_area/my_announ')
        }
        return res.json()
    })
    .then(res=>console.log(res))
}
const myAnnoun = ref()
const {user} = getUserByServer()
let owner = ref();
owner.value =user.value
watch(user,(val)=>owner.value = val)
onMounted(()=>{
    // owner.value = user.value
    fetch('http://127.0.0.1:8000/api/get_announs_by_user',{
        method:'GET',
        headers:{
            "Content-Type":"application/json",
            Authorization:'Bearer '+JSON.parse(localStorage.getItem('token'))
        },
        // body:JSON.stringify({uId:user.value.id})
    })
    .then(res=>res.json())
    .then(res=>{
        myAnnoun.value = res[0]
        for (let i = 0; i < myAnnoun.value.length; i++) {
            const element = myAnnoun.value[i];
                let d = new Date(element.created_at)
                let newDate = new Date()
                const formatted = d.toLocaleString('ru-RU', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                    })
                    element.created_at = formatted
                    let result;
        }
    })
    const date = ref('')
})

</script>
<style scoped>
*{
    font-family:'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;
}
</style>
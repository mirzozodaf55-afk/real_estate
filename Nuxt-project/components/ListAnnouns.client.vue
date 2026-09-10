
<template>
    <div class="flex flex-col w-[100vw] h-[max-contetnt]">
            <div class="z-[3] w-[100%] bg-[white] h-[50px] flex md:flex responce" style="border-bottom:1px solid silver">
                
                <div class="h-full flex items-center justify-center w-[100%] mx-[2px]">
                    <div>
                        <font-awesome-icon class="text-primary mr-[2px]" :icon="['fas', 'filter']" />
                    </div>
                    <button @click="filters" >
                        <span class="font-bold text-[14px] text-primary">Фильтры</span>
                    </button>
                </div>
                <div class="h-full w-[100%] flex items-center justify-center mx-[2px]" style="border-right:1px solid silver;border-left:1px solid silver;">
                    <div>
                        <font-awesome-icon class="text-primary mr-[2px]" :icon="['fas', 'save']" />
                    </div>
                    <button>
                        <span class="font-bold text-[14px] text-primary">Сохранить</span>
                    </button>
                </div>
                <div class="h-full w-[100%] flex items-center justify-center mx-[2px]">
                    <div>
                        <font-awesome-icon class="text-primary mr-[2px]" :icon="['fas', 'location-dot']" />
                    </div>
                    <button>
                        <span class="font-bold text-[14px] text-primary">На карте</span>
                    </button>
                </div>
            </div>
            <div class="min-w-[100%] justify-between md:flex-col lg:flex-row lg:items-center px-[20px] d-none py-[5px] lg:h-[55px] desktop-filter" style="box-shadow:0 0 8px silver;">
                <div class="flex mb-[5px] mt-[5px]">
                <div>
                    <div class="dropdown">
                    <button class="btn w-[max-content] border" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                        {{ filteredData.dealType == 'Продажа'?'Купить':filteredData.dealType == 'Аренда'?'Снять':filteredData.dealType == 'Посуточно'?'Посуточно':'Купить' }}
                    </button>
                    <ul class="dropdown-menu border-0" aria-labelledby="dropdownMenuButton1" style="box-shadow:0 0 10px silver;">
                        <li  v-for="(type,i) of dealType" :key="i" :style="type.value == filteredData.dealType ?'background: rgb(0, 102, 255); color:white':''" @click="selectDealType(type)">
                            <a class="mx-[10px] my-[5px] text-[black]" style="text-decoration: none;" :style="type.value == filteredData.dealType ?'color:white':''" href="#">{{ type.name?type.name:type.value }}</a></li>
                    </ul>
                    </div>
                </div>
                <div class="ml-[10px] ">
                    <div class="dropdown">
                        <button class="btn border truncate" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                            {{  filteredData.typeObject }}
                        </button>
                    <div >

                    <resident class="dropdown-menu absolute border bg-[white] z-[10] truncate" style="padding-left: 10px;padding-right: 10px;"/>
                    <!-- <commercial class="absolute border px-[10px] bg-[white] w-full z-[10]"/> -->

                    </div>
                    </div>
                </div>
                <div class="ml-[10px]" v-if="typeObject == 'Квартира' || typeObject == 'Комната' || typeObject == 'Квартира в новостройке'">
                    <div class="dropdown" >
                        <button class="btn border" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                        <!-- <span v-for="(qtRoom,i) of filteredData.quantityRoom" :key="i">{{ qtRoom }}</span> -->
                        <span class="truncate">
                            {{quantityRoom.join('-')}}{{quantityRoom.length?' комнать':''}}
                        </span>
                        <span v-if="!quantityRoom.length">Комнатность</span>
                    </button>
                    <ul class="dropdown-menu py-[10px] border-0 ml-[20px] p-0 m-[0]" style="box-shadow:0 0 10px silver;">
                    <DesktopQuantityRoom v-if="filteredData.typeObject == 'Квартира'||filteredData.typeObject == 'Комната'||filteredData.typeObject == 'Квартира в новостройке'" />
                    </ul>
                    </div>
                </div>
                <!-- <div v-if="showNotifyMe" class="fixed top-[150px]">Уведомить меня</div> -->
                 <NotifyMe v-if="showNotifyMe"/>
                <div>
                    <div class="dropdown ml-[10px]">
                    <button class="btn border" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                        Ценна
                    </button>
                    <ul class="dropdown-menu border-0" aria-labelledby="dropdownMenuButton1">
                        <DesktopPrice/>
                    </ul>
                    </div>
                </div>
                <div class="flex mx-[10px]">
                    <button @click="isShowHideModal" class="border btn truncate" style="box-shadow:none;">
                        Еще фильтры
                    </button>
                </div>
                </div>
                <div class="flex w-full lg:mt-[0px] md:mt-[5px]">
                    <div class="w-[100%]">
                    <div class="md:w-full flex py-[6px] px-[6px] rounded-[6px] border">
                        <div class="text-[gray]">
                            <font-awesome-icon :icon="['fas', 'magnifying-glass']" />
                        </div>
                        <input @input="searchCIty" v-model="city" class="w-[100%] px-[5px] border-0 outline-0" placeholder="Адрес, город" type="text">
                    </div>
                </div>
                <div class="ml-[10px]">
                    <button @click="Search" class="btn btn-primary">Найти</button>
                </div>
                </div>
            </div>
            
            <div v-if="announLoader" class="w-full h-full items-center fixed top-[30px] bottom-0 flex justify-center" style="z-index:20; background-color:rgba(255, 255, 255, 0.438);">
                <div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
            </div>
            <div v-if="data.length" class="flex items-center h-[50px] mt-[4px] w-full">
                <div class="mx-[20px]">
                    <h2>{{ filteredData.dealType }}{{ filteredData.quantityRooms?filteredData.quantityRooms:'' }} {{ filteredData.typeObject }} {{ filteredData.dealType =='Аренда'?'на длительный срок':'' }}</h2>
                </div>

            </div>
            <div v-if="data.length" class="ml-[170px] flex text-[gray]">
                <p class="p-0 m-[0] mr-[10px]">найденно {{ data.length }} объявления</p>
                <button
            class="py-[2px] px-[6px] h-[25px] text-[15px] rounded items-center bg-[rgba(4,104,255,.1)] flex flex-nowrap"
            @click="goToMap"
          >
            <span>
              <font-awesome-icon
                style="margin-right: 5px"
                class="font-bold text-primary"
                :icon="['far', 'map']"
              />
            </span>
            <span
              style="font-family: Lato, Arial, sans-serif"
              class="text-[rgb(13,110,253)] overflow-hidden text-ellipsis font-normal whitespace-nowrap"
              >На карте</span
            >
          </button>
        </div>
            <div  class="box-border py-[0] ">
                <div class=" flex flex-col w-full px-[20px] items-center">
                    <article  v-for="(item,i) of data"  :key="item.id" class="entered  xl:max-w-[80%] 2xl:max-w-[80%] relative my-[10px] w-full h-[max-content] rounded mx-[0px]" style="box-shadow: 0 0 9px rgb(134, 134, 134);">
                        <div class="flex lg:flex-row md:flex-row md:px-[10px] w-full flex-col  transition-all cursor-pointer rounded-[0]" style="padding:15px 15px;">
                            <NuxtLink class="flex relative bg-[black]/10 h-[350px]">
                                <div :id="`carouselExampleIndicators-${i}`" class="carousel slide" data-bs-ride="carousel" style="overflow: hidden; height: 100%;">
                                    <div class="carousel-indicators">
                                        <button v-for="(elem, index) in data[i].image" :key="`indicator-${i}-${index}`"  
                                            type="button" :data-bs-target="`#carouselExampleIndicators-${i}`" 
                                            :data-bs-slide-to="index" :class="{ 'active': index === 0 }"
                                            :aria-current="index === 0 ? 'true' : false"
                                            :aria-label="`Slide 0`">
                                        </button> 
                                    </div> 
                                    <div class="h-full md:w-[400px]">
                                        <div v-for="(elem, index) in data[i].image" :key="`slide-${i}-${index}`"
                                        style="height: 100%;" 
                                            :class="{ 'carousel-item': true, 'active': index === 0 }">
                                            <div class="flex absolute right-[5px] top-[5px] text-[white] 
                                                bg-[black]/40 px-[5px] z-[30]">
                                                    <button>
                                                        <font-awesome-icon :icon="['fas', 'camera']" />
                                                    </button>
                                                    <div><span>&nbsp;{{ index+1 }}</span> / <span>{{ data[i].image.length }}</span></div>
                                                </div>
                                            <img :style="`transform:rotate(${elem.rotation}deg)`" :src="`http://127.0.0.1:8000/api/image/large/${elem.pictures}`" class="h-full" >
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
                            </NuxtLink>
                            
                            <div class="flex w-[100%] relative sm:flex-col justify-between md:flex-row lg:flex-row">

                                <div @click="showAnnoun(filteredData,item)" class="lg:mx-[10px] flex flex-col  items-start ml-[0px] md:ml-[15px] block w-full ">
                                    <span class=" font-bold leading-[28px] md:text-[22px] text-[#0468ff]">
                                            {{item.title}}.
                                    </span>
                                    <div class="mt-[4xp]">
                                        <span v-if="item.type_object !== 'Участок'" class="block font-bold leading-[28px] text-[16px] tracking-normal m-0 p-0">
                                             {{`${item.quantity_room !== null ? item.quantity_room+'-комн.': item.type_real_estate == "Жилая"?item.quantity_bedrooms+'-мест':item.numberSeats+'-мест'}`}}
                                             {{item.total_area !== null? item.total_area : item.house_area}} <span>М<sup>2</sup></span>,
                                            {{`${item.floor?item.floor + '/' : ''}`}}{{`${item.floor_in_house}`}} этаж
                                        </span>
                                        <span v-if="item.type_object == 'Участок'">
                                            участка: {{item.land_area}} сот.
                                        </span>
                                    </div>
                                    <div class="flex items-center">
                                        <div>
                                            <font-awesome-icon class="text-primary text-[13px] mr-[2px]" :icon="['fas', 'home']" />
                                        </div>
                                    <span class="text-[13px] mt-[4px] ml-[3px]">{{item.type_object}}</span>
                                    </div>
                                    <div class="mt-[10px] ">
                                        <div class="text-[#737a8e] flex">
                                            <div class="text-[13px] text-[#0468ff] mt-[1px] mr-[5px]">
                                                <font-awesome-icon :icon="['fas', 'location-dot']" />
                                            </div>
                                            <a class="relative z-1 text-[#737a8e]" style="text-decoration:none;">
                                                {{item.city}}
                                            </a>
                                        </div>
                                    </div>
                                    <div class="mt-[16px]">
                                        <span class="block font-bold leading-[28px] text-[22px] tracking-normal text-[#152242]">
                                            {{item.price}} cомон
                                        </span>
                                    </div>
                                    <div 
                                    class="text-[gray]"
                                    v-if="item.deal_type == 'Продажа'">{{ Math.floor(item.price / item.total_area) }}см/м<sup>2</sup></div>
                                    <div class="mt-[16px] relative">
                                        <div class="relative h-[60px] overflow-hidden" >
                                            <p class="w-[100%] h-full text-[14px]" style="white-space: pre-wrap;">
                                                {{ item.description }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="absolute right-[0] bottom-0 py-[10px]">
                                        {{ item.create_time }}
                                    </div>
                                </div>
    
                                        
    
                                <div class="w-[200px] rounded-[5px] regular-w border rounded-[20px] h-[max-content] md:d-none mr-[0] h-[100px]" >
                                    <div class="flex flex-col items-center my-[15px] relative">
                                        <div class="overflow-hidden flex justify-center items-center w-[80px] avatar_image shadow rounded-[15%] h-[80px] bg-[red]">
                                            <img style="object-fit: cover;" class="w-full h-full bg-[lime]" :src="`http://127.0.0.1:8000/api/avatar/${item.owner && item.owner.avatar ? item.owner.avatar : 'avatar.jpg'}`" :alt="item.owner && item.owner.name ? item.owner.name : ''">
                                        </div>
                                        <div class="mt-[10px] avatar_image">
                                            <h5 class="text-[17px]">{{ item.owner && item.owner.name ? item.owner.name : '' }} {{ item.owner && item.owner.last_name ? item.owner.last_name : '' }}</h5>
                                        </div>
                                        <div class="w-[90%] flex flex-col items-center justify-around px-[10px] pt-[15px]">
                                            <NuxtLink v-if="user.id !== item.owner.id" type="tel" class="
                                            w-full text-[14px]
                                            py-[2px]
                                            flex
                                            flex-row
                                            rounded-[3px]
                                            bg-primary
                                            justify-center
                                            items-center
                                            text-[white]
                                            my-[5px]
                                            h-[30px]
                                            overflow-hidden
                                            "
                                            style="letter-spacing: 2px;text-decoration: none; text-overflow: ellipsis; white-space: nowrap;"
                                            ><span class="mx-[10px] overflow-hidden" style="text-overflow: ellipsis; white-space: nowrap;">{{user.phone_number && item.owner.phone_number ?"+992"+ item.owner.phone_number : 'показать номер телефон'}}</span></NuxtLink>
                                            <button v-if="user.id !== item.owner.id" @click="goToChat(item)" class="
                                            w-full 
                                            mx-[10px] text-[14px]
                                            justify-center
                                            items-center
                                            flex
                                            h-[30px]
                                            bg-light
                                            py-[2px]
                                            rounded-[3px]
                                            border-0 
                                            text-primary
                                            my-[5px]
                                            "
                                            style="letter-spacing: 2px; text-decoration: none;"
                                            >
                                            <span class="mx-[25%]">Написать</span>
                                            </button>
                                        </div>
    
                                        
                                    </div>
                                </div>
                            </div>
                        <div 
                        class="h-[98%] w-[40px] rounded 
                        right-[5px] top-[4px] opacity-[0] transition-all 
                        absolute flex-col hovered">
                          
                            <button
                            @click.prevent="addLikes(item)"
                            class="h-[50px] mb-[2px] bg-[black]/90 
                            w-full items-center  rounded">
                            
                            <div 
                                class="justify-flex-start">
                                <font-awesome-icon 
                                    class=" text-[22px]"
                                    :class="item.isLike?'text-[red]':'text-[white]'"
                                :icon="[item.isLike?'fas':'far', 'heart']" />
                                </div>
                                <div class="text-[white]">
                                    <p >{{ item['likes-count'] }}</p>
                                </div>
                            </button>
                            <button
                            class="h-[50px] bg-[black]/90 
                            mb-[2px]
                            w-full items-center  rounded">
                            
                            <div 
                            
                                class="justify-flex-start">
                                <font-awesome-icon 
                                    class="text-[white] text-[18px]"
                                :icon="['far', 'eye']" />
                                </div>
                                <div class="text-[white]">
                                    <p >0</p>
                                </div>
                            </button>
                            <button
                            class="h-[40px] bg-[black]/90 
                            
                            w-full items-center  rounded">
                            
                            <div 
                            
                                class="justify-flex-start">
                                <font-awesome-icon 
                                    class="text-[white] text-[18px]"
                                :icon="['fas', 'location-dot']" />
                                </div>
                            </button>
                          
                        </div>
                        </div>
                    </article>
                </div>
            </div>
            <MooreFilter :getAnnouns="getAnnouns" v-if="showFIlterModal"/>
        </div>
</template>
<script setup>
import { Swiper, SwiperSlide } from "swiper/vue"
import { Thumbs } from 'swiper/modules'
import 'swiper/css'
import 'swiper/css/navigation'
import 'swiper/css/pagination'
import 'swiper/css/thumbs'

const data = ref([])
const images = ref([])
const router = useRouter()
const route = useRoute()
const thumbsSwiper = ref(null)
const {responce, showAnnouns, typeObject, quantityRoom, priceFrom, priceTo, typeDeal} = getData()
const {showNavBar, showMadoal, showFIlterModal, showObject} = useSwitch()
let owner = {} 
const {user} = getUserByServer(JSON.parse(localStorage.getItem('token')))
watch(user, (value) => {
    owner = value
})
const announLoader = ref(false)
const city = ref('')
const filteredData = reactive({...route.query})

function showAnnoun(datafilter, item){
    event.stopPropagation()
    router.push(`/show_announ-${JSON.stringify(item)}`)    
}

function goToChat(item){
    if (owner.name && owner.id !== item.owner.id) {
        router.push(`chat-${owner.id}-${item.owner.id}`)
    }
    
}

// function searchCIty(){
//     // filteredData.city = city.value
//     sessionStorage.setItem('filter',JSON.stringify(filteredData))
// }

function filters(){
    window.location.replace('/filters/buy')
    // if (route.fullPath == '/filters/buy') {
    //     showNavBar.value = false
    // }
}
const isLike = ref(false)
async function addLikes(item){
    item.isLike = !item.isLike 
    
    event.preventDefault()
    await fetch('http://127.0.0.1:8000/api/likes',{
        method:'post',
        headers:{
            "Content-Type":'application/json',
            Authorization:'Bearer '+JSON.parse(localStorage.getItem('token'))
        },
        body: JSON.stringify({
            announ_id: item.id,
            user_id: owner.id
        })
    }).then(r=>r.json())
    .then(res=>{
        item.likes = res
        
    })
}
const showNotifyMe = ref(false)
async function goToMap(){

    await fetch("http://127.0.0.1:8000/api/filter", {
    method: "post",
    headers: {
        "Content-type": "application/json",
    },
    body: JSON.stringify(filteredData),
    })
    .then((res) => res.json())
    .then((res) => {
        let uniqueSet = new Set(res.map(JSON.stringify));
        data.value = Array.from(uniqueSet).map(JSON.parse)
        router.push({
            path: '/yandex-map',
            query: route.query
        })
        console.log(filteredData);
        
    });
}
async function getAnnouns(filter){
    showNavBar.value = true
    announLoader.value = true
    // filteredData.user_id = owner.id
    localStorage.setItem('filter', JSON.stringify(filteredData))
    data.value = []
    await fetch('http://127.0.0.1:8000/api/get_announs',{
        method: 'post',
        headers: {
            "Content-Type":'application/json',
            Authorization: 'Bearer ' + JSON.parse(localStorage.getItem('token'))
        },
        body: JSON.stringify(filter)
    })
    .then(res => res.json())
    .then(res => {
      let uniqueSet = new Set(res[0].map(JSON.stringify));
      res[0] = Array.from(uniqueSet).map(JSON.parse)
      let uniqPicture = new Set(res[1].map(JSON.stringify));
      res[1] = Array.from(uniqPicture).map(JSON.parse)
        for (let i = 0; i < res[0].length; i++) {
            if (res[1][i].length > 0) {
                res[0][i]['image'] = res[1][i] 
            }
            let timestamp = res[0][i]['created_at']
            let d = new Date(timestamp)
            let newDate = new Date()
                let result;
                result = d.getDate()
                    res[0][i]['create_time'] = calculateDate(res[0][i]['created_at'])
        }
        data.value = res[0];
        res[0].length === 0 ? showNotifyMe.value = true : showNotifyMe.value = false
        try {
            data.value.forEach(item => {
                window.ymaps.ready(() => {
                window.ymaps.geocode([item['coord_x'],item['coord_y']])
                .then((res) => {
                const firstGeoObject = res.geoObjects.get(0)
    
                const locality = firstGeoObject.getLocalities()[0] || ''
    
                item.city = firstGeoObject.getAddressLine()
                })
                })  
            })
        } catch(e) {
            console.error(e);
            
        }
        
    })

    function calculateDate(created_at){
        const date = new Date(created_at)
        const now = new Date()
        let diffMs = date - now 
        let Minutes = Math.abs(Math.floor(diffMs / 1000 / 60)) 
        let Hours = Math.abs(Math.floor(Minutes / 60)) 
        let Days = Math.abs(Math.floor(Hours / 24))
        let Weeks = Math.abs(Math.floor(Days / 7))
        let Month = Math.abs(Math.floor(Weeks / 4))


        if (Month > 1) {
            return `${Month} месяц. назад`
        }else if (Weeks > 0) {
            return `${Weeks} неделя. назад`
        }else if (Days > 0) {
            return `${Days} дн. назад`
        }else if(Hours > 0){
            return `${Hours} ч. назад`
        }else if(Minutes > 0){
            return `${Minutes} мин. назад`
        }else{
            return `только что`
        }

    }
    let ownerWithAd = []
    for (let i = 0; i < data.value.length; i++) {
        ownerWithAd.push(data.value[i].owner_id);
        
    }
    await fetch('http://127.0.0.1:8000/api/get_user_with_announ',{
        method:'post',
        headers:{
            "Content-Type":'application/json'
        },
        body:JSON.stringify({owner_id: ownerWithAd})
    })
    .then(res => res.json())
    .then(res =>{
        ownerWithAd = res[1]
        
        // announWithUser = res[0]
    })
    
    for (let i = 0; i < data.value.length; i++) {
        for (let j = 0; j < ownerWithAd.length; j++) { 
            if (data.value[i].owner_id == ownerWithAd[j].id) {
                data.value[i].owner = ownerWithAd[j]
            }
        }

    }
    announLoader.value = false

}


const dealType = ref([
    {name: 'Купить', value: 'Продажа'},
    {name: 'Снять', value: 'Аренда'},
    {name: '', value: 'Посуточно'},

])


function selectDealType(item){
    quantityRoom.value = filteredData.quantityRoom.length ? filteredData.quantityRoom :[]
    filteredData.quantityRoom = quantityRoom.value.length ? quantityRoom.value : []
    filteredData.dealType = typeDeal.value = item.value
    if (item.value == 'Посуточно') {
        filteredData.dealType = 'Аренда'
    }
    if (item.value == 'Купить') {
        filteredData.typeObject  = 'Квартира в новостройке'
    }
    if (item.value == 'Снять') {
        filteredData.typeObject = 'Квартира'
    }
    router.push({
        path:`list-announ`,
        query:{...filteredData}
    })
    sessionStorage.setItem('filter',JSON.stringify(filteredData))
}

async function Search(){
    filteredData.typeObject = typeObject.value
    filteredData.quantityRoom = quantityRoom.value
    filteredData.priceFrom = priceFrom.value
    filteredData.priceTo = priceTo.value

    getAnnouns(filteredData)
}
function isShowHideModal(){
    showFIlterModal.value = true
}

// city.value = filteredData.city
 
window.onscroll = function(event){
// setTimeout(()=>{
//     typeDeal.value = JSON.parse(sessionStorage.getItem('filter')).dealType
// },2000)
if (route.fullPath.includes('/list-announ')) {
    // if(event.target.scrollingElement.scrollTop > 50){
    //     const filterEl = document.querySelector('.responce');
    //     filterEl.classList.add('sticky')
    //     filterEl.classList.add('top-[0]')
    // }else{
    //     // filterEl.classList.remove('sticky')
    //     // filterEl.classList.remove('top-[0]')

    // }
}else{
    return
}
}
onMounted(() =>{
    owner = user.value
    getAnnouns(filteredData)
})
    
    
</script>

<style scoped>
.entered:hover .hovered{
    opacity: 1;
}
@media screen and (min-width: 886px) {
    .responce{
        display: none;
    }
    .desktop-filter{
        display: flex !important;
    }
    
}

@media screen and (max-width: 640px) {
    .regular-w{
        width: 100%;
        background: white;
        align-items: center;
    }
}

@media screen and (max-width: 1024px) {
    /* .h-full{
        width: 100%;
    } */

    .avatar_image{
        display: none;
        width: 100%;
    }
    
    .flex .flex-col .items-center{
        margin-top: 0;
    }
    .regular-w{
        display: none;
    }
}

.lds-spinner {
  color: official;
  display: inline-block;
  position: relative;
  width: 80px;
  height: 80px;
}
.lds-spinner div {
  transform-origin: 40px 40px;
  animation: lds-spinner 1.2s linear infinite;
}
.lds-spinner div:after {
  content: " ";
  display: block;
  position: absolute;
  top: 3px;
  left: 37px;
  width: 6px;
  height: 18px;
  border-radius: 20%;
  background: #000;
}
.lds-spinner div:nth-child(1) {
  transform: rotate(0deg);
  animation-delay: -1.1s;
}
.lds-spinner div:nth-child(2) {
  transform: rotate(30deg);
  animation-delay: -1s;
}
.lds-spinner div:nth-child(3) {
  transform: rotate(60deg);
  animation-delay: -0.9s;
}
.lds-spinner div:nth-child(4) {
  transform: rotate(90deg);
  animation-delay: -0.8s;
}
.lds-spinner div:nth-child(5) {
  transform: rotate(120deg);
  animation-delay: -0.7s;
}
.lds-spinner div:nth-child(6) {
  transform: rotate(150deg);
  animation-delay: -0.6s;
}
.lds-spinner div:nth-child(7) {
  transform: rotate(180deg);
  animation-delay: -0.5s;
}
.lds-spinner div:nth-child(8) {
  transform: rotate(210deg);
  animation-delay: -0.4s;
}
.lds-spinner div:nth-child(9) {
  transform: rotate(240deg);
  animation-delay: -0.3s;
}
.lds-spinner div:nth-child(10) {
  transform: rotate(270deg);
  animation-delay: -0.2s;
}
.lds-spinner div:nth-child(11) {
  transform: rotate(300deg);
  animation-delay: -0.1s;
}
.lds-spinner div:nth-child(12) {
  transform: rotate(330deg);
  animation-delay: 0s;
}
@keyframes lds-spinner {
  0% {
    opacity: 1;
  }
  100% {
    opacity: 0;
  }
}

</style>

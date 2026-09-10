<template>
    <div style="flex: 1 1 auto;" class="containers">
        <div class="mobile-container">
            <section class="container" style="margin-top: 16px;">
                <div class="filters">
                    <NuxtLink data-name="buy" class="filter" @click="filter">
                        <div style="pointer-events:none;">
                            <!-- <font-awesome-icon class="fas" :icon="['fas', 'wallet']" /> -->
                            <Icon name="uil:wallet" size="20"/>
                        </div>
                        <span style="pointer-events:none;">Купить</span>
                    </NuxtLink>
                    <NuxtLink data-name="take_off" class="filter" @click="filter">
                        <div style="pointer-events:none;">
                            <font-awesome-icon class="fas" :icon="['fas', 'couch']" />
                            <!-- <Icon name="uil:couch"/> -->

                        </div>
                        <span style="pointer-events:none;">Снять</span>
                    </NuxtLink>
                    
                    <NuxtLink data-name="new_buildings" class="filter" @click="filter">
                        <div style="pointer-events:none;">
                            <!-- <font-awesome-icon  class="fas" :icon="['far', 'building']" /> -->
                            <Icon name="uil:building" size="20"/>
                            
                        </div>
                        
                        <span style="pointer-events:none;">Новостройки</span>
                    </NuxtLink>
                    
                    <NuxtLink data-name="house" class="filter" @click="filter">
                        <div style="pointer-events:none;">
                            <!-- <font-awesome-icon class="fas" :icon="['fas', 'house-chimney']" /> -->
                            <Icon name="uil:home" size="20"/>

                        </div>
                        <span style="pointer-events:none;">Дом</span>
                    </NuxtLink>
                    
                    <NuxtLink data-name="commercial" class="filter" @click="filter">
                        <div style="pointer-events:none;">
                            <font-awesome-icon  class="fas" :icon="['fas', 'chair']" />
                            <!-- <IconCSS name="uil:"/> -->

                        </div>
                        <span style="pointer-events:none;">&nbsp;Коммерческая</span>
                    </NuxtLink>  

                    <NuxtLink data-name="daily" class="filter" @click="filter">
                        <div style="pointer-events:none;">
                            <font-awesome-icon class="fas" :icon="['fas', 'calendar-day']" />
                        </div>
                        <span style="pointer-events:none;">&nbsp;Посуточно</span>
                    </NuxtLink>  
                </div>
                
            </section>
            
            <section class="container " style="margin-top:30px;">
                <div class="announcement">
                    <div>
                        <font-awesome-icon :icon="['fas', 'circle-plus']" />
                        <!-- <span>
                        <Icon  name="uil:plus-circle" size="1.3em"/>
                        </span> -->
                    </div>
                    <div style="display:flex; justify-content:center;align-items:center;">
                        <h3 class="header" style="margin-top:8px;">Разместить Объявление</h3>
                    </div>
                </div>
            </section>
            <!-- <section class="bg-[red] h-[350px]">
                <video ref="video" class="h-[80%] bg-[black]" id="video"></video>
            </section> -->
            <section class="container" style="margin-top:32px; ">
                <span>
                    Могут подойти
                </span>
                <div class="list-addition">
                    <a href="#" v-for="item of announ" :key="item.id" target="_blank" class="addition">
                        <img :src="`http://192.168.0.114:8000/api/image/medium/${item.image}`">
                        <span class="price nowrap">{{item.price}}$</span>
                        <span class="description nowrap">{{item.description}}</span>
                        <span class="description nowrap">{{item.city}}</span>    
                    </a>
                </div>
            </section>

            <section style="margin-top:32px; padding:12px; margin: auto; max-width:640px;">
                <h2 class="header-title">Быстрые ссылки</h2>

                <section style="margin-top:20px;" class="container-block">
                    <div class="image" style="background-image: url('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSy63AUbZNuBUuuRrPOU0A9nPd4BdMTUt3CtA&usqp=CAU');"></div>

                    <div class="content">
                        <h2 class="title" @click="isActive()">
                            <a class="text-dark fw-bold" >Купить квартиру</a>
                            <span :class="!active ? `no-rotate`:'rotate'"></span>

                        </h2>
                        <div class="container-list" :class="!active ? `active`:'no-active'">
                            <ul class="lists">
                                <li class="item">
                                    <a href="#">
                                        1-комнатные
                                    </a>
                                        <span>10&nbsp;440</span>
                                </li>
                                <li class="item">
                                    <a href="#">
                                        2-комнатные
                                    </a>
                                        <span>45&nbsp;017</span>
                                </li>
                                <li class="item">
                                    <a href="#">
                                        3-комнатные
                                    </a>
                                        <span>19&nbsp;121</span>
                                </li>
                                <li class="item">
                                    <a href="#">
                                        Квартиры от застройшика
                                    </a>
                                        <span>61&nbsp;126</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </section>

                <section style="margin-top:20px;" class="container-block">
                    <div class="image" style="background-image: url('https://img.freepik.com/free-photo/3d-rendering-loft-luxury-living-room-with-shelf-near-dining-table-counter_105762-2105.jpg');"></div>

                    <div class="content">
                        <h2 class="title" @click="isActive()">
                            <a class="text-dark fw-bold">Снять квартиру</a>
                            <span :class="!active ? `no-rotate`:'rotate'"></span>

                        </h2>
                        <div class="container-list" :class="!active ? `active`:'no-active'">
                            <ul class="lists">
                                <li class="item">
                                    <a href="#">
                                        1-комнатные
                                    </a>
                                        <span>10&nbsp;440</span>
                                </li>
                                <li class="item">
                                    <a href="#">
                                        2-комнатные
                                    </a>
                                        <span>45&nbsp;017</span>
                                </li>
                                
                                <li class="item">
                                    <a href="#">
                                        Комнаты в Квартире
                                    </a>
                                        <span>61&nbsp;126</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </section>

                <section style="margin-top:20px;" class="container-block">
                    <div class="image" style="background-image: url('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRyKIv92p5xRWZCpssHXEv1ZQdiqJMp8HOu8w&usqp=CAU');"></div>

                    <div class="content">
                        <h2 class="title" @click="isActive()">
                            <a class="text-dark fw-bold">Новостройки</a>
                            <span :class="!active ? `no-rotate`:'rotate'"></span>
                        </h2>
                        <div class="container-list" :class="!active ? `active`:'no-active'">
                            <ul class="lists">
                                <li class="item">
                                    <a href="#">
                                        1-комнатные
                                    </a>
                                        <span>48&nbsp;112</span>
                                </li>
                                <li class="item">
                                    <a href="#">
                                        2-комнатные
                                    </a>
                                        <span>39&nbsp;165</span>
                                </li>
                                
                                <li class="item">
                                    <a href="#">
                                        3-комнатные
                                    </a>
                                        <span>54&nbsp;216</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </section>

                <section style="margin-top:20px;" class="container-block">
                    <div class="image" style="background-image: url('https://arearent.ru/upload/resize_cache/webp/iblock/8e6/1250_600_1/fvu90x1k5pj77o47ok5svp9jnb16d0ed.webp');"></div>

                    <div class="content">
                        <h2 class="title" @click="isActive()">
                            <a class="text-dark fw-bold">Коммерческая Недвижимость</a>
                            <span :class="!active ? `no-rotate`:'rotate'"></span>
                        </h2>
                        <div class="container-list" :class="!active ? `active`:'no-active'">
                            <ul class="lists">
                                <li class="item">
                                    <a href="#">
                                        Арендовать офис
                                    </a>
                                        <span>9&nbsp;051</span>
                                </li>
                                <li class="item">
                                    <a href="#">
                                        Арендовать ПСН
                                    </a>
                                        <span>27&nbsp;518</span>
                                </li>
                                
                                <li class="item">
                                    <a href="#">
                                        Арендовать торговое помещение
                                    </a>
                                        <span>48&nbsp;012</span>
                                </li>
                                <li class="item">
                                    <a href="#">
                                        Купить офис
                                    </a>
                                        <span>31&nbsp;642</span>
                                </li>
                                <li class="item">
                                    <a href="#">
                                        Купить склад
                                    </a>
                                        <span>49&nbsp;003</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </section>

                
                
                <section style="margin-top:20px;" class="container-block">
                    <div class="image" style="background-image: url('https://www.ligakvartir.ru/img/tempimg/min1633268306.jpg');"></div>

                    <div class="content">
                        <h2 class="title" @click="isActive()">
                            <a class="text-dark fw-bold">Снять Посуточно</a>
                            <span :class="!active ? `no-rotate`:'rotate'"></span>
                        </h2>
                        <div class="container-list" :class="!active ? `active`:'no-active'">
                            <ul class="lists">
                                <li class="item">
                                    <a href="#">
                                        Квартиры посуточно
                                    </a>
                                        <span>11&nbsp;974</span>
                                </li>
                                <li class="item">
                                    <a href="#">
                                        Коттеджи на сутки
                                    </a>
                                        <span>67&nbsp;183</span>
                                </li>
                                
                                <li class="item">
                                    <a href="#">
                                        Комнаты на сутки
                                    </a>
                                        <span>70&nbsp;541</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </section>
            </section>
        </div>
    </div>
</template>
<script setup>
    const props = defineProps({
        pictures:{
            type: Array,
            required:true,
            error: Object

        }
    })
    const video = ref(null)

//     const startScreenCapture = async () => {
//   try {
//     // Запрос на захват экрана
//     const stream = await navigator.mediaDevices.getDisplayMedia({
//       video: {
//         mediaSource: 'screen'  // Захват экрана
//       }
//     });

//     // Отображение видео на веб-странице
//     const videoElement = document.querySelector('video');
//     videoElement.srcObject = stream;
//     videoElement.play();

//     // Для остановки потока
//     stream.getTracks().forEach(track => {
//       track.onended = () => {
//         console.log('Screen capture stopped');
//       };
//     });

//   } catch (err) {
//     console.error("Ошибка захвата экрана: ", err);
//   }
// };

// // Запуск функции
// startScreenCapture();

const sStorage = {}

const {showNavBar,isShow} = useSwitch()
const router = useRouter()
function filter(event){
    showNavBar.value = true 
    isShow.value = false 
    if (event.target.children[1].textContent.trim() == 'Купить') {
        sStorage.dealType = 'Продажа'
        sStorage.typeObject = 'Квартира'
        sStorage.buildingType = ['Квартира','Квартира в Новостройке']
        sessionStorage.setItem('filter', JSON.stringify(sStorage))
        router.push('/filters/buy')
    }
    if (event.target.children[1].textContent.trim() == 'Снять') {
        sStorage.dealType =  'Аренда'
        sStorage.typeObject = 'Квартира'
        sStorage.buildingType = []
        sessionStorage.setItem('filter', JSON.stringify(sStorage))
        router.push('/filters/buy')
    }
    if (event.target.children[1].textContent.trim() == 'Новостройки') {
        sStorage.dealType =  'Продажа'
        sStorage.buildingType = []
        sStorage.typeObject = 'Квартира в Новостройке'
        sessionStorage.setItem('filter', JSON.stringify(sStorage))
        router.push('/filters/buy')
    }
    if (event.target.children[1].textContent.trim() == 'Дом') {
        sStorage.dealType =  'Продажа'
        sStorage.buildingType = []
        sStorage.typeObject = 'Дом/дача'
        sessionStorage.setItem('filter', JSON.stringify(sStorage))
        router.push('/filters/buy')
    }
    
    if (event.target.children[1].textContent.trim() == 'Коммерческая') {
        // sStorage.dealType =  'Продажа'
        // sStorage.buildingType = []
        // sStorage.typeObject = 'Дом/дача'
        // sessionStorage.setItem('filter', JSON.stringify(sStorage))
        router.push('/filters/commercial')
    }
    }

useHead({title:'Аренда и Продажа Квартир'})
        const active = ref(true) 
    function isActive(){
        if(!active.value){
            active.value = true

        }else{
            active.value = false
        }
    }
        active.value =true
        const {announ} = getData()
        
            onMounted(()=>{
                sessionStorage.setItem('filter',JSON.stringify(sStorage))
            })
</script>

<style scoped>

@media screen and (min-width:320px) {
    .containers{
        position: relative;
    }
    .container > .announcement{
        display: flex;
        color: #0468ff;
        background-color: rgba(4,104,255,.1);
        left: 0;
        font-size: 100%;
        padding: 5px;
        min-width: 96%;
    }
    .filters{
        min-width: 0;
        width: 100%;
        position: relative;
        max-width: 640px;
    }
    /* .list-addition{
        min-width: 320px;
        width: 96%;
        max-width: 640px;
    }
    .announcement{
        width: 96%;
    }
    .mobile-container > section:last-child{
        width: 96%;
    } */
}
@media screen and (min-width:640px){
    .container > .announcement{
        display: none;
    }
}
.mobile-container{
    margin: 0 auto;
    /* padding: 0 16px; */
    max-width: 640px;
    font-family: Lato,Arial,sans-serif;
}
.container {
    margin: 0 auto;
}
.container > span{
    font-family: Lato,Arial,sans-serif;
    font-size: 22px;
    font-weight: 700;
}
.content{
    margin-top: 15px;
    margin: 10px;
}
.filters{
    display: grid;
    grid-template-columns: 1fr 1fr;
    grid-auto-rows: minmax(60px, auto);
    grid-column-gap: 6px;
    position: relative;
    grid-gap: 10px;
    justify-content: space-between;
    width: 100%;
    min-width: 0;
    max-width: 650px;
}
.filter *{
    margin-left: 4px;
}
.header-title{
    margin: 0;
    color: #152242;
    font-weight: 700;
    font-size: 22px;
    line-height: 28px;
}
.filter .fas{
    color: rgba(32, 84, 198, 0.984);
}
.filter{
    position: relative;
    grid-gap: 8px;
    display: flex;
    flex-direction: row;
    justify-content: flex-start;
    align-items: center;
    border-radius: 7px;
    border: 1px solid silver;
    gap: 8px;
    padding: 4px 8px;
    text-decoration: none;
}
.filter span{
    font-size: 15px;
    font-style:normal;
    letter-spacing: 0;
    line-height: 1.15;
    font-weight: normal;
    color: #000;
    font-family: Lato,Arial,sans-serif;
    font-stretch: normal;
}

.announcement{
    margin-top: 10px;
    display: flex;
    border-radius: 8px;
    
    align-items: center;
    justify-content: center;
    flex-direction: row;
    max-height: 48px;
}
.announcement > *{
    margin-left: 8px;
}

.announcement .header{
    font-size: 1em;
} 

.list-addition{
    grid-gap: 24px 16px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px 16px;
    margin-top: 16px;
    max-width: 650px;
}
.addition{
    grid-gap: 2px;
    display: flex;
    flex-direction: column;
    gap: 2px;
    overflow-x: hidden;
    text-decoration: none;
}
.addition img{
    margin-bottom: 8px;
    border-radius: 4px;
    aspect-ratio: 164/90;
    object-fit: cover;
}
.addition .price{
    font-family: Lato, Arial, sans-serif;
    font-weight: 700;
    color: #152242;
    line-height: 20px;
    font-size: 14px;
    font-style: normal;
    font-stretch: normal;
}
.addition .description{
    color: #152242;
    font-size: 14px;
    line-height: 20px;
    white-space: nowrap;
}
.addition .address{
    font-weight: 400;
    line-height: 20px;
}
.nowrap{
    overflow: hidden;
    text-overflow: ellipsis;
}
.container-block{
    box-shadow: 0 0 10px rgba(0, 0, 0,0.6);
    border-radius: 7px;
    background: #fff;
    overflow: hidden;
}
.image{
    height: 150px;
    background-repeat: no-repeat;
    background-position: 50%;
    background-size: 100%;
    transition: background-size .3s ease;
}
.image:hover{
    background-size: 120%;
}
.title{
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.title, .title > a{
    color: #152242;
    line-height: 28px;
    text-decoration: none;
    margin-left: 5px;
}
.title > a{
    margin-left: 12px;
    font-size: 21px;
    font-family: Lato,Arial,sans-serif;
}

.title span{
    width: 8px;
    height: 8px;
    border: none;
    border-right: 3px solid rgb(0, 128, 255);
    border-bottom: 3px solid rgb(0, 128, 255);
    margin-right: 15px;
    transform: rotate(45deg);
    transform-origin: center;
    transition:  .3s ease;
}
.title .rotate{
    transform: rotate(225deg);
    transform-origin: center;

}
.title .no-rotate{
    transform: rotate(45deg);
    transform-origin: center;

}
.container-list{
    color: rgb(0, 128, 255);
    height: 0;
    overflow: hidden;
    transition: height .3s ease-in-out ;
}
.active{
    height: max-content
}
.no-active{
    height: 0;
}

.lists{
    margin: 0;
    list-style: none;
    display: flex;
    flex-direction: column; 

}
.lists .item{
    position: relative;
    left: -20px;
    width: 100%;
    padding: 5px 0;
    /* background-color: #000; */
    margin: 5px;
    display: flex;
    border-bottom: 1px solid silver;
    justify-content: space-between;
}
.lists .item:last-child{
    border: none;
}
.lists .item > a{
    text-decoration: none;
} 
.lists .item >  span{
    color: silver;
}
@media screen and (min-width: 1024px) {
    .mobile-container{
    margin: 0 auto;
    /* padding: 0 16px; */
    max-width: 640px;
    font-family: Lato,Arial,sans-serif;
}
}
</style>
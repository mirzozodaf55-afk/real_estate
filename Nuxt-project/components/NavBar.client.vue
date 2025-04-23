

<template>
    <div  id="header-frontend" class="containers">
        <div class="wrapper">
            <div class="inner">
                <div class="containers" v-if="!desktopSize">
                    <button class="nav-bars" @click="isActive()" >
                    <font-awesome-icon  :icon="['fas', 'bars']" />
                    </button>
                    
                </div>

                    <h4 @click="Home" class="name-page cursor-pointer" style="text-transform:uppercase;">Avera</h4>
                 
                <div class="container-filters" v-if="desktopSize">
                    <ul class="lists">
                        <li>
                            <a href="#">Аренда</a>
                        </li>
                        <li>
                            <a href="#">Продажа</a>
                        </li>
                        <li>
                            <a href="#">Новостройки</a>
                        </li>
                        <li>
                            <a href="#">Посуточно</a>
                        </li>
                        <li>
                            <a href="#">Коммерческая</a>
                        </li>
                    </ul>
                    <!-- <div class="special-promos">

                    </div> -->
                </div>

            <div class="user-related">

                <div class="containers-icons">
                    <div class="utilities" >
                        <div class="containers ">
                            <button @click="message" class="buttons">
                                <font-awesome-icon style="font-size:18px;" :icon="['far', 'comment']" />
                            </button>
                        </div>
                        <button @click="favorite" class="buttons">
                            <font-awesome-icon style="font-size:18px;" :icon="['far', 'heart']" />
                        </button> 
                        <div class="bell" v-if="desktopSize">
                            <button @click="notification" class="buttons">
                            <font-awesome-icon style="font-size:18px;" :icon="['far', 'bell']" />
                            </button>
                        </div>
                    </div>

                    
                </div>

                <div class="place" v-if="mobileSize" @click="showAndHide">
                    <NuxtLink rel="noopener" href="#">
                        <span >
                            <span >&plus;</span>&nbsp;Разместить oбъявление
                        </span>
                    </NuxtLink>
                </div>
                    <!-- Here will be button login -->
                        <a  rel="noopener" v-if="!data" href="#" class="login" @click="showModalWindow">
                            <span>
                                Войти
                            </span>
                        </a>
                    <div v-if="data" class="user" @click="logOut" :style="`background-image: url('http://127.0.0.1:8000/api/avatar/${data.avatar}');`">
                    </div>
            </div>
            <!-- mobileSize &&  -->
        </div>
    </div>
    <div class="container-menu" v-if="!showModal" :class="!active ? `active`: 'no-active'">
            <div class="wrap" >
                <button class="btn"  @click="isActive()" >
                    <font-awesome-icon class="fawe" style="font-weight:bolder; font-size:1.1em;" :icon="['fas', 'chevron-left']"/>
                </button>
                <Navigation  />
            </div>
            
    </div>
            <div @click="isActive()"  class="solid" :class="active ? 'show': 'hide'"></div>
    </div>
    <!-- <div>{{ user.phone_number }}</div> -->
</template>
<script setup>
    const route = useRoute()
    const router = useRouter()
    const {user,loading} = getUserByServer(JSON.parse(localStorage.getItem('token')))

   
    
    const {isShow, toggleShow, active,showMadoal} = useSwitch();
    const {responce, direction} = getData()
    // localStorage.setItem('owner',JSON.stringify(user.value))
    // responce.value = JSON.parse(localStorage.getItem('owner')) || []
    const data = ref()

    
    watch(user,(val)=>{
        console.log(val);
        data.value = user.value
        localStorage.setItem('owner', JSON.stringify(user.value))
    })

    
    function showAndHide(){
        if (data.value.phone_number) {
         isShow.value = false
        //  window.location.replace('/announ')
        navigateTo('/announ')
         localStorage.removeItem('announ')
     }else{
         showMadoal.value = true
        direction.value = '/announ'
     }
    
}       
 function isActive(){
     if(!active.value){
        active.value = true;
        window.document.body.style.overflow = 'hidden'
        
    }else{
        active.value = false
        window.document.body.style.overflow = 'auto'
    }
    
 }

    const desktopSize = ref(false)
    const showModal = ref(false)
    const mobileSize = ref(false)

function handleSize(){
    if(window.innerWidth > 1024){
        desktopSize.value = true;
        showModal.value = true
        active.value  = false
        window.document.body.style.overflow = 'auto'

    }else{
        desktopSize.value = false
        showModal.value = false
    }

    if (window.innerWidth > 640) 
        mobileSize.value = true
    else mobileSize.value = false

}

onMounted(()=>{
    window.addEventListener('resize', handleSize)
    handleSize()
    isShow.value = false
})

onUpdated(()=>{
    handleSize()
})
onBeforeUnmount(()=>{
    window.removeEventListener('resize', handleSize)
})


function logOut(){
    navigateTo('/personal_area/profile')
    document.body.style.overflow = 'auto'
    isShow.value = false

}

function message(){
    if (data.value.phone_number) {
        navigateTo(('/listfriend'))
        document.body.style.overflow = 'auto'
        isShow.value = false
    }else{
        showMadoal.value = true
        direction.value = '/listfriend'
    }
}
function favorite(){
    if (data.value.phone_number) {
        navigateTo('/personal_area/favorite')
        document.body.style.overflow = 'auto'
        isShow.value = false
    }else{
        showMadoal.value = true
        direction.value = '/personal_area/favorite'
    }
}

function notification(){
    if (data.value.phone_number) {
        navigateTo('/personal_area/notification') 
        document.body.style.overflow = 'auto'
        isShow.value = false
    }else{
        showMadoal.value = true
        direction.value = '/personal_area/notification'
    }
}
function showModalWindow(){
    const {active,showMadoal} = useSwitch()
    document.body.style.overflow ='hidden';
    direction.value = '/personal_area/profile'
    showMadoal.value = true 
}

function Home(){
    window.location.replace('/')
    // navigateTo('/')
}
</script>


<style  scoped>

*{
    box-sizing: border-box;
    
}

@media screen and (min-width: 320px) {
    .container-menu{
    width: 100%;
    position: fixed;
    left: 0;
    background: #fff;
    z-index: 101;
    top: 0;
    visibility: visible;
    overflow-y:auto;
    transition: left .5s ease;
}

 .active{
        left: -110%;
        color: white;
        overflow-y:scroll;
    }
    .no-active{
        left: 0;

    }
    .solid{
        width: 100vw;
        height: 100vh;
        position: fixed;
        top: 0;
        right: 0;
        left: 0;
        bottom: 0;
        z-index: 100;
        background-color: rgba(24,41,48,.8);
        display: flex;
        visibility: hidden;
    }
    .show{
        animation: opacity .5s ease alternate;
    }
    @keyframes opacity {
        0%{
            opacity: 0;
            visibility: hidden;
        }
        100%{
            opacity: 1;
            visibility: visible;
        }
    }
    .hide{
        animation: opacity-zero .5s ease alternate;
    }

    @keyframes opacity-zero {
        0%{
            opacity: 1;
            visibility: visible;
        }
        100%{
            opacity: 0;
            visibility: hidden;

        }
    }

    .place, .login, .user{
        display: none;
    }

    .wrapper{
        position: relative;
        width: 100%;
        max-width: 100vw;
        display: flex;
        justify-content: center;
        height: 50px;
        z-index: 100;
        border-bottom: 1px solid silver;
        background: #fff;
    }
    .inner{
        display: flex;
        margin: 0 auto;
        /* height: 100%; */
        width: 100vw;
        max-width: 1367px;
    }
    .containers-icons{
        position: absolute;
        right: 0;
        top: 0;
    }
    
     .name-page {
        display: flex;
        margin: 0 auto;
        left: 60px;
        z-index: 120;
        align-items: center;
        position: absolute;
    }

   


    .utilities{
    height: 100%;
    display: flex;
    margin-right: 15px;
    }

    .buttons{
        display: inline-block;
        position: relative;
        cursor: pointer;
        border: none;
        background: none;
        padding: 11px;
        color: #737a8c;
        text-decoration: none;
    }
    .bell{
        display: none;
    }
    .container-filters > .lists{
        display: none;
    }
    .btn{
        width: 100%;
    }
}



@media screen and (min-width: 640px){    
    .container-menu{
    max-width: 325px;
    position: fixed;
    overflow-y: auto;
    background: #fff;
    z-index: 101;
    top: 0;
    transition: left .5s ease;
    }
    .active{
        left: -59%;
        color: white;
    }
    .no-active{
        left: 0;
    }

    .solid {
        width: 100vw;
        height: 100vh;
        position: fixed;
        top: 0;
        right: 0;
        left: 0;
        bottom: 0;
        z-index: 100;
        background-color: rgba(24,41,48,.8);
        display: flex;
        visibility: visible;
    }
    .show{
        animation: opacity .5s ease alternate;
    }
    @keyframes opacity {
        0%{
            opacity: 0;
            visibility: hidden;
        }
        100%{
            opacity: 1;
            visibility: visible;
        }
    }
    
    .hide{
        animation: opacity-zero .5s ease alternate;
        visibility: hidden;
    }

     @keyframes opacity-zero {
        0%{
            opacity: 1;
            visibility: visible;
        }
        100%{
            opacity: 0;
            visibility: hidden;

        }
    }
    .place, .login, .user{
        display: flex;
        padding: 0 10px;
    }

    .place > span >span{
        color: white !important;
    }

    .place > a{
        margin-left: 10px;
        text-decoration: none;
        padding: 3px 10px;
        border-radius: 4px;
        color: white;
        background-color: rgb(0, 102, 255);
        font-size: 14px;
        font-weight: 540;
        font-family: Lato,Arial,sans-serif;
    }

    .login{
        background-color: rgba(0, 102, 255, 0.114);
        padding: 3px 10px;
        border-radius: 4px;
        text-decoration: none;
        color: rgb(0, 60, 255);
        font-size: 15px;
        font-weight: 500;
        margin-left: 8px;
    }
    .user{
        max-width: 80px;
        /* min-width: 35px; */
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background-position: center;
        background-size: cover;
        background-repeat: no-repeat;
        outline: none;
        cursor: pointer;
    }
    .wrapper{
        position: relative;
        width: 100%;
        /* max-width: 100vw; */
        display: flex;
        height: 50px;
        z-index: 100;
        border-bottom: 1px solid silver;
        background: #fff;
    }
    .inner{
        display: flex;
        margin: 0 auto;
        padding: 0 20px;
        align-items: center;
        justify-content: space-between;
        position: relative;
        /* width: 100vw; */
        max-width: 1367px;
    }
    
    .containers-icons{
        position: relative;
    }
     .name-page{
        /* background: #00000025; */
        display: flex;
        margin: 0 auto;
        z-index: 120;
        font-size: 24px;
        align-items: center;
        /* top: 25%; */
    }

    .utilities{
    position: relative;
    display: flex;
    margin-right: 0;
    }

    .buttons{
        display: inline-block;
        position: relative;
        cursor: pointer;
        border: none;
        background: none;
        padding: 11px;
        color: #737a8c;
        text-decoration: none;
    }
    .bell{
        display: none;
    }
    .user-related{
        display: flex;
        position: relative;
        align-items: center;
        flex-shrink: 0;
    }
    .container-filters > .lists{
        display: none;
    }
    .btn{
        min-width: 320px;
        max-width: max-content;
    }
}

@media screen and (min-width:1024px) {
    .humburgers, .nav-bars{
        display: none;
    }
    .no-active{
        left: -59%;
    }
    .solid{
        display: none;
    }

    .wrapper{
        position: relative;
        height: 70px;
        z-index: auto;
        background-color: #fff;
        border-bottom: 1px solid silver;
        /* border-bottom: 1px solid silver;     */
    }
    .inner {
        display: flex;
        margin: 0 auto;
        padding: 0 20px;

        max-width: 1367px;
    }

    .container-links{
        width: 110px;
        height: 67px;

    }

     .name-page{
        z-index: 120;
        color: rgb(0, 102, 255);
        text-transform: uppercase;
        font-size: 30px;
        height: 100%;
        top: 0;
        left: 15px;
        margin:0 auto;
        font-weight: 700;
        font-family: Lato,Arial,sans-serif;
        letter-spacing: 3px;
    }
    .user-related{
        display: flex;
        position: relative;
        align-items: center;
        /* flex-shrink: 0; */
        height: 100%;
    }
    .containers-icons{
        position: relative;
        width: max-content;
    }
    .containers-icons > .utilities{
        padding: 10px;
        left: 0;
        justify-content: center;
    }
    .bell{
        display: block;
    }
    .container-filters {
        position: relative;
        right: -4%;
        margin-left: 40px;
        margin-right: 10px;
        height: 100%;
        display: flex;
        width: max-content;    
    }
    .container-filters  .lists{
        display: flex;
        width: 130%;
        list-style: none;
        flex-shrink: 0;
        /* justify-content: space-evenly; */
        margin: auto;
        margin-left: 10px;
    }
    .container-filters > .lists > li{
        margin-left: 20px;
        font-size: 14px;
        font-weight:500;
    }
    .container-filters > .lists > li >a{
        text-decoration: none;
        color: black;
        font-family: Lato,Arial,sans-serif;
    }

    .container-filters > .special-promos{
        position: relative;
        right: 0;
        height: 100%;
        white-space: nowrap;
        padding: 0 8px;
        display: flex;
        flex: 1 1;
        overflow: hidden;
        text-overflow: ellipsis;
        align-items: center;
        background-color: #000;
        /* background-color: #ffffff83; */
        /* width: 105px; */
    }

    .container-filters > .special-promos::before{
        display: block;
        position: absolute;
        width: 20px;
        top: 0;
        right: 0;
        height: 100%;
        content: "";
        pointer-events: none;
    }


}

.wrap{
    display: flex;
    position: relative;
    flex-direction: column;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    cursor: auto;
    background: #fff;
    z-index: 125;
    height: 100vh;
}
.btn{
    display: flex;
    align-items: center;
    height: 50px;
    cursor: pointer;
    position: fixed;
    border: none;
    background: rgba(255, 255, 255, 0.53);
    font-size: 1.2em;
    z-index: 200;
    /* background: transparent; */
    color: #182930;
    /* color: red; */
    }
.btn > .fawe{
    margin-left: 10px;
}
    
/* .container-menu::before{
    background: #000;
    display: block;
    position: absolute;
    top: 0;
    width: 100vw;
    left: 100%;
    right: 0;
    bottom: 0;
    height: 100vh;
    opacity: .5;
    content: '';
    transition: all .4s ease-in-out;
} */

    .containers{
      position: relative;
    }
    .flex{
        display: flex;
    }
    
    
    .buttons{
        display: inline-block;
        position: relative;
        cursor: pointer;
        border: none;
        background: none;
        padding: 11px;
        color: #737a8c;
        text-decoration: none;
    }


    .humburgers{
        display: flex;
        align-items: center;
        margin-left: 10px;
    }

    .nav-bars{
        cursor: pointer;
        margin-right: 12px;
        margin-left: -10px;
        border: none;
        padding: 8px;
        font-size: 1.3em;
        color: #737a8c;
        background: none;
    }
    .wrapper{
        position: relative;
        width: 100%;
        max-width: 100vw;
        display: flex;
        justify-content: center;
        /* height: 70px; */
        z-index: 100;
        border-bottom: 1px solid silver;
        background: #fff;
    }
    .inner{
        display: flex;
        margin: 0 auto;
        flex-wrap: wrap;
        padding: 0 20px;
        align-items: center;
        max-width: 1367px;

    } 
</style>
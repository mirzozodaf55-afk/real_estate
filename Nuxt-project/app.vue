б<template>
  <NuxtLayout>
    <NavBar v-if="showNavBar"/>
  </NuxtLayout>
    <ClientOnly>
      <NuxtPage />
      <Register v-if="showMadoal" />
      <MobilePage v-if="isShow && mobileOrDesktop" :pictures="pictures" />
      <DesktopPage v-if="isShow && mobileOrDesktop < 1" />
    </ClientOnly>
</template>

<script setup>
// import auth from './middleware/auth'



useHead({
  script:[
    {
      src:"https://api-maps.yandex.ru/2.1/?apikey=44f8dc38-ee61-4091-9259-3978f6bcf5bf&lang=ru_RU",
      type:'text/javascript'
    }
    
  ]
})

// 


  // const isClient = !import.meta.env.SSR;
  // console.log(isClient);
  
  const {isShow, toggleShow, showMadoal,showNavBar} = useSwitch()
  const {responce} = getData()
  const route = useRoute()
  const mobileOrDesktop = ref()
  const pictures = ref([]);
  
  // watch(() => route.path, (newPath) => {
    // isShow.value = newPath === '/'
    // })
    // isShow.value = route.path === '/'
    watch(
  () => route.path,
  (newPath) => {
    isShow.value = newPath === '/'
  },
  { immediate: true }
)



    onMounted(async () =>{

      // const {Notification} = window
// let permission = Notification.permission
//     let result = await Notification.requestPermission();
//     permission = result;
//     if (permission === 'granted') {
//         new Notification('Привет! 👋', {
//             body: 'Это тестовое уведомление в Nuxt 3',
//             // icon: `http://127.0.0.1:8000/api/avatar/${owner.avatar}`
//         });
//     } else {
//         alert('Разрешение не получено!');
//     }

      mobileOrDesktop.value =  navigator.maxTouchPoints 
     setTimeout(()=>{
     isShow.value = route.path === '/'
       
     },200)

     setInterval(async () => {
      await fetch('http://127.0.0.1:8000/api/get_user', {
      method: 'GET',
      headers: {
        Authorization: 'Bearer ' + JSON.parse(localStorage.getItem('token')), 
      }
    })
     },5000)
      
      // if (navigator.maxTouchPoints < 1 && route.fullPath == '/announ') {
      //   isShow.value = false
      // }
    const announId = ref([])
    const {announ} = getData()
    
    //   if (!JSON.parse(localStorage.getItem('token'))) {
    //     fetch('http://127.0.0.1:8000/api/get_data')
    // .then(res=>res.json())
    // .then(res=>{
    //   for (const item of res[0]) {
    //     announId.value.push(item.id);
    //     // responce
        
    //   }
    //   })
    //   }

  
    const infrastructure = ["автомойка",
     "автосервис",
     "аптека",
     "ателье-одежды",
     "банкомат",
     "бассейн",
     "автомойка",
     "буфет",
     "выставочно-складской комплекс",
     "гостиница",
     "кафе",
     "кинотеатр",
     "конференц-зал",
     "медицинский-центр",
     "минимаркет",
     "нотариальная контора",
     "отделение банка",
     "парк",
     "ресторан",
     "салон красоты",
     "складские помещения",
     "столовой",
     "супермаркет",
     "торговая зона",
     "фитнес-центр",
     "фотосалон",
     "центральная рецепция",
     ];
     const cityData = [
      "Душанбе",
      "Худжанд",
      "Абдурахмони Джоми",
      "Айни",
      "Ашт",
      "Бальджувон",
      "Бободжон Гафуров",
      "Бохтар (Курган-Тюбе)",
      "Бустон (Чкаловск)",
      "Вандж",
      "Варзоб",
      "Вахдат",
      "Вахш",
      "Восе",
      "Гиссар",
      "Горная Матча",
      "Гулистон",
      "Дангара",
      "Дарваз",
      "Деваштич (Ганчи)",
      "Джаббор Расулов",
      "Джайхун (Кумсангир)",
      "Джалолиддини Балхи (Руми)",
      "Джами",
      "Дусти",
      "Зафарабад",
      "Истаравшан",
      "Исфара",
      "Ишкашим",
      "Кубодиён",
      "Канибадам",
      "Куляб",
      "Кушониён",
      
     ]
  
      await fetch('http://127.0.0.1:8000/api/create/infrastructure',{
        method:'post',
        headers:{
          "Content-type":"application/json"
        },
        body: JSON.stringify(infrastructure)
      })
      
      await fetch('http://127.0.0.1:8000/api/create/city', {
        method:'post',
        headers:{
          "Content-type": "application/json"
        },
        body: JSON.stringify(cityData)
      })
    })
</script>
<style>


* {
  font-family:'Trebuchet MS', 'Lucida Sans Unicode', 'Lucida Grande', 'Lucida Sans', 'Arial, sans-serif';

}
body{
  overflow-x: hidden;
}
@media screen and (max-width:320px) {
  * {
    font-size:17px;
  }
}
</style>


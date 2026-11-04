<template>
  <DemoBar />
  <NuxtLayout>
    <NavBar v-if="showNavBar"/>
  </NuxtLayout>
    <ClientOnly>
      <NuxtPage />
      <Register v-if="showMadoal" />
      <MobilePage v-if="isShow && mobileOrDesktop" :pictures="pictures" />
      <DesktopPage v-if="isShow && mobileOrDesktop < 1" />
      <AiAssistant />
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
//             // icon: `${apiBase()}/api/avatar/${owner.avatar}`
//         });
//     } else {
//         alert('Разрешение не получено!');
//     }

      mobileOrDesktop.value =  navigator.maxTouchPoints 
     setTimeout(()=>{
     isShow.value = route.path === '/'
       
     },200)

     // «в сети» для чата: сервер отмечает last_seen_at — раз в минуту и только пока вкладка открыта перед глазами
     const pingOnline = () => {
      if (!getToken() || document.hidden) return
      fetch(`${apiBase()}/api/get_user`, { headers: { Authorization: 'Bearer ' + getToken() } }).catch(() => {})
     }
     setInterval(pingOnline, 60000)
     document.addEventListener('visibilitychange', pingOnline)
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


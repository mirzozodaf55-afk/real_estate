<template>
  <button @click="goBack" class="absolute z-[1000] py-[2px] px-[5px] bg-primary cursor-pointer my-[3px] text-[white]
  rounded border font-bold mt-0 top-[55px]
  border-white ml-[4px] text-[20px] mb-[3px]">
<p class="m-0 pb-[3px]">назад</p>
</button>
  <div class="flex justify-center" style="height: 96vh;">
    <div
      id="map-yandex"
      class="border-5 w-[100vw]"
      style="height: 96vh; box-shadow: 0 0 6px silver"
    ></div>
  </div>
</template>

<script setup>

import { onMounted, defineProps } from 'vue'
let coords = []
let announ = []
const filteredData = JSON.parse(sessionStorage.getItem('filter'))
let arr = []
const router = useRouter()
function goBack(){
  router.back()
}
async function getAnnouns(filter){
    data.value = []
    await fetch('http://127.0.0.1:8000/api/get_announs',{
        method:'post',
        headers:{
            "Content-Type":'application/json'
        },
        body:JSON.stringify(filter)
    })
    .then(res=>res.json())
    .then(res=>{
      let uniqueSet = new Set(res[0].map(JSON.stringify));
      res[0] = Array.from(uniqueSet).map(JSON.parse)
      let uniqPicture = new Set(res[1].map(JSON.stringify));
      res[1]= Array.from(uniqPicture).map(JSON.parse)
        for (let i = 0; i < res[0].length; i++) {
            if (res[1][i].length > 0) {
                res[0][i]['image'] = res[1][i] 
            }
        }
        data.value = res[0];
        console.log(res);
        coords=res[2]
        announ=res[0]
        for (let i = 0; i < announ.length; i++) {
          if (coords[i][0] == res[0][i]['coord_x']&&coords[i][1] == res[0][i]['coord_y']) {
            arr.push(res[0][i])
          }
        }
        
    })
    let ownerWithAd = []
    for (let i = 0; i < data.value.length; i++) {
        ownerWithAd.push(data.value[i].owner_id);
        
    }
    await fetch('http://127.0.0.1:8000/api/get_user_with_announ',{
        method:'post',
        headers:{
            "Content-Type":'application/json'
        },
        body:JSON.stringify({owner_id:ownerWithAd})
    })
    .then(res=>res.json())
    .then(res=>{
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
}




// Пропсы: список городов и цветов
const hash ='0123456789abcdefgh'
console.log(arr);

const props = defineProps({
  cities: {
    type: Array,
    default: () => [
      { name: 'Душанбе', color: 'red' },
      { name: 'Вахдат', color: 'blue' },
      { name: 'Гиссар', color: 'green' },
      { name: 'Вашгирд', color: 'pink' },
      { name: 'Варзоб', color: '' },
    ]
  },
  center: {
    type: Array,
    default: () => [38.559772, 68.787038] // Центр Таджикистана
  },
  zoom: {
    type: Number,
    default: 9
  }
})

// Показываем навбар (если используешь)
const { showNavBar } = useSwitch()
showNavBar.value = true


onMounted(() => {
  
  getAnnouns(filteredData)
  const checkYMaps = setInterval(() => {
  if (window.ymaps) {
    clearInterval(checkYMaps)

    window.ymaps.ready(() => {
      const myMap = new window.ymaps.Map('map-yandex', {
        center: coords[0] || [0, 0], // Центрируем на первой координате, если есть
        zoom: 10,
        controls: ['zoomControl', 'fullscreenControl'],
        type:'yandex#map'
      })

      coords.forEach((coord, index) => {
        const announ = arr[index]
        const placemark = new window.ymaps.Placemark(coord, {
          hintContent: `
          <div id="announs" class='w-[200px] mb-1 text-primary h-[150px] flex flex-col truncate items-center'>
            <strong>${announ.title||'no title'}</strong>
            ${announ.image.length?
            `<img class='w-full h-full' src="http://127.0.0.1:8000/api/image/large/${announ.image[Math.floor(Math.random()*announ.image.length)].pictures}" width="100">`
            :''}
            <p>${announ.deal_type=='Аренда'?announ.deal_type+'плата:':'ценна:'} ${announ.price} сомони</p>
          </div>
          `,
          balloonContent: `Координаты: ${coord.join(', ')}`
        })

        myMap.geoObjects.add(placemark)
      })
    })
    
  }
}, 1500)
console.log(document.querySelectorAll('#announs'));


})


</script>
<style scoped>
#map-yandex::after {
  content: "";
  position: absolute;
  top: 50px; 
  /* bottom: 0; */
  left: 0;
  width: 100%; 
  height: 100%;
  background-color: rgba(0, 0, 0, 0.1); /* затемнение */
  pointer-events: none;
  z-index: 1000;
}</style>



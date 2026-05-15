<template>
  <div class="mt-[40px] px-[8px]" :class="!title?'mt-0':''">
        <div class="flex items-center">
          <div
            style="font-family: Lato, Arial, sans-serif"
            class="font-bold
            leading-[24px]
            text-[16px] text-[#152242]
            tracking-[normal]"
          >
           {{ title }}
          </div>
        </div>
        <div class="mt-[10px] mb-[10px]">
          <component v-if="title == 'Площадь'" :is='tag' class="mb-[-10px] text-[13px]">Жилая</component>
          <component v-if="!title" :is='tag' class="mb-[-10px] text-[13px]">Кухня</component>
          <div class="flex mt-[4px] mb-[-4px]">
            <div class="w-[100%] justify-between flex flex-nowrap">
              <div
                class="flex border h-[40px] w-[49%] rounded-[4px] px-[8px] overflow-hidden"
              >
                <input
                  v-model="price.from"
                  @input="writeFloor"
                  class="outline-0 w-[100%] h-full"
                  :placeholder="placeholder1"
                  type="number"
                />
                <component v-if="title !== 'Этаж' || title !== 'Год постройки' || title !== '' " :is="tag" class="w-[20px] mt-[5px] h-[100%]">{{ innerText }}<component v-if="title == 'Площадь'|| title == 'Общая площадь'|| title == 'Площадь дома' || title == ''" :is="sup" class="text-[10px]">{{ textSup }}</component>
                </component>

              </div>
              <div
                class="flex border h-[40px] w-[49%] rounded-[4px] px-[8px] overflow-hidden"
                
                >
                <input
                  v-model="price.to"
                  @keyup="writeFloor"
                  class="outline-0 w-[100%]"
                  :placeholder="placeholder2"
                  type="number"
                />
                <component v-if="title !== 'Этаж' || title !== 'Год постройки'" :is="tag" class="w-[20px] mt-[5px] h-[100%]">{{ innerText }}<component v-if="title == 'Площадь'|| title == 'Общая площадь'|| title == 'Площадь дома' || title == ''" :is="sup" class="text-[10px]">{{ textSup }}</component>
                </component>
              </div>
            </div>
          </div>
        </div>
      </div>
</template>
<script setup>
const price = ref({
from:'',
to:''
})
const p = defineProps({
title:{
  type:String,
  required:true
},
placeholder1:{
  type:String,
  required:true
},

placeholder2:{
  type:String,
  required:true
},
tag:{
  type:String,
  required:true
},
innerText:{
  type:String,
  required:true
},
sup:{
  type:String,
  required:true
},
textSup:{
  type:String,
  required:true
},
enterPrice:{
  type:Function,
  required:true
},
updateTotalArea:{
  type:Function,
  required:true
},
updateArea:{
  type:Function,
  required:true
},
updateKitchenArea:{
  type:Function,
  required:true
},
updateFloor:{
  type:Function,
  required:true
},
updateYear:{
  type:Function,
  required:true
},
updateLoader:{
  type:Function,
  required:true
},
updateData:{
  type:Function,
  required:true
},
updateLandArea:{
  type:Function,
  required:true
},
error: Object

})

let timeout = null
function writeFloor() {
    if(timeout){
      clearTimeout(timeout)
    }
    if (price.value.from < 0) {
    price.value.from = 0
    }
    if (price.value.to < 0) {
      price.value.to = 0
    }

    timeout = setTimeout(async () => {
    if (p.title == 'Цена') {
      p.enterPrice(price.value.from,price.value.to);
    }
    
    if (p.title == 'Общая площадь') {
      p.updateTotalArea(price.value.from, price.value.to)
    }

    if (p.title == 'Площадь дома') {
      p.updateTotalArea(price.value.from, price.value.to)
    }

    if (p.title == 'Площадь') {
      p.updateArea(price.value.from,price.value.to)
    }
  
    if (!p.title) {
      p.updateKitchenArea(price.value.from,price.value.to)
    }
  
    if (p.title == 'Этаж') {
      p.updateFloor(price.value.from,price.value.to)
    }
    if (p.title == 'Год постройки') {
      p.updateYear(price.value.from,price.value.to)
    }
    if (p.title == 'Площадь участка') {
      p.updateLandArea(price.value.from, price.value.to)
    }
  const filter = JSON.parse(sessionStorage.getItem('filter'))
    p.updateLoader(true)
   await fetch("http://127.0.0.1:8000/api/filter", {
    method: "post",
    headers: {
      "Content-type": "application/json",
    },
    body: JSON.stringify(filter),
  })
    .then((res) => res.json())
    .then((res) => {
      p.updateData(res)
      console.log(res);
    });
    p.updateLoader(false)
}, 1000);
}

let filter = JSON.parse(sessionStorage.getItem('filter')) || []
  onMounted(() => {
    if (p.title == 'Цена') {
      price.value.from = filter.priceFrom
      price.value.to = filter.priceTo
    }
    if (p.title == 'Общая площадь') {
      price.value.from = filter.totalAreaFrom
      price.value.to = filter.totalAreaTo
    }
    if (p.title == 'Этаж') {
      price.value.from = filter.floorFrom
      price.value.to = filter.FloorTo
    }
    if (p.title == 'Год постройки') {
      price.value.from = filter.yearFrom
      price.value.to = filter.yearTo
    }
  })
</script>
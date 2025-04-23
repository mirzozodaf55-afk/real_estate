<template>
<div>
<!-- <div class="d-none"><component :is='tag'></component>{{title}}</div> -->
    <input id="place" @input="write" v-model="obj.from" v-maska data-maska="####" type="text" class="border w-[50px] outline-0 px-[5px] ml-[3px] py-[3px] text-[15px]" placeholder="от" style="border-top-left-radius: 5px; border-bottom-left-radius: 5px;">
    <input id="place" @input="write" v-model="obj.to" v-maska data-maska="####" type="text" class="border w-[50px] outline-0 px-[5px]  py-[3px] text-[15px]" placeholder="до" style="border-top-right-radius: 5px; border-bottom-right-radius: 5px;">
</div>
</template>

<script setup>
const obj ={
    from:'',
    to:''

}

const p = defineProps({
title:{
  type:String,
  required:true
},
tag:{
  type:String,
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
updateFloorHouse:{
  type:Function,
  required:true
},
updateYear:{
  type:Function,
  required:true
},

updateLandArea:{
  type:Function,
  required:true
},
})

function write() {
    if (obj.from) {
        obj.from = parseInt(obj.from)
    }
    if (obj.to) {
        obj.to = parseInt(obj.to)
    }
    if (p.title == 'totalArea') {
        p.updateTotalArea(obj.from,obj.to)
    }
    if (p.title == 'liveArea') {
        p.updateArea(obj.from,obj.to)
    }
    if (p.title == 'kitchenArea') {
        p.updateKitchenArea(obj.from,obj.to)
    }
    if (p.title == 'floor') {
        p.updateFloor(obj.from,obj.to)
    }

    if (p.title == 'floorHouse') {
        p.updateFloorHouse(obj.from,obj.to)
    }
    // if (p.title == 'area') {
    //     p.updateArea(obj.from,obj.to)
    // }
    if (p.title == 'year') {
        p.updateYear(obj.from,obj.to)
    }

    if (p.title == 'landArea') {
        p.updateLandArea(obj.from,obj.to)
    }
    

}
const filter = JSON.parse(sessionStorage.getItem('filter'))||{}

onMounted(()=>{
    if (p.title == 'totalArea') {
        obj.from = filter.totalAreaFrom
        obj.to = filter.totalAreaTo
    }
    if (p.title == 'liveArea') {
        obj.from = filter.areaFrom 
        obj.to = filter.areaTo
    }
    if (p.title == 'kitchenArea') {
        obj.from = filter.kitchenAreaFrom 
        obj.to = filter.kitchenAreaTo
    }
    if (p.title == 'floor') {
        obj.from = filter.floorFrom 
        obj.to = filter.floorTo
    }

    if (p.title == 'floorHouse') {
        obj.from = filter.floorHouseFrom 
        obj.to = filter.floorHouseTo
    }
    if (p.title == 'area') {
        obj.from = filter.areaFrom 
        obj.to = filter.areaTo
    }

    if (p.title == 'landArea') {
        obj.from = filter.landAreaFrom 
        obj.to = filter.landAreaTo
    }

    if (p.title == 'year') {
        obj.from = filter.yearFrom 
        obj.to = filter.yearTo
    }
})
</script>

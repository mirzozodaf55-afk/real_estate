<template>
    <div class="w-full h-[100vh] z-[200] bg-[black]/40 fixed flex justify-center top-[0] modals">
        <div class="w-[900px] flex flex-col overflow-hidden rounded-[4px] h-[500px] my-[auto] relative bg-[white]" >
            <div class="w-full h-[75px] border-bottom justify-between flex items-center">
                <h3 class="text-[23px] font-bold ml-[50px]">Еще фильтры</h3>
                <button @click="isHideModal" class="text-[25px] font-normal absolute right-[10px] text-[gray] cursor-pointer top-[0px] ">
                    <font-awesome-icon :icon="['fas', 'xmark']" />
                </button>
            </div>
            <div class="w-full overflow-auto top-[75px] h-[350px]" >
                <div class="h-[auto] flex flex-col px-[20px] py-[20px]">
                    <div class="flex w-full" v-if="filter.typeObject=='Квартира в новостройке'||filter.typeObject=='Квартира'||filter.typeObject=='Комната'" >
                        <div class="w-[100px] flex items-center justify-end">
                            <span class="text-[14px]">Площадь м<sup class="text-[10px]">2</sup></span>
                        </div>

                        <div class="flex ml-[30px] items-center">
                            <span class="text-[13px] mr-[5px]">Общая</span>
                            <DesktopInput 
                            tag="span" 
                            title="totalArea" 
                            :updateTotalArea="updateTotalArea" />
                        </div>
                        <div class="flex ml-[15px] items-center">
                            <span class="text-[13px] mr-[5px]">Кухня</span>
                            <DesktopInput 
                            tag="span" 
                            title="kitchenArea" 
                            :updateKitchenArea="updateKitchenArea"/>
                        </div>
                        <div class="flex ml-[15px] items-center">
                            <span class="text-[13px] mr-[5px]">Жилая</span>
                            <DesktopInput 
                            tag="span" 
                            title="liveArea" 
                            :updateArea="updateArea"/>
                        </div>
                    </div>
                    <div class="flex w-full mt-[15px]">
                        <div class="w-[100px] flex items-center justify-end">
                            <span class="text-[14px]">Этаж</span>
                        </div>

                        <div class="flex ml-[30px] items-center">
                            <DesktopInput tag="span" title="floor" :updateFloor="updateFloor"/>
                        </div>
                    </div>
                    <div class="flex w-full mt-[15px]" v-if="filter.typeObject=='Квартира в Новостройке'||filter.typeObject=='Квартира'||filter.typeObject=='Комната'">
                        <div class="w-[100px] flex items-center justify-end">
                            <span class="text-[14px] truncate">Этажей в доме</span>
                        </div>

                        <div class="flex ml-[30px] items-center">
                            <DesktopInput tag="span" title="floorHouse" :updateFloorHouse="updateFloorHouse"/>
                        </div>
                    </div>

                    <div class="flex w-full mt-[15px]" v-if="filter.typeObject=='Дом/Дача'||filter.typeObject=='Коттедж'||filter.typeObject=='Участок'" >
                        <div class="w-[100px] flex items-center justify-end">
                            <span class="text-[14px]">Площадь</span>
                        </div>

                        <div class="flex ml-[30px] items-center">
                            <span class="text-[13px] mr-[5px]">Дом</span>
                            <DesktopInput tag="span" title="area" :updateArea="updateArea"/>
                            <span class="text-[13px] ml-[5px]">м<sup>2</sup></span>
                        </div>
                        <div class="flex ml-[15px] items-center">
                            <span class="text-[13px] mr-[5px]">Участок</span>
                            <DesktopInput tag="span" title="landArea" :updateLandArea="updateLandArea"/>
                            <span class="text-[13px] ml-[5px]">сот.</span>
                        </div>
                    </div>

                    <div class="flex w-full mt-[15px]">
                        <div class="w-[100px] flex items-center justify-end">
                            <span class="text-[14px]">Ремонт</span>
                        </div>

                        <div class="flex ml-[30px] items-center">
                            <div v-for="repair of repairs" :key="repair.value" class="repair">
                                <input name="repair" @change="chooseRepair(repair)" type="checkbox" class="d-none" :id='repair.value'>
                                <label class="border-1 text-[14px] py-[5px] px-[3px] mx-[3px] cursor-pointer rounded-[3px] label" :for="repair.value">{{repair.name}}</label>
                            </div>
                        </div>
                    </div>
                    <div class="flex w-full mt-[15px]">
                        <div class="w-[100px] flex items-center justify-end">
                            <span class="text-[14px]">Тип дома</span>
                        </div>

                        <div class="flex ml-[30px] items-center">
                            <div v-for="home of typeHome" :key="home.name" class="typeHome">
                                <input name="typeHome" @change="chooseRepair(home)" type="checkbox" class="d-none" :id='home.name'>
                                <label class="border-1 text-[14px] py-[5px] px-[3px] 
                                mx-[3px] cursor-pointer rounded-[3px] label" 
                                :for="home.name">{{home.name}}</label>
                            </div>
                        </div>

                    </div>
                    <div class="flex w-full mt-[15px]">
                        <div class="w-[100px] flex items-center justify-end">
                            <span class="text-[14px]">Парковка</span>
                        </div>

                        <div class="flex ml-[30px] items-center">
                            <div v-for="p of parking" :key="p.name" class="parking">
                                <input name="parking" @change="chooseRepair(p)" type="checkbox" class="d-none" :id='p.name'>
                                <label class="border-1 text-[14px] py-[5px] px-[3px] 
                                mx-[3px] cursor-pointer rounded-[3px] label" 
                                :for="p.name">{{p.name}}</label>
                            </div>
                        </div>

                    </div>
                    
                    <div v-if="filter.dealType=='Аренда'" class="flex w-full mt-[15px]">
                        <div class="ml-[40px] flex items-center justify-end">
                            <span class="text-[14px]">Удобства</span>
                        </div>

                        <div class="flex items-center">
                            <div class="flex ml-[30px] flex-wrap w-[80%] justify-start">
                                <div v-for="c of condition" :key="c.name" class="mt-[3px] condition">
                                    <input name="condition" @change="chooseRepair(c)" type="checkbox" class="d-none" :id='c.id'>
                                    <label class="border-1 text-[14px] py-[5px] px-[3px] 
                                    mx-[3px] cursor-pointer rounded-[3px] label" 
                                    :for="c.id">{{c.name}}</label>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="flex w-full mt-[15px]">
                        <div class="w-[100px] flex items-center justify-end">
                            <span class="text-[14px]">Год постройки</span>
                        </div>

                        <div class="flex ml-[30px] items-center">
                            <DesktopInput tag="span" title="year" :updateYear="updateYear"/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-content-end w-full h-[75px] border-top">
                <div class="w-[350px] z-[100] px-[10px] flex items-center">
                    <button @click="clearAll"  class="text-[14px] font-bold p-[5px] px-[15px] border-[black] mr-[5px] text-primary rounded-[6px]" style="background-color: #e8e9ec;">Сбросить фильтры</button>
                    <button @click="showObjects" id="showObject" class="text-[14px] font-bold p-[5px] px-[15px] border-[black] ml-[5px] text-[white] bg-primary  rounded-[6px]">Показать объекты</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
const filter = JSON.parse(sessionStorage.getItem('filter'))||{}
const props = defineProps({
    getAnnouns:{
        type:Function,
        required:true
    }
})
function updateTotalArea(value1,value2){
    filter.totalAreaFrom = value1
    filter.totalAreaTo = value2
    sessionStorage.setItem('filter',JSON.stringify(filter))
}

function updateArea(value1,value2){
    filter.areaFrom = value1
    filter.areaTo = value2
    sessionStorage.setItem('filter',JSON.stringify(filter))
}

function updateKitchenArea(value1,value2){
    filter.kitchenAreaFrom = value1
    filter.kitchenAreaTo = value2
    sessionStorage.setItem('filter',JSON.stringify(filter))
}

function updateFloor(value1,value2){
    filter.floorFrom = value1
    filter.floorTo = value2
    sessionStorage.setItem('filter',JSON.stringify(filter))
}
function updateFloorHouse(value1,value2){
    filter.floorHouseFrom = value1
    filter.floorHouseTo = value2
    sessionStorage.setItem('filter',JSON.stringify(filter))
}
function updateYear(value1,value2){
    filter.yearFrom = value1
    filter.yearTo = value2
    sessionStorage.setItem('filter',JSON.stringify(filter))
}


function updateLandArea(value1,value2){
    filter.landAreaFrom = value1
    filter.landAreaTo = value2
    sessionStorage.setItem('filter',JSON.stringify(filter))
}
const repairs = ref([
    {name:'Без ремонта',value:'Без ремонта'},
    {name:'Косметический',value:'Косметический'},
    {name:'Дизайнерский',value:'Дизайнерский'},
    {name:'Евроремонт',value:'Евро'},
    {name:'Обичный',value:'Обичный'},

])

const typeHome= ref([
    {name:'Кирпичный'},
    {name:'Деревянный'},
    {name:'Панельный'},
    {name:'Блочный'},
    {name:'Монолитный'},
    {name:'Монолитно-кирпичный'},
    {name:'Сталинский'},

])

const parking = ref([
    {name:'Наземная'},
    {name:'Многоуровневая'},
    {name:'Подземная'},
    {name:'На крыше'},
])

const c = fetch('http://127.0.0.1:8000/api/conditions')
const cond = await c
const condition = ref(await cond.json()) 
condition.value = condition.value.filter(item=>item.name !=='Можно с детьми'&&item.name !=='Можно с животными')
console.log();
const {showFIlterModal,showObject} = useSwitch()
function showObjects(event){
    showFIlterModal.value = false
    props.getAnnouns(filter)
}

function isHideModal(){
showFIlterModal.value = false
}

function clearAll(){
    const inputs = document.querySelectorAll('#place')
    inputs.forEach(input=>{
        input.value = ''
    })
}


let repairHome = []
let parkingHome = []
let typehouse = []
let conditions = []
function chooseRepair(item){
    if (event.target.checked) {
    if (event.target.name == 'repair') {
        repairHome.push(item.value)
        filter.repair = repairHome
    }
    if (event.target.name == 'parking') {
        parkingHome.push(item.name)
        filter.parking = parkingHome 
    }
    if (event.target.name == 'condition') {
        conditions.push(item.id)
        filter.condition = conditions
    }

    if (event.target.name == 'typeHome') {
        typehouse.push(item.name)
        filter.typeHome = typehouse
    }
    }


    if (!event.target.checked) {
        if (event.target.name == 'repair') {
            repairHome.splice(repairHome.indexOf(item.value),1)
            filter.repair = repairHome
        }

        if (event.target.name == 'parking') {
            parkingHome.splice(parkingHome.indexOf(item.name),1)
            filter.parking = parkingHome
        }
        if (event.target.name == 'typeHome') {
            typehouse.splice(typehouse.indexOf(item.name),1)
            filter.typeHome = typehouse
        }
        if (event.target.name == 'condition') {
            conditions.splice(conditions.indexOf(item.id),1)
            filter.condition = conditions
        }
        
    }
    sessionStorage.setItem('filter',JSON.stringify(filter))
}

function checkStyle(selector, repair){
    setTimeout(()=>{
        const rep = document.querySelectorAll(selector)
        repair.forEach(el=>{
                rep.forEach(r=>{
                    if (r.id === el) {
                        r.checked = true
                    }
                })
        })
    })
}

    repairHome = filter.repair || []
    parkingHome = filter.parking || []
    typehouse = filter.typeHome || []
    conditions = filter.condition || []

    checkStyle('.repair > input', repairHome)
    checkStyle('.typeHome > input', typehouse)
    checkStyle('.parking > input', parkingHome)
    // checkStyle('.condition > input', conditions)

    setTimeout(()=>{
        const condsEl = document.querySelectorAll('.condition > input')
        conditions.forEach(el=>{
                condsEl.forEach(r=>{
                    if (r.id == el) {
                        r.checked = true
                    }
                })
        })
    },100)
</script>

<style scoped>
input:checked+.label{
    border: 1px solid black;
    background: #00000018;
}
</style>
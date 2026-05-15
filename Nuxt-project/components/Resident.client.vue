<template>
    <div>
        <div style="margin:0; box-sizing:border-box;">
            <div style="margin-top:10px" >
                <ul class="form-check p-[0]"  >
                    <li style="list-style:none; padding:4px 0; margin:0; " >
                        <label v-if="filter.dealType == 'Продажа'" for="check1" class="my-[5px] block form-check-label">
                            <input :checked="filter.typeObject == 'Квартира в новостройке'" @change="selectTypeResident" class="form-check-input" id="check1" name="radio" type="checkbox" style="box-shadow:none;">
                            <span style="font-size:15px; font-weight:normal;">Квартира в новостройке</span>
                        </label>
                        <label v-if="filter.dealType == 'Продажа'" for="check0" class="my-[5px] block form-check-label">
                            <input :checked="filter.typeObject == 'Квартира'" @change="selectTypeResident" class="form-check-input" id="check0" name="radio" type="checkbox" style="box-shadow:none;">
                            <span style="font-size:15px; font-weight:normal;">Квартира</span>
                        </label>
                        <label v-if="filter.dealType == 'Аренда'" for="check2" class="my-[5px] block">
                            <input :checked="filter.typeObject == 'Квартира'" @change="selectTypeResident" id="check2" class="form-check-input" name="radio" type="radio" style="box-shadow:none;">
                            <span style="font-size:15px; font-weight:normal;">Квартира</span>
                        </label>
                    </li>
                </ul>
            </div>
            <div>
                <ul class="form-check p-[0]" >
                    <li style="list-style:none; padding:4px 0">
                        <label for="check3">
                            <input :checked="filter.typeObject =='Комната'" @change="selectTypeResident" 
                            id="check3" class="form-check-input" name="radio" type="checkbox" style="box-shadow:none;">
                            <span style="font-size:15px; font-weight:normal;">Комната</span>
                        </label>
                    </li>
                </ul>
            </div>
            <div>
                <div class="form-check p-[0]" >
                    <div class="flex flex-col" style="list-style:none; padding:4px 0">
                        <label v-for="(home,index) of house" :key="index" :for="`checks${index}`" class="mt-[5px]">
                            <input :checked="filter.typeObject == home.name" @change="selectTypeResident(home)" :id="`checks${index}`" class="form-check-input" name="radio" type="checkbox" style="box-shadow:none;">
                            <span style="font-size:15px; font-weight:normal;">{{ home.name }}</span>
                            <!-- <p>{{ typeHome[index] }}</p> -->
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script setup>
const {typeObject,typeDeal,quantityRoom} = getData()
const {user} = getUserByServer(JSON.parse(localStorage.getItem('token')))
let owner = {}

// if (!typeObject.value) {
//     typeObject.value = JSON.parse(sessionStorage.getItem('filter')).typeObject
// }
// if (!typeDeal.value) {
//     typeDeal.value = JSON.parse(sessionStorage.getItem('filter')).dealType
// }
const route = useRoute()
const router = useRouter()

let filter = reactive({
    typeObject: route.query.typeObject || typeObject.value,
    dealType: route.query.dealType,
    quantityRoom: route.query.quantityRoom || quantityRoom.value,
    realEstate: route.query.realEstate || '',
})
//  
const house = ref([
    {name:'Дом/Дача', checked:false},
    {name:'Коттедж', checked:false},
    {name:'Участок', checked:false},
]);
let typeHome = ref([])
function selectTypeResident(home){
    if (event.target.checked) {
        typeObject.value = event.target.nextSibling.textContent.trim()
        if (typeObject.value == 'Дом/Дача'||typeObject.value == 'Коттедж'||typeObject.value == 'Участок') {
            filter.quantityRoom = []
        }else{
            filter.quantityRoom = quantityRoom.value
        }
            filter.typeObject = typeObject.value
    }
    router.replace({query:{...filter}})
    // console.log(route.query);
    
}

// watch(filter, (value) => {
//     // value.user_id = owner.id
//   router.replace({ query: { ...value } }) 
// }, { deep: true })
watch(user,(value)=>owner = value)
</script>
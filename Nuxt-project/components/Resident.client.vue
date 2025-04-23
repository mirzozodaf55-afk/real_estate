<template>
    <div>
        <div style="margin:0; box-sizing:border-box;">
            <div style="margin-top:10px" >
                <ul class="form-check p-[0]"  >
                    <li style="list-style:none; padding:4px 0; margin:0; " >
                        <label v-if="typeDeal == 'Продажа'" for="check1" class="my-[5px] block form-check-label">
                            <input :checked="typeObject == 'Квартира в новостройке'" @change="selectTypeResident" class="form-check-input" id="check1" name="radio" type="radio" style="box-shadow:none;">
                            <span style="font-size:15px; font-weight:normal;">Квартира в новостройке</span>
                        </label>
                        <label for="check2" class="my-[5px] block">
                            <input :checked="typeObject == 'Квартира'" @change="selectTypeResident" id="check2" class="form-check-input" name="radio" type="radio" style="box-shadow:none;">
                            <span style="font-size:15px; font-weight:normal;">Квартира</span>
                        </label>
                    </li>
                </ul>
            </div>
            <div>
                <ul class="form-check p-[0]" >
                    <li style="list-style:none; padding:4px 0">
                        <label for="check3">
                            <input :checked="typeObject =='Комната'" @change="selectTypeResident" id="check3" class="form-check-input" name="radio" type="radio" style="box-shadow:none;">
                            <span style="font-size:15px; font-weight:normal;">Комната</span>
                        </label>
                    </li>
                </ul>
            </div>
            <div>
                <div class="form-check p-[0]" >
                    <div class="flex flex-col" style="list-style:none; padding:4px 0">
                        <label v-for="(home,index) of house" :key="index" :for="`checks${index}`" class="mt-[5px]">
                            <input :checked="typeObject == home.name" @change="selectTypeResident(home)" :id="`checks${index}`" class="form-check-input" name="radio" type="radio" style="box-shadow:none;">
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

if (!typeObject.value) {
    typeObject.value = JSON.parse(sessionStorage.getItem('filter')).typeObject
}
// if (!typeDeal.value) {
//     typeDeal.value = JSON.parse(sessionStorage.getItem('filter')).dealType
// }


let filter = JSON.parse(sessionStorage.getItem('filter'))
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
            sessionStorage.setItem('filter',JSON.stringify(filter))
        // filter.dealType = typeDeal.value
    }
    // if (!event.target.checked) {
    //     typeHome.value.splice(typeHome.value.indexOf(event.target.nextSibling.textContent),1)
    // }
    // console.log(filter.typeObject);
}
</script>
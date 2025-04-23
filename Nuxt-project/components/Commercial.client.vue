<template>
    <div>
        <ul  style="margin:0; padding:0; box-sizing:border-box;">
            <li style="margin-top:10px" >
                <ul class="form-check" style="paddong:0;" >
                    <li style="list-style:none; padding:4px 0; margin:0; " class="flex flex-column" >
                        <label @click="selectTypeCommercial(comm)" v-for="(comm,idx) of commercial" :key="idx" :for="`check${idx}`" class="form-check-label my-[5px]">
                            <input :checked="typeObject == comm.name" class="form-check-input" :id="`check${idx}`" name="commersial" type="radio" style="box-shadow:none;">
                            <span style="font-size:15px; font-weight:normal;">{{ comm.name }}</span>
                        </label>
                        
                    </li>
                </ul>
            </li>
            <li>
                
            </li>
            <li>
            </li>
        </ul>
    </div>
</template>
<script setup>
const commercial = ref([
    {name:'Офис'},
    {name:'Склад'},
    {name:'Здание'},
    {name:'Торговая плолщадь'},
    {name:'Коммерчемкая земля'},
])
const {typeObject} = getData()

typeObject.value = JSON.parse(sessionStorage.getItem('filter')).typeObject || ''
const filter = {}

let typeHome = ref([])
function selectTypeCommercial(item){
    if (event.target.checked) {
    }
    if (!event.target.checked) {
        typeHome.value.splice(typeHome.value.indexOf(item.name),1)
    }
    filter.typeObject = typeObject.value = item.name
    sessionStorage.setItem('filter',JSON.stringify(filter))
    console.log(typeObject);
}
</script>
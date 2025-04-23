<template>
    <div class="drop-down ml-[-12px] mt-[1px]">
        <ul class="list-drop">
            <li v-for="(room,index) of quantityRooms" :key="index">
                <input @change="selectQuanitityRoom(room)" type="checkbox" class="d-none room" :id="`btn-check${index}`" autocomplete="off">
                <label class="border border-black-300 drop-btn"  :for="`btn-check${index}`">{{room.value}}</label>
            </li>
        </ul>

    </div>
</template>
<script setup>
const filter = JSON.parse(sessionStorage.getItem('filter')) || {}
const quantityRooms = ref([
    {value:1},
    {value:2},
    {value:3},
    {value:4},
    {value:5},
])

const { quantityRoom, priceFrom, priceTo } = getData()
function selectQuanitityRoom(room){
    if (event.target.checked ) {
        quantityRoom.value.push(room.value)
        filter.quantityRoom = quantityRoom.value;
        // filter.priceFrom = priceFrom.value
        // filter.priceTo = priceTo.value
    }
    if (!event.target.checked) {
        quantityRoom.value.splice(quantityRoom.value.indexOf(room.value), 1)
    }
        
    sessionStorage.setItem('filter',JSON.stringify(filter)) 
}


onMounted(()=>{
    setTimeout(() => {
        const items = document.querySelectorAll('.room')
        if (filter.quantityRoom) {
            quantityRoom.value = filter.quantityRoom 
            items.forEach(item=>{
            filter.quantityRoom.forEach(el=>{
                // quantityRoom.value.push(el)
                if (parseInt(item.nextSibling.textContent) == el) {
                    item.checked = true
                }
            })
        })
        }else{
            quantityRoom.value = []
        }
        
    });
})
</script>
<style scoped>
.drop-down{
    position: relative;
    left: 0;
    z-index: 10;
    /* margin-top: 5px; */
    box-shadow: 0 10px 20px 0 rgba(0,0,0,.1);
    border-radius: 4px;
    background-color: #fff;
    padding: 16px;
    min-width: 300px;
    }
    .list-drop{
    margin: 0;
    display: flex;
    justify-content: space-around;
    margin: 0;
    padding: 0 ;
    }

    .drop-btn{
    cursor: pointer;
    border: 1px solid #f4f4f4;
    border-radius: 4px;
    background: none;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 35px;
    height: 35px;
    color: #121212;
    font-size: 16px;
    text-align: center;
    }

     .list-drop > li > input:checked + label{
        background-color: #e9f3fb;
    }
</style>

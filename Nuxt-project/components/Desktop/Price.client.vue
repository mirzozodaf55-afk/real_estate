<template>
    <div class="drop-down" >
        <div class="flex relative flex-nowrap justify-between">
            <div class=" rounded hover:border-sky-600 h-[28px]  w-[100%] text-sm border-[2px] overflow-hidden flex relative">
                <div class="absolute overflow-hidden  pl-[8px] pt-[4px]">
                    <input v-model="filter.min_price" @input="enterPrice" type="number" placeholder="от" class="block outline-none w-[100%] h-[100%]">
                </div>
                <span class="flex items-center justify-center h-[100%] absolute right-[0]  w-[20px]" style="background-color:white;">
                <div>с.</div>
                </span>
            </div>
            
            <div class="rounded hover:border-sky-600 h-[28px] w-[100%] ml-[8px] text-sm border-[2px] flex relative overflow-hidden ">
                <div class=" absolute overflow-hidden pl-[8px] pt-[4px]">
                    <input v-model="filter.max_price" @input="enterPrice" type="number" style="font-size:inherit; line-height:inherit; padding:0;" placeholder="до" class="block outline-none w-[100%] h-[100%]">
                </div>
                <span class="flex items-center  justify-center h-[100%] absolute right-[0]  w-[20px]" style="background-color:white;">
                    <div>с.</div>
                </span>
            </div>
        </div>
    </div>
</template>
<script setup>
const {priceFrom, priceTo, quantityRoom} = getData()
const router = useRouter()
const route = useRoute()
let filter = reactive({
    typeObject: route.query.typeObject || '',
    dealType: route.query.dealType || 'Жилая',
    quantityRoom: route.query.quantityRoom || quantityRoom.value,
    realEstate: route.query.realEstate || '',
    max_price: route.query.max_price || '',
    min_price: route.query.min_price || ''
})


function enterPrice(){
    filter.priceFrom = priceFrom.value  
    filter.priceTo = priceTo.value 
    filter.quantityRoom = quantityRoom.value
    sessionStorage.setItem('filter',JSON.stringify(filter))
}
watch(filter,(val)=>{
    router.replace({query:{...val}})
})

onMounted(()=>{
    priceFrom.value = filter.priceFrom
    priceTo.value = filter.priceTo
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
    .price-btn{
    cursor: pointer;
    border: none;
    background: none;
    padding: 20px 38px 20px 24px;
    width: 100%;
    overflow: hidden;
    color: #121212;
    font-size: 14px;
    line-height: 1.43;
    text-align: left;
    text-overflow: ellipsis;
    white-space: nowrap;
    }
</style>

<template>
    <div class="flex relative items-center justify-around w-full shadow min-h-[100vh]">
        <div class="max-w-[550px] h-[580px] absolute left-[10px] top-[50px]"> 
            <div :id="`carouselExampleIndicators-${i}`" class="carousel rounded border-[2.5px] border-primary slide w-full" data-bs-ride="carousel" style="overflow: hidden; height: 100%;">
                <div class="carousel-indicators bg-[black]/50 left-[0]">
                    <button v-for="(elem, index) in show.image" :key="`indicator-${i}-${index}`"  
                        type="button" :data-bs-target="
                        `#carouselExampleIndicators-${i}`
                        " 
                        :data-bs-slide-to="index" :class="{ 'active': index === 0 }"
                        :aria-current="index === 0 ? 'true' : false"
                        :aria-label="`Slide 0`"
                        >
                    </button> 
                </div> 
                <div class="h-full ">
                    <div v-for="(elem, index) in show.image" :key="`slide-${i}-${index}`"
                    style="height: 100%;" 
                        :class="{ 'carousel-item': true, 'active': index === 0 }">
                        <img :style="`transform:rotate(${elem.rotation}deg)`" :src="`http://127.0.0.1:8000/api/image/large/${elem.pictures}`" 
                        class="h-full" >
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" 
                :data-bs-target="`#carouselExampleIndicators-${i}`" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon bg-black" aria-hidden="true"></span> 
                    <span class="sr-only">Previous</span>
                </button> 
                <button class="carousel-control-next" type="button" :data-bs-target="`#carouselExampleIndicators-${i}`" data-bs-slide="next">
                    <span class="carousel-control-next-icon bg-black" aria-hidden="true"></span> 
                    <span class="sr-only">Next</span> 
                </button>
            </div>
        </div>

        <div class="h-[500px] absolute 
        right-[50px] top-[50px]
        w-[300px] 
        flex flex-col items-center
        justify-center
        " style="
        box-shadow: 0 0 10px black;
        background-image: linear-gradient(-135deg, white, black);
        ">
        <div class="w-[120px] h-[120px] border flex-start">
            <img :src="`http://127.0.0.1:8000/api/avatar/${show.owner.avatar}`" alt="" class="w-full h-full">
        </div>
        <h5 class="mt-[13px] text-[white]">{{show.owner.name}} {{ show.owner.last_name }}</h5>
        <NuxtLink class="text-white" style="text-decoration: none;" :href="show.owner.email" type="email">{{ show.owner.email?show.owner.email:'нет email' }}</NuxtLink>
        <NuxtLink class="text-white" style="text-decoration: none;" :to="show.owner.phone_number" target="_blank">{{ show.owner.phone_number }}</NuxtLink>
        </div>
    </div>
</template>
<script setup>
const route = useRoute()
const show = JSON.parse(route.params.object) 
console.log();

</script>
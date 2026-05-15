<template>
    <div class=" w-full flex h-[max-content]">
        <div class="bg-[white] h-[max-content] 
         w-full flex flex-wrap overflow-auto ">
            <li class="flex flex-wrap my-[10px] flex 
                 overflow-hidden mx-[8px] max-h-[350px]
                justify-center rounded-[6px] max-w-[300px]
                 p-[5px] bg-[white] h-[max-content]" v-for="(announ, i) of likes" :key="i"
                style="box-shadow:0 0 10px silver;">
                <div class="flex flex-wrap m-0 p-0 sm:flex-wrap 
                    md:flex-wrap lg:flex-wrap flex-col">
                    <div class="h-[200px] relative items-center text-[white] flex overflow-hidden bg-[black] m-[0]">
                        <div class="flex absolute flex-col top-[8px] left-[5px] z-[20]">

                            <button @click="addLikes(announ, likes)"
                                class="w-[max-content] mb-[5px] text-[silver] text-[14px] flex items-center h-[20px]">
                                <div class="text-[19px] text-[red]">
                                    <font-awesome-icon :icon="['fas', 'heart']" />
                                </div>
                            </button>
                        </div>
                        <div :id="`carouselExampleIndicators-${i}`" class="carousel slide" data-bs-ride="carousel"
                            style="overflow: hidden; height: 100%;">
                            <div class="h-full md:w-full">
                                <div v-for="(elem, index) in announ.images" :key="`slide-${i}-${index}`"
                                    style="height: 100%;" :class="{ 'carousel-item': true, 'active': index === 0 }">
                                    <div class="flex absolute right-[5px] top-[5px] text-[white] 
                                                bg-[black]/40 px-[5px] z-[30]">
                                        <button>
                                            <font-awesome-icon :icon="['fas', 'camera']" />
                                        </button>
                                        <div><span>&nbsp;{{ index + 1 }}</span> / <span>{{ announ.images.length }}</span>
                                        </div>
                                    </div>
                                    <img :style="`transform:rotate(${elem.rotation}deg)`"
                                        :src="`http://127.0.0.1:8000/api/image/medium/${elem.pictures}`" class="h-full">
                                </div>
                            </div>
                            <button class="carousel-control-prev" type="button"
                                :data-bs-target="`#carouselExampleIndicators-${i}`" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="sr-only">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button"
                                :data-bs-target="`#carouselExampleIndicators-${i}`" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="sr-only">Next</span>
                            </button>
                        </div>

                    </div>
                    <div class="flex flex-col bg-[white] h-[max-content] mt-[5px]">
                        <h3 class="flex justify-between items-start flex-col w-[100%] text-[16px]">
                            <span class="text-[#0661ec] font-bold">
                                {{ announ.quantity_room + '-комн' }},
                                {{ announ.type_object }}.,
                                {{ announ.total_area }}м<sup class="text-[13px]">2</sup>,
                                {{ announ.floor }}/{{ announ.floor_in_house }}
                                этаж
                            </span>&nbsp;
                            <span class="text-[black] font-bold
                         truncate mr-[5px] text-[19px]">{{ announ.price }} сомон</span>
                        </h3>
                        <div class="w-full max-w-[300px] py-[0px] text-[14px] text-[gray] h-[max-content]">
                            {{ announ.description }}
                        </div>
                        <div class="flex items-start
                         bottom-[0] static justify-between
                         flex-col
                         h-[40px] bg-[white] mt-[5px] pr-[8px]">
                        </div>
                    </div>
                </div>
            </li>
        </div>
    </div>
</template>
<script setup>
const { user } = getUserByServer(JSON.parse(localStorage.getItem('token')))
const owner = ref()
watch(user, value => owner.value = value)
const likes = ref()
fetch('http://127.0.0.1:8000/api/favorite', {
    method: 'GET',
    headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer ' + JSON.parse(localStorage.getItem('token'))
    }
}).then(r => r.json())
    .then(data => {
        likes.value = data

    })

async function addLikes(item, likesArr) {
    owner.value = user.value
    item.isLike = !item.isLike

    event.preventDefault()
    await fetch('http://127.0.0.1:8000/api/likes', {
        method: 'post',
        headers: {
            "Content-Type": 'application/json',
            Authorization: 'Bearer ' + JSON.parse(localStorage.getItem('token'))
        },
        body: JSON.stringify({
            announ_id: item.id,
            user_id: owner.value.id
        })
    }).then(r => r.json())
        .then(res => {
            item.likes = res
            likes.value = likesArr = likesArr.filter(elem => elem.id !== item.id)

        })
}
</script>
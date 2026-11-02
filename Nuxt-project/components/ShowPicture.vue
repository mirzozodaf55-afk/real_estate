<template>
    <div class="w-full flex bg-[black] fixed top-[0] z-[160] h-[100vh]">
        <img class="w-full h-full" :src="`${apiBase()}/api/filemessage/${picture}`" alt="">
    <div @click="back" class="absolute p-[0] right-[5px] top-[5px] 
    cursor-pointer bg-[pink] text-[50px] bolder
    text-[white]">&times;</div>
    </div>
</template>
<script setup>
const picture = ref()
const route = useRoute();
const router = useRouter();
picture.value = route.params.name

function back(){
    router.go(-1)
}
</script>
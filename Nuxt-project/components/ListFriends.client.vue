<template>
    <div class="mx-[auto] px-[10px] w-[300px] h-[500px] shadow rounded">
        <div @click="startMessae(users)"  v-for="users of friends" :key="users.id">
         <div v-if="users.phone_number!==owner.phone_number" class="flex h-[50px] my-[15px] rounded 
        cursor-pointer bg-[gray]
         items-center">
             <div class="w-[45px] ml-[7px] h-[45px] rounded-[50%] border">
                 <img :src="`http://127.0.0.1:8000/api/avatar/${users.avatar}`" class="w-full h-full rounded-[50%]" alt="">
             </div>
             <div class="ml-[5px]">{{ users.name&&users.last_name?`${users.name} ${users.last_name}`:users.phone_number }}</div>
         </div>
        </div>
    </div>
</template>
<script setup>

const owner = ref()
const {user} = getUserByServer(JSON.parse(localStorage.getItem('token')))
const friends = ref([])
const route = useRoute()
const router = useRouter()
watch(user,(value)=>{
owner.value = value
})
owner.value = user.value
fetch('http://127.0.0.1:8000/api/get_all_users',{
    method:'GET',
    headers:{
        'Content-Type':'aplication/json',
        Authorization:'Bearer '+JSON.parse(localStorage.getItem('token')),
    }
}).then(r=>r.json())
.then(res=>{
    friends.value = res
})

function startMessae(user){
    router.push(`chat-${owner.value.id}-${user.id}`)    
}


</script>
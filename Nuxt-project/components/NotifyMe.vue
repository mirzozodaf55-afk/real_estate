<template>
<button @click="sendDataNotify" class="fixed top-[150px] border-[1px] border-orange-500
font-semibold text-orange-500 hover:bg-orange-500 hover:text-white
hover:shadow-lg transition-all duration-300 ease-in-out 
py-[5px] px-[2.5px] rounded ">
    <span>Уведомить Меня</span>
</button>
</template>

<script setup>
const { user } = getUserByServer(JSON.parse(localStorage.getItem('token')))
const route = useRoute()
const router = useRouter()
async function sendDataNotify(){

    await fetch('http://127.0.0.1:8000/api/notify-me',{
        method:'POST',
        headers:{
            "Content-Type":'application/json',
            Authorization:'Bearer '+JSON.parse(localStorage.getItem('token'))
            
        },
        body: JSON.stringify({
            user_id: user.value.id,
            filters: route.query
        })
    }).then(r=>r.json())
    .then(responce=>{
        console.log(responce);
        
    })
    
}
</script>
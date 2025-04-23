<template>
    <div class="min-h-[100vh] m-[10px]  bg-[white] flex w-full" >
        <Message v-if="showMessage"/>
        <div class="mt-[20px] m-[10px] flex w-full" style="backdrop-filter: 8px;">
            <ul style="list-style: none;" class="m-[0] p-[0] w-full">
            <h2>Мои объявления</h2>
                <li class="flex-wrap my-[15px] flex flex-col justify-center rounded-[6px] p-[8px] bg-[white] w-[100%]" v-for="(announ,index) of myAnnoun" :key="index" style="box-shadow:0 0 10px silver;">
                    <div class="flex m-0 p-0 sm:flex-wrap md:flex-nowrap lg:flex-nowrap ">
                    <!-- <UpdateAnnoun :announ="announ" class="absolute z-[100] h-[100vh] botom-0 left-0 right-0 bg-[white]" /> -->
                    <div class="h-[150px] m-[0] min-w-[200px]">
                        <img class="w-full h-full" :src="`http://127.0.0.1:8000/api/image/small/${announ.image?announ.image:'deafault.jpg'}`">
                    </div>
                    <div class="flex flex-col h-[max-content] w-[75%] ml-[10px]">
                        <h3 class="flex justify-between items-center w-[100%] text-[16px]">
                            <span class="text-[#0661ec] font-bold">
                                {{ announ.quantity_room+'-комн' }},
                                {{ announ.type_object }}.,
                                {{announ.total_area}}м<sup class="text-[13px]">2</sup>,
                                {{announ.floor}}/{{announ.floor_in_house}}
                                этаж
                                </span>&nbsp;
                        <span class="text-[black] font-bold truncate mr-[5px] text-[19px]">{{announ.price}} сомон</span>
                        </h3>
                        <div class="w-full text-[14px] text-[gray] h-[115px]">
                            {{announ.description}}
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between h-[40px] bg-[white] mt-[5px] pr-[8px]">
                    <div class="flex">
                        <div class="flex h-[max-content]">
                            <button class=" rounded-[3px] px-[10px] text-primary h-[max-content]" style="box-shadow:0 0 4px;" @click="updateAnnoun(announ)">Изменить</button>
                        </div>
                        <div v-if="announ.deleted_at == null" class="flex h-[max-content] ml-[10px]">
                            <button class="rounded-[3px]
                            px-[10px] text-danger h-[max-content]"
                            style="box-shadow:0 0 4px;"
                            @click="deleteAnnoun(announ)">Удалить</button>
                        </div>
                        <div v-if="announ.deleted_at !== null" class="flex h-[max-content] ml-[10px]">
                            <button class="rounded-[3px]
                            px-[10px] text-success h-[max-content]"
                            style="box-shadow:0 0 4px;"
                            @click="restore(announ)">Восстановить</button>
                        </div>
                        <div class="flex h-[max-content] ml-[10px]">
                            <button class="rounded-[3px]
                            px-[10px] text-[darkorange] h-[max-content]"
                            style="box-shadow:0 0 4px;"
                            @click="deleteAnnoun">Скрыть</button>
                        </div>
                    </div>
                    <div class="flex justify-between  w-[max-content]">
                        <span class="text-[14px] font-bold">дата публикация:&nbsp;<span class="font-normal text-[silver]">{{announ.created_at}}</span></span>
                        <span class="w-[max-content] mx-[5px] text-[silver] text-[14px] flex items-center h-[20px]">
                            <div class="text-[16px]">
                                <font-awesome-icon :icon="['far', 'heart']" />
                            </div>
                            &nbsp;<span>{{ announ.likes }}</span>
                        </span>
                        <span class="w-[max-content] text-[silver] text-[14px] flex items-center h-[20px]">
                            <div class="text-[15px]">
                                <font-awesome-icon :icon="['far', 'eye']" />
                            </div>
                            &nbsp;<span>0</span>
                        </span>
                    </div>
                </div>        
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup>
const {updateAnnounData}= getData()
const {showMessage} = useSwitch()
function updateAnnoun(announ){
    navigateTo('/update-announ')
    sessionStorage.setItem('update',JSON.stringify(announ))
}
function deleteAnnoun(announ){
    showMessage.value = true
    document.body.style.overflowY ='hidden'
    sessionStorage.setItem('delete',JSON.stringify(announ))
}

async function restore(announ){
    await fetch('http://127.0.0.1:8000/api/restore',{
        method:'post',
        headers:{
            "Content-Type":'application/json',
            Authorization:'Bearer '+JSON.parse(localStorage.getItem('token'))
            
        },
        body:JSON.stringify({announ:announ})
    }).then(res=>{
        if (res.ok) {
            window.location.replace('/personal_area/my_announ')
        }
        return res.json()
    })
    .then(res=>console.log(res))
}
const myAnnoun = ref()
const {user} = getUserByServer(JSON.parse(localStorage.getItem('token')))
let owner = {};

watch(user,(val)=>
owner = val)
onMounted(async()=>{
    owner = user.value
    await fetch('http://127.0.0.1:8000/api/get_announs_by_user',{
        method:'post',
        headers:{
            "Content-Type":"application/json",
            Authorization:'Bearer '+JSON.parse(localStorage.getItem('token'))
        },
        body:JSON.stringify(user.value)
    })
    .then(res=>res.json())
    .then(res=>{
        myAnnoun.value = res[0]
        console.log(res);
    })
    const date = ref('')
    for (let i = 0; i < myAnnoun.value.length; i++) {
        const element = myAnnoun.value[i];
            let d = new Date(element.created_at)
            let newDate = new Date()
            const formatted = d.toLocaleString('ru-RU', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
                })
                element.created_at = formatted
                let result;
    }
})

</script>
<style scoped>
*{
    font-family:'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;
}
</style>
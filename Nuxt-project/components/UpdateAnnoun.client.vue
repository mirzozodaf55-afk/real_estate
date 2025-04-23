<template>
    <div class="min-h-[100vh] flex flex-col items-center">
        <div class="w-[80%] shadow px-[20px] h-[max-content] mt-[20px]">
            <!-- {{ updateAnnounData }} -->
            <input type="text" class="border p-[5px] rounded outline-0 text-[15px] shadow-none w-[max-content]" v-model="updateAnnounData.type_object">
            <Pictures :image="updateAnnounData.pictures?updateAnnounData.pictures:announ.pictures"/>
            <div class="w-full flex justify-end py-[10px]">
                <button class="btn btn-primary mr-[5px]" @click="navigateTo('/personal_area/my_announ')">Назад</button>
                <button @click="changeAnnouncement" class="btn btn-success">Изменить</button>
            </div>
        </div>
    </div>
</template>
<script setup>
const {typeObjectCommercial} = data()
const {images} = getData()
let updateAnnounData = JSON.parse(sessionStorage.getItem('update'))
let announ = ref({})
onMounted( async ()=>{
    await fetch('http://127.0.0.1:8000/api/get-data-info',{
        method:'post',
        headers:{
            "Content-Type":'application/json'
        },
        body:JSON.stringify({announ:updateAnnounData})
        
    }).then(res=>{
        return res.json()}
        ).then(responce=>{
        const formData = new FormData()
        announ.value = responce
        updateAnnounData = responce 
        // images.value = updateAnnounData.pictures
        sessionStorage.setItem('update',JSON.stringify(updateAnnounData))
        // for (let i = 0; i < responce.images.length; i++) {
        //     formData.append('images[]',responce.images[i])
            
        // }
        // console.log(formData.getAll('images[]'));
    })
    
})
const formData = new FormData()
async function changeAnnouncement(){
    for (let i = 0; i < images.value.length; i++) {
        if (images.value[i].file) {
            formData.append('images[]',images.value[i].file)
            formData.append(`image-data[${i}]`,images.value)
        }
    }
    
        await fetch('http://127.0.0.1:8000/api/update-announ',{
            method:'post',
            headers:{
                "Content-Type": 'application/json',
                Authorization:'Bearer '+JSON.parse(localStorage.getItem('token'))
            },
            body:JSON.stringify({img:images.value,announ:updateAnnounData})
        })
        .then(res=>res.json())
        .then(res=>{
            console.log(res);
        })
        
    if (formData.getAll('images[]').length) {
        await fetch('http://127.0.0.1:8000/api/update-announ',{
            method:'post',
            headers:{
                Authorization:'Bearer '+JSON.parse(localStorage.getItem('token'))
            },
            body:formData
        })
        .then(res=>res.json())
        .then(res=>{
            console.log(res);
        })
    }
    }
     
</script>
<template>
    <div class="fixed top-0 overflow-y-scroll left-0 right-0 bottom-0 z-[1000] flex items-center justify-center bg-[black]/40">
        <div class="w-[450px] relative rounded h-[120px] bg-[white] flex justify-center">
            <div @click="hideMessage" class="cursor-pointer absolute top-[4px] right-[4px]">
                <font-awesome-icon :icon="['fas', 'circle-xmark']" />
            </div>
            <span class="absolute top-[30px]">Вы действительно хотите удалить этот объявления?</span>
            <div class="w-full justify-evenly absolute bottom-[10px] flex">
                <button @click="deleteAnnoun" class="btn btn-danger">Удалить</button>
                <button @click="hideMessage" class="btn btn-success">Отмена</button>
            </div>
        </div>
    </div>
</template>
<script setup>
const deleteAnnounData = JSON.parse(sessionStorage.getItem('delete'))
const {updateAnnounData} = getData()
const {showMessage} = useSwitch()
function hideMessage(){
    showMessage.value = false
    document.body.style.overflowY = 'scroll'
}
async function deleteAnnoun(){
    await fetch('http://127.0.0.1:8000/api/delete-announ',{
        method:'post',
        headers:{
            "Content-Type":'application/json',
            Authorization:'Bearer '+JSON.parse(localStorage.getItem('token'))
        },
        body:JSON.stringify({announ:deleteAnnounData})
    }).then(res=>res.json())
    .then(res=>console.log(res))
    sessionStorage.removeItem('delete')
    window.location.replace('/personal_area/my_announ')
    hideMessage()
}
</script>
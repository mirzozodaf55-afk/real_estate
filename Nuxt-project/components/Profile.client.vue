<template>
    
    <main class="bg-[#f4f4f4] flex w-[100%] min-h-[100vh] sm:w-[100%] md:w-[700px] lg:w-[1000px] ">
                    <div class="w-full block ">
                        <div class="justify-evenly sm:flex-col md:flex-col lg:flex-row lg:items-start md:items-center flex-col items-center sm:flex-col sm:items-center mt-[15px] flex ">
                            <div class="w-[94%]  bg-[white] m-[5px] sm:w-[94%]">
                                <div class="mb-[8px] border-b px-[10px] pb-[20px]">
                                    <div class="flex mt-[10px]">
                                            <a style="flex: 0 0 56px; user-select: none; text-decoration: none;" class="mr-[15px]  overflow-hidden rounded-[50%] flex justify-center
                                             items-center h-[56px] overflow-hidden"
                                                >
                                                <span  class="rounded-[50%]
                                                flex items-center justify-center
                                                bg-[black] w-[92%] h-full relative">
                                                    <!-- <font-awesome-icon
                                                v-if="loadImage"
                                                :icon="['fa', 'load']"
                                                class="text-[white]"
                                            /> -->
                                            <p v-if="loadImage" class="text-[white]" style="position: absolute; left: 50%; top: 50%; transform: translate(-50%,-50%);">
                                                <font-awesome-icon :icon="['fas', 'rotate']" spin />
                                            </p>
                                                    <img :src="`http://127.0.0.1:8000/api/avatar/${avatar?avatar:owner.avatar}`" class="w-full h-full rounded-[50%]">
                                                </span>
                                            </a>
                                            <div class="flex flex-col justify-center" style="flex: 1 1;">
                                                <a style="text-decoration: none;" class="font-normal cursor-pointer text-[#2b87db] text-[14px]">
                                                    <div class="text-[#121212] font-bold text-[16px] leading-[1.43]">{{ name }}</div>
                                                    <div class="text-[#121212] font-bold text-[16px] leading-[1.43]">{{ lastName }}</div>
                                                    <p v-if="age" class="text-[black]">{{ age }} лет</p>
                                                </a>
                                            </div>
                                    </div>
                            
                                </div>
                                <div class="w-[65%] flex  flex-col">
                                    <div class="mx-[15px] mt-[10px] flex w-full justify-between">
                                        <input @change="addNewPicture" accept="image/*" type="file" id="file" class="d-none">
                                        <button @click="open" class="rounded bg-[rgba(4,104,255,.2)] text-[#075ad9] m-[0] px-[10px] text-[14px] h-[30px] w-[max-content]">Обновить фото</button>
                                        <button class="rounded bg-[rgba(255,31,52,.2)] text-[red] m-[0] px-[10px] text-[14px] h-[30px] w-[max-content]">Удалить фото</button>
                                    </div>

                                    <div class="flex h-[250px] justify-around flex-col mx-[15px] mt-[10px] w-full">
                                        <div class="flex flex-col">

                                            <label class="text-[14px]" for="">Имя</label>
                                            <input v-model="name" class="border h-[30px] rounded outline-0 px-[8px]" type="text">
                                        </div>
                                        <div class="flex flex-col">

                                            <label class="text-[14px]" for="">Фамилия</label>
                                            <input v-model="lastName" class="border h-[30px] rounded outline-0 px-[8px]" type="text">
                                        </div>
                                        <div class="flex flex-col">

                                            <label class="text-[14px]" for="">Дата рождения</label>
                                            <input @change="data" v-model="date" class="border bg-[white] h-[30px] rounded outline-0 px-[8px]" type="date">
                                        </div>

                                        <button @click="sendData" class="w-[max-content] bg-[#075ad9] px-[10px] outline-0 text-[white] rounded h-[28px]">Сохранить</button>
                                    </div>
                                </div>
                            </div>
                            <div class="w-[100%] px-[20px] mt-[3px] sm:w-[100%] md:w-[100%]">
                                <div class="w-[100%] my-[2px] sm:w-[100%] bg-[white]">
                                    <div class="flex flex-col p-[10px]">
                                        <label for="email" class="text-[14px]">
                                        Электронная почта
                                        </label>
                                        <input  v-model="message" class="border outline-0 text-[15px] rounded-[4px] py-[2px] px-[8px]" type="email"  id="email">
                                    </div>
                                    <div class="p-[10px] pt-[0]">
                                        <button @click="sendMessage" class="px-[8px] rounded bg-[#0d6efd] text-[white] py-[2px] outline-0 text-[15px] border">Отправить</button>
                                    </div>
                                </div>
                                <div class=" w-[100%] mt-[3px] sm:w-[100%] bg-[white]">
                                    <div @click="show" class="block py-[5px] justify-between flex px-[10px]">
                                        <h6 class="text-[15px] mt-[5px] font-bold">Сменить пароль</h6>
                                        <div>
                                            <font-awesome-icon
                                                v-if="!showHide"
                                                style="margin-right: 5px"
                                                class="font-bold text-[12px] text-primary"
                                                :icon="['fas', 'chevron-up']"
                                            />
                                            <font-awesome-icon
                                                v-if="showHide"
                                                style="margin-right: 5px"
                                                class="font-bold text-[12px] text-primary"
                                                :icon="['fas', 'chevron-down']"
                                            />
                                        </div>
                                    </div>
                                    <div class="flex px-[10px] flex-col w-[300px] overflow-hidden transition-all" :class="showHide ? 'h-[0]': 'h-[120px]'">
                                        <div class="border rounded overflow-hidden flex my-[5px] h-[30px] items-center">
                                            <input v-model="password" class="font-normal  text-[15px] w-[250px] h-full mr-[5px] px-[10px] outline-0" :type="!isPassword? 'password':'text'" >
                                            <button @click="changeType" class="outline-0">
                                                <font-awesome-icon class="text-[darksilver] text-[14px]" :icon="['far', isPassword ? 'eye':'eye-slash']" />
                                            </button>
                                        </div>

                                        <div class="border rounded overflow-hidden flex my-[5px] h-[30px] items-center">
                                            <input v-model="confirm_password" class="font-normal text-[15px] w-[250px] h-full mr-[5px] px-[10px] outline-0" :type="!isPassword1 ? `password`:'text'" >
                                            <button @click="changeType2" class="outline-0">
                                                <font-awesome-icon  class="text-[darksilver] text-[14px]" :icon="['far',isPassword1 ? 'eye':'eye-slash']" />
                                                <!-- <font-awesome-icon v-else class="text-[darksilver] text-[14px]" :icon="['far', 'eye-slash']" /> -->
                                            </button>
                                        </div>
                                        <div class="py-[10px] pt-[0]">
                                        <button @click="confirm" class="px-[8px] rounded bg-[#0d6efd] text-[white] py-[2px] outline-0 text-[15px] border">Сохранить</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </div>
                    </div>
                </main>    
    
</template>
<script setup>

// useHead({
//     title:'Профиль'
// })
const route = useRoute()
getUser()
const {responce,loadImage} = getData()
const isPassword = ref(false);
const isPassword1 = ref(false);
const name = ref('')
const lastName = ref('')
const date = ref('')
const message = ref('')
const password = ref('')
const confirm_password = ref('')
const {user} = getUserByServer()
const owner = user.value
const d = new Date()
const age = ref(0)
const {avatar} = getData()
watch(user,(value)=>{
    owner.value=value
})
console.log(user.value);

function changeType(){
    isPassword.value = !isPassword.value
}

function changeType2(){
    isPassword1.value = !isPassword1.value
}

const showHide = ref(true)
function show(){
showHide.value = !showHide.value
}
function open(){
    const file = document.querySelector('#file');
    file.click()
}


function data(){
    date.value = event.target.value
    // console.log(date.value);
}
// const loadImage = ref(false)
async function addNewPicture(event){
    owner.value = user.value
    
    const formData = new FormData()
    formData.append('avatar',event.target.files[0])
    formData.append('phone', owner.value.phone_number)
    loadImage.value=true
    await fetch('http://127.0.0.1:8000/api/avatar',{
        method:'post',
        headers:{
            Authorization:'Bearer '+JSON.parse(localStorage.getItem('token')) 
        },
        body:formData
    })
    .then(res=>res.json())
    .then(res=>{
        avatar.value = res.avatar || {}
        // responce.value[1] = res
        owner.value = user.value = res
        
        // localStorage.setItem('owner',JSON.stringify(res))
        
        
    })
    loadImage.value =false
}

async function sendData(){
    owner.value = user.value
    
    await fetch('http://127.0.0.1:8000/api/update_user',{
        method:'post',
        headers:{
            "Content-Type":'application/json',
            Authorization:'Bearer '+JSON.parse(localStorage.getItem('token')) 
        },
        body:JSON.stringify({
            name:name.value,
            lastName:lastName.value,
            date:date.value,
            phone: owner.value.phone_number
        })
        
    })
    .then(res=>res.json())
    .then(res=>{
        user.value = owner.value = res
        name.value= lastName.value= date.value = ''
        name.value = res.name
        lastName.value = res.last_name
        date.value = res.birth_date
        const birthYear = new Date(date.value)
        const year = birthYear.getFullYear()    
        console.log(res);
            
        age.value = d.getFullYear() - year
        })
}

async function sendMessage(){
    owner.value = user.value
    await fetch('http://127.0.0.1:8000/api/send_message',{
        method:'post',
        headers:{
            "Content-Type":'application/json',
            Authorization:'Bearer '+JSON.parse(localStorage.getItem('token')) 
        },
        body:JSON.stringify({
            'phone':owner.phone_number,
            'email':message.value
        })
    })
    .then(res=>res.json())
    .then(res=>{
       message.value = res.email
    })
    
}

function confirm(){
    console.log(password.value,confirm_password.value);
}

async function getUser(){
    await fetch('http://127.0.0.1:8000/api/get_user',{
       method:'GET',
       headers:{
           "Content-Type":'application/json',
           Authorization:'Bearer '+ JSON.parse(localStorage.getItem('token')) 
       }
   })
   .then(res=>res.json())
   .then(res=>{
       name.value = res.name
       lastName.value = res.last_name
       date.value = res.birth_date
       message.value = res.email
    //    responce.value[1] = res
    //    localStorage.setItem('owner',JSON.stringify(responce.value))
       const birthYear = new Date(date.value)
       const year = birthYear.getFullYear()  
       
       if (owner.birth_date || res.date) {
           
           age.value = d.getFullYear() - year
       }      
   }) 
}

onMounted(()=>{
owner.value = user.value
})
</script>
<style>
#chat-container::-webkit-scrollbar{
  display: none;
}
#chat-container {
  scroll-behavior: smooth;
}
.animate{
  animation: animate .3s ease;
}

@keyframes animate{
  0%{
    opacity: 0;
    bottom: 0;
  }
  100%{
    opacity: 1;
    bottom: 50px;
  }
}
.hide{
  animation: hide .3s ease;
}

@keyframes hide{
  0%{
    opacity: 1;
    bottom: 50px;
  }
  100%{
    opacity: 0;
    bottom: 0px;
  }
}
</style>
<template>
  <ShowVideo v-if="show_video"/>
  <main v-if="!show_video" class="xsm:w-[100vw] mx-[auto] justify-between 
  overflow-hidden rounded relative flex md:w-[330px] sm:w-[330px] lg:w-[330px]
  min-h-[550px] h-[93vh] border border-color-[red]">
    <div  class="w-full h-[50px] absolute top-0 items-center flex" >
      <button @click="navigateTo('/listfriend')" class="ml-[10px] text-[gray] text-[19px]">
        <font-awesome-icon :icon="['fa-solid', 'fa-chevron-left']" />
      </button>
      <div v-if="!wait" class="flex justify-between items-center min-w-[50%] h-full absolute left-[20px]">
        <div class="w-[45px] mx-[5px] h-[45px] rounded-[50%] border bg-cover bg-center"
        :style="`background-image:url('http://127.0.0.1:8000/api/avatar/${friend.avatar}');`"
        ></div>
        <h5 class="cursor-default">{{ friend.name&&friend.last_name?`${friend.name} ${friend.last_name}`:`${friend.phone_number}`}}</h5>
        <div class="absolute bottom-[-15px] left-[55px]"><p class="text-[15px] cursor-default text-[green]">{{ typingMessage }}</p></div>
      </div>

      <div class="absolute right-[20px] text-[20px] text-[gray]">
        <font-awesome-icon :icon="['fas', 'ellipsis-vertical']" />
      </div>
    </div>
    <div 
    id="chat-container" ref="scrollBottom"
    class="snap-normal py-[20px] overflow-y-auto absolute top-[50px] h-[86%] w-full"
    :style="`background-image: linear-gradient(45deg,#${color},#${color2});`"
    >
      <div  
      class="space-y-2 w-full rounded my-[5px] flex" :class="mess.isMyMessage?'justify-end items-center':''" 
      v-for="(mess,i) of messageList" :key="i" @contextmenu="menuMessage">
        <div class="message flex items-start p-[2px] 
         rounded my-[10px] bg-[none]"
         :class="mess.isMyMessage?'justify-start flex-row-reverse':''">
          <div 
          v-if="mess.type =='message'"
          class="
           ml-2 text-[20px] 
           max-w-[100%]
           mr-[5px]
           relative  top-[15px]
           cursor-default shadow 
          bg-[white] text-[black]
          flex items-center
          py-[5px] px-[8px]
           text-gray-700" 
           style="border-radius: 17px;"
          :style="mess.isMyMessage?`border-top-right-radius: 0px; font-size:18px; background: darkblue; color:white;`:'border-bottom-left-radius: 0px;'"
           
           >
           <p class="text-[20px] mb-[0]">{{ mess.text}}</p>
           <p v-if="mess.pending" class="text-[13px] mb-[0] mt-[15px] ml-[10px]">
            <font-awesome-icon :icon="['fas', 'spinner']" spin-pulse />
           </p>
            <p v-else class="text-[13px] mb-[0] mt-[15px] ml-[10px]">{{ mess.time }}</p>
          </div>
          
           <div v-if="mess.type == 'picture'" class="
           ml-2 text-[20px] text-[white]
           max-w-[50%]
           mr-[5px]
           relative
           top-[15px]
           min-w-[78%]
           cursor-pointer
           min-h-[180px]
           bg-[white]
           h-[100px] relative
           shadow text-[black] p-[2px]
           overflow-hidden
           text-gray-700 relative
           " 
           style="border-radius: 7px;"
           :style="mess.isMyMessage?`background: darkblue; color:white;border-top-right-radius: 0px;`:'border-bottom-left-radius: 0px;'"
           >
           <img @click.stop="showPicture(mess)" class="w-full h-full min-w-[78%]" 
           style="border-radius: 7px;"
           :style="mess.isMyMessage?`border-top-right-radius: 0px;`:'border-bottom-left-radius: 0px;'"
           :src="mess.urlImage" :alt="mess.type"/>
           <p v-if="mess.pending" class="absolute bg-[black]/50 
           bottom-[5px] right-[10px] text-[13px] mb-[0] mt-[15px]
           ml-[10px] text-[white] rounded-[3px] px-[4px]">
            <font-awesome-icon :icon="['fas', 'spinner']" spin-pulse />
           </p>
            <p v-else class="text-[13px] bg-[black]/50 absolute 
           bottom-[-5px] text-[white] px-[4px] rounded-[3px]
           right-[10px]">{{ mess.time }}</p>
           <!-- <button @click.stop="removeFile(mess)" v-if="!mess.pending"
           style="transform: translate(-50%,-50%); pointer-events: none;"
           class="flex items-center 
              w-[30px] h-[30px] justify-center left-[50%]
              top-[50%]
              bg-[black]/50 border absolute rounded">
              <font-awesome-icon data-name="video"
            style="pointer-events: none;"
          class="text-white bg-[none] text-[17px] absolute" 
          :icon="['fa', 'times']" />
        </button> -->
          </div>

          
          <div v-if="mess.type == 'video'" class="
           ml-2 text-[20px] text-[white]
           max-w-[50%]
           mr-[5px]
           relative
           top-[15px]
           min-w-[65%]
           cursor-pointer
           h-[180px]
           bg-[white]
           h-[100px] relative
           shadow text-[black] p-[2px]
           text-gray-700
           flex justify-center items-center
           " 
           style="border-radius: 7px;"
           :style="mess.isMyMessage?`background: orange;
           color:white;border-bottom-right-radius: 0px;`:'border-bottom-left-radius: 0px;'"
           >
           <video ref="videoRef" @click="showVideo(mess)"
           style="border-radius: 7px; object-fit: cover;"
           v-if="mess.type == 'video'"
           :style="mess.isMyMessage?`border-bottom-right-radius: 0px;`:'border-bottom-left-radius: 0px;'"
            :src="mess.urlVideo"
           class="w-full bg-[black]/80 h-full min-w-[65%]"
           >
           <div class="bg-[red] h-[30px]">{{ mess.time }}</div>
           <!--  -->
          </video> 
          <p v-if="mess.pending" class="absolute bg-[black]/50 
           bottom-[5px] right-[10px] text-[13px] mb-[0] mt-[15px]
           ml-[10px] text-[white] rounded-[3px] px-[4px]">
           <font-awesome-icon :icon="['fas', 'spinner']" spin-pulse />
          </p>
          <p v-else class="absolute 
           right-[10px] text-[13px] bg-[black]/50 
           bottom-[-5px] text-[white] px-[4px] rounded-[3px]
           right-[10px]">{{ mess.time }}
          </p>
          <!-- <progress id="progress-bar"></progress> -->
          <div @click="removeFile(mess)" v-if="mess.pending" class="flex items-center 
          w-[30px] h-[30px] justify-center
          bg-[black]/50 border absolute rounded">
              <font-awesome-icon data-name="video"
            style="pointer-events: none;"
          class="text-white bg-[none] text-[17px] absolute" 
          :icon="['fa', 'times']" />
        </div>
          <div v-else @click="playVideo(mess)" class="flex items-center 
          w-[30px] h-[30px] justify-center
          bg-[black]/50 border absolute rounded">
              <font-awesome-icon data-name="video"
            style="pointer-events: none;"
        class="text-white bg-[none] text-[17px] absolute" 
        :icon="['fa', mess.isPlay?'pause':'play']" />
      </div>
      
        </div>
          </div>
          <!-- <div>
            <input v-if="mess.isMyMessage" type="checkbox" name="select-message" class="mr-[5px] top-[50%]"
            style="transform: translateY(-50%);"
            :id="`check-${i+1}`">
          </div> -->
        </div>
        
        <!-- <div v-if="value" class="w-[100px] flex h-[30px] bg-[white] rounded absolute left-[0] bottom-[0]">
          <p class="flex p-0">{{ value }}</p>
        </div> -->
      </div>
      <div v-if="showFile" class="animate w-full h-[200px]
       bottom-[50px]
       absolute bg-[white] justify-around 
       items-center flex">
        <button @click="close" class="absolute top-[-10px] right-[3px] text-[30px]">&times;</button>
       
        <button data-name="picture" @click.prevent="open" class="cursor-pointer w-[130px] h-[130px] rounded 
       flex items-center justify-center
       " style="border: 2px dotted skyblue;">
        <font-awesome-icon data-name="picture"
        style="pointer-events: none;"
        class="text-[40px] text-blue-500" 
        :icon="['fas', 'image']" />
       </button>
        <button @click.prevent="open" data-name="video" class="cursor-pointer w-[130px] h-[130px] rounded
       flex items-center justify-center
       " style="border: 2px dotted skyblue;">
        <font-awesome-icon data-name="video" 
        style="pointer-events: none;"
        class="text-[40px] text-blue-500" 
        :icon="['fa', 'video']" />
       </button>
      </div>
      <div class="w-full h-[50px] absolute bottom-[0] bg-[lime]">
      <!-- <div v-if="typingMessage" class="bg-[yellow] top-[-25px] text-[red] absolute right-[20px]">{{ typingMessage }}</div> -->
      <div class="flex absolute top-0 bottom-[0] left-[0] bg-[white] space-x-2 mt-0 border w-full">
          
          <form 
          @submit.prevent="sendMessage"
          enctype="multipart/form-data"  class="flex space-x-2 w-full" 
          action=""><input
          v-model="message"
          type="text"
          placeholder="Type a message..."
          class="flex-1 p-2  outline-none rounded-md"
          />
          <input  
          type="file" @change.prevent="handleFileChange"
           style="display: none;" ref="fileInput" id="sendfile" >
          <button id="file"
          @click.prevent="open"
          class="outline-none p-2 text-silver rounded-md
           hover:bg-blue-200"
          >
            <font-awesome-icon style="pointer-events: none;" :icon="['far', 'folder-closed']" />
          </button>
          <button
          
          class="p-2  text-blue-500 rounded-md hover:bg-blue-600 hover:text-white"
  
          >
  
          <font-awesome-icon :icon="['fas', 'paper-plane']" />
          </button>
      </form>
          
      </div>
    </div>
  </main>


  <!--  -->
</template>
<script setup>
// import Hls from 'hls.js'

const route = useRoute()
const router = useRouter()
const {$axios,$setHeader,$pusher} = useNuxtApp()
const friend = ref({avatar:''})
const hash = ref('0123456789abcdef')
const messageList = ref([]) 
const scrollBottom = ref(null)
const {show_video} = getData()
const message = ref('')
const isTyping=ref(false)
const owner = ref({})
const typingMessage = ref('online')
const wait = ref(false)
const userMessage = ref('')
const messagePenging = useState('messagePenging',()=>false)
const xhr = new XMLHttpRequest();
// const value = ref('')
const {user} = getUserByServer(JSON.parse(localStorage.getItem('token')))

watch(user,(value)=>{
  owner.value = value
})
function scrollTobottom(){
  if (scrollBottom.value) {
    scrollBottom.value.scrollTop = scrollBottom.value.scrollHeight
  }
}

function sendTyping(typing){
  owner.value = user.value
  $axios.post('/api/typing',
  { 
    value:message.value,
    userId:owner.value.id,
    userName:owner.value.name,
    lastName:owner.value.last_name,
    typing:typing,
  },
  
)

}
const videoUrl = ref('')
const videoRef = ref(null)
const isPlay = ref(false)

const props = defineProps({
  videoId: String,
  error: Object

})

function playVideo(mess){
  const video = event.target.offsetParent.children[0]
  
  messageList.value.forEach(elem=>{
    elem.isPlay = false
    mess.isPlay = !mess.isPlay;
    if (elem.type == 'video' && elem.isPlay) {
      document.querySelectorAll('video').forEach(v=>v.pause());
      mess.isPlay ? video.play(): video.pause()
      setInterval(()=>{
    localStorage.setItem('time',video.currentTime)
    },1000)
    }
    
  })
  
}

function removeFile(file){
  event.stopPropagation()
  messageList.value = messageList.value.filter(mess=> mess.name !== file.name
  )
  if (file.type === 'video') {
    xhr.abort()
  }
}

function showVideo(mess){
  // router.push(`show-video-${mess.name}-${event.target.currentTime}`)
  router.push({
    query:{name:mess.name,time:event.target.currentTime}
  })
  event.target.pause()
  mess.isPlay=false
  show_video.value = true
  
  
}

$setHeader(JSON.parse(localStorage.getItem('token')))
const {responce} = getData()
const avatar = ref('')
const ownerData = ref()
const fileInput = ref(null)
const hasFile = ref(false)
const showFile =ref(false);

const open = (e)=>{

  if (e.target.dataset.name === 'picture') {
    document.querySelector('#sendfile').setAttribute('accept','image/*')

  }
  if(e.target.dataset.name==='video'){
    document.querySelector('#sendfile').setAttribute('accept','video/*')
  }
  if (e.target.id == 'file') {
    showFile.value=true
  }else{
      fileInput.value.click(); 
  }
  
}

function close(){
  document.querySelector('.animate').classList.add('hide')
  setTimeout(()=>{

    showFile.value = false
  },250)
}


const selectFile = ref(null)
const date = new Date()
const d = date.getDay() 
const h = date.getHours()
const m = date.getMinutes()
const addZero = zero=>zero <= 9 ? '0'+zero:zero;

const handleFileChange = async (event) => {
  close()

  const formdata = new FormData()
  const file = event.target.files[0];
  const reader = new FileReader();
  reader.onload = (e) => {
    const objFiles = {
    userId:parseInt(route.params.uid),
    isMyMessage:true,
    name:file.name,
    type:(file.type.includes('image')?'picture':'video'),
    isTemp:true,
    pending: watch(messagePenging,(v)=>{return v}),
    urlVideo:e.target.result,
    urlImage:e.target.result,
  }
  messageList.value.push(objFiles)
  console.log(objFiles);
  } 
  
  reader.readAsDataURL(event.target.files[0])
  
  formdata.append('userId',route.params.uid)
  formdata.append('friendId',route.params.fid)
  formdata.append('time',`${h}:${addZero(m)}`)
  let fileMessages = []
  
  let files = null;
  if (file && file.type.includes('image')) {
    files = file
    formdata.append('file',files)
    formdata.append('type','picture')
    
    messagePenging.value = true
    await fetch('http://127.0.0.1:8000/api/send-file', {
      method: 'POST',
      headers: {
        Authorization: 'Bearer ' + JSON.parse(localStorage.getItem('token')), 
      },
      body: formdata,
    }).then(r=>r.json())
    // .then(res=>{
    // })
    messagePenging.value = false
  }
  
  if (file && file.type.includes('video')) {
    files = file
    // console.log(formdata.get('time'));
    
  formdata.append('video', file);
  formdata.append('types', 'video');

  
  messagePenging.value =true
  xhr.open('POST', 'http://127.0.0.1:8000/api/send-video', true);

  xhr.setRequestHeader('Authorization', 'Bearer ' + JSON.parse(localStorage.getItem('token')));

  xhr.upload.onprogress = function(event) {
    if (event.lengthComputable) {
      const percent = (event.loaded / event.total) * 100;
    }
  };

  xhr.onload = function() {
    if (xhr.status === 200) {
      const res = JSON.parse(xhr.responseText);
      console.log('Файл загружен', res);
      close();
    } else {
      console.log('Ошибка при загрузке файла');
    }
  };
  xhr.upload.addEventListener('progress', function (e) {
    if (e.lengthComputable) {
      const progress = (e.loaded / e.total) * 100;
      console.log(`Загрузка: ${progress.toFixed(2)}%`);
      // Здесь можно обновить UI, например, прогресс-бар
      // updateProgressBar(progress);
    }
  });

  // Обработчик ошибок
  xhr.onerror = function() {
    console.error('Ошибка при отправке запроса');
  };

  // Отправляем запрос
  xhr.send(formdata);
  close()
  messagePenging.value=false
}

// function updateProgressBar(progress) {
//   const progressBar = document.getElementById('progress-bar'); // Пример
//   progressBar.style.width = `${progress}%`;
//   progressBar.textContent = `${progress.toFixed(2)}%`;
//   console.log(progress);
  
// }
  
};
import Pusher from 'pusher-js';

// Логируем Pusher в консоль
Pusher.logToConsole = false;

// Инициализируем Pusher
const pusher = new Pusher('local', {
  wsHost:'127.0.0.1',
  wsPort:'6001',
  forceTLS:false,
  disableStats:true,
  cluster: 'mt1',
  enabledTransports:['ws','wss']
});

// Подписываемся на канал
const channel = pusher.subscribe('your-channel'),
 fileChannel = pusher.subscribe('file'),
 typingChanel = pusher.subscribe('typing')

 const head = ref('')

 typingChanel.bind('userTyping',function(data){
  owner.value = user.value
  if (data.data.userId!==owner.value.id) {
    typingMessage.value = 
    data.data.typing ? 
    `${data.data.userName} печатает...`:'online'
    head.value = data.data.typing ? 
    `${data.data.userName[0]}. ${data.data.lastName[0]} печатает...`:
    friend.value.name + ' ' + friend.value.last_name
    
  }
  
  useHead({title:head.value?head.value:friend.value.name + ' ' + friend.value.last_name})
 })

fileChannel.bind('sendFile', function(data){
  watch(messageList.value,async ()=>{
  await nextTick()
  scrollTobottom()
})
  owner.value = user.value
  const currentUserId = owner.value.id
  const isMyMessage = currentUserId === data.data.user_id
  const idx = messageList.value.findIndex(m=>m.isTemp&&m.type ===data.data.type)

  if (idx !== -1) {
    messageList.value[idx] = {
      userId:data.data.user_id,
      isMyMessage,
      ...(data.data.type == 'message'?{text:data.data.text}:{name:data.data.text}),
      ...(data.data.type == 'video'?{isPlay:false}:''),
      avatar:data.data.avatar,
      type:data.data.type,
      time:data.data.time,
      urlVideo:`http://127.0.0.1:8000/api/filemessage/original/${data.data.text}`,
      urlImage:`http://127.0.0.1:8000/api/filemessage/${data.data.text}`
    }
  }else{
    messageList.value.push({
        userId:data.data.user_id,
        isMyMessage,
        ...(data.data.type == 'message'?{text:data.data.text}:{name:data.data.text}),
        ...(data.data.type == 'video'?{isPlay:false}:''),
        avatar:data.data.avatar,
        type:data.data.type,
        time:data.data.time,
        urlVideo:`http://127.0.0.1:8000/api/filemessage/original/${data.data.text}`,
        urlImage:`http://127.0.0.1:8000/api/filemessage/${data.data.text}`
      });
  }
    
    setTimeout(()=>{
      if (!isMyMessage) {
      playNotificationSound()
    }
    },200)
})
// Обрабатываем событие

// В случае подписки на канал
channel.bind('pusher:subscription_succeeded', function() {
});


let color = ref(''),
    color2 = ref(''),
    color3 =ref(''),
    color4 = ref('');

function showPicture(mess){
  if (mess.type == 'picture') {
    router.push(`/showpicture-${mess.name}`) 
  }
  
  

}
let s =0
function menuMessage(e){
  e.preventDefault()
  s++
  if (s == 1 ) {
    const menu = document.createElement('div');
    menu.innerHTML=`
    <div class="w-[160px] bg-[white] fixed top-[${e.target.pageY}px] left-[${e.target.pageX}px] h-[140px]">
      <ul class="p-[0] px-[10px] h-full">
        <li class="cursor-pointer">Изменить</li>    
        <li class="cursor-pointer">Удалить</li>    
        <li class="cursor-pointer">Выделить</li>    
      </ul>  
    </div>
    `
    e.target.append(menu)
    
  }
}
let unSendedMessage = JSON.parse(localStorage.getItem('unSendedMessage')) ?? []
//Здесь нужно timeout если скорость
// интернеть низкая сообшения отправляются 
// медленне и локльная хранилиша быстро очишается
if (window.navigator.onLine && unSendedMessage.length) {
      unSendedMessage.forEach(mess=>sendMessageWithFetch(mess))
      localStorage.setItem('unSendedMessage',JSON.stringify([]))
  }
  
function sendMessage(e){
  e.preventDefault()
  const msg = {
      type:'message',
      text:message.value,
      userId:owner.value.id,
      friendId:route.params.fid,
      isMyMessage:owner.value.id,
      pending: watch(messagePenging,(v)=>{return v}),
      isTemp:true}
  unSendedMessage.push(msg)
  unSendedMessage.forEach(mess=>messageList.value.push(mess))
  localStorage.setItem('unSendedMessage',JSON.stringify(unSendedMessage))
  unSendedMessage = []
  // if (window.navigator.onLine == false) {
  // }
  // messageList.value.push(msg)
  sendMessageWithFetch(message.value)
  message.value=''
  
}
async function sendMessageWithFetch(mess){
  if (mess.trim()) {
    messagePenging.value =true
       await fetch('http://127.0.0.1:8000/api/send-message', {
          method:'POST',
          headers:{
            "Content-Type":'application/json',
            Authorization:'Bearer '+JSON.parse(localStorage.getItem('token'))
          },
          body:JSON.stringify({
            text:mess,
            userId:owner.value.id,
            friendId:route.params.fid,
            type:'message',
            time:`${addZero(h)}:${addZero(m)}`
          })
        }).then(r=>r.json())
        .then(res=>{
          avatar.value = res.avatar
          ownerData.value = res
          localStorage.setItem('unSendedMessage',JSON.stringify([]))
        })
        messagePenging.value=false
    }
}

channel.bind('MyEvent', function(data) {
  watch(messageList.value,async ()=>{
  await nextTick()
  scrollTobottom()
})
  owner.value = user.value
  const currentUserId = owner.value.id
  const isMyMessage = currentUserId === data.data.user_id
  const idx = messageList.value.findIndex(m=>m.isTemp && m.text === data.data.text)
  
  if (idx !== -1) {
    messageList.value[idx] = {
      userId:data.data.user_id,
      ...(data.data.type == 'message'?{text:data.data.text}:{name:data.data.text}),
      isMyMessage,
      avatar:data.data.avatar,
      type:data.data.type,
      time:data.data.time
    }
  } else {
  messageList.value.push({
      userId:data.data.user_id,
      ...(data.data.type == 'message'?{text:data.data.text}:{name:data.data.text}),
      isMyMessage,
      avatar:data.data.avatar,
      type:data.data.type,
      time:data.data.time
    });
  }
  if (!isMyMessage) {
      playNotificationSound()
    }
  
});
function setColor(){
  for (let i = 0; i < 6; i++) {
  color.value+= hash.value[Math.floor(Math.random()*hash.value.length)]
  color2.value += hash.value[Math.floor(Math.random()*hash.value.length)]
  }
  return '#'+color.value
}

const playNotificationSound = () => {
  const audio = new Audio('/voice/notify.mp3')
  // audio.ontimeupdate=()=>audio.currentTime == audio.duration 
  // ?audio.pause():audio.play()
  audio.play().catch((e) => {
    console.warn('Ошибка воспроизведения звука:', e)
  })
}


onMounted(async ()=>{
  
  setTimeout(()=>{
    const el = document.querySelector('#chat-container')
    el.scrollTop += el.scrollHeight 
  },100)
  
  wait.value =true
 await fetch('http://127.0.0.1:8000/api/get-message',{
    method:'POST',
    headers:{
      "Content-Type":'application/json',
      Authorization:'Bearer '+JSON.parse(localStorage.getItem('token'))
    },
    body:JSON.stringify({
      owner:route.params.owner_id,
      friend:route.params.friends_id
    })
  })
  .then(r=>r.json())
  .then(res=>{
    friend.value = res.users.friend
    console.log(res);
    
    
    useHead({title:`${res.users.friend.name} ${res.users.friend.last_name}`})
    
    res.message.forEach(el => {
  const currentUserId = user.value.id 
  //owner.value.id
  const isMyMessage = currentUserId === el.user_id
  // console.log(currentUserId , el.user_id);
  
  messageList.value.push({
          userId:el.user_id,
          ...(el.type == 'message'?{text:el.message}:{name:el.message}),
          ...(el.type =='video')?{isPlay:false}:'',
          avatar:el.avatar.avatar,
          isMyMessage, 
          type: el.type=='message'?'message':el.type=='picture'?'picture':'video',
          time:el.time,
          urlVideo:`http://127.0.0.1:8000/api/filemessage/original/${el.message}`,
          urlImage:`http://127.0.0.1:8000/api/filemessage/${el.message}`
        })
        if (el.type=='video') {
                const { getQuality } = useNetworkSpeed()
          const quality = getQuality()
          
        }
      });

  })
  wait.value =false
 setTimeout(()=>{
 },100)
  
  
  setColor()

  
})

watch(message,async (value)=>{
  if (value) {
    sendTyping(true)
    isTyping.value=true

    setTimeout(()=>{
      isTyping.value=false
      sendTyping(false)
    },1000)
  }
  
})


</script>
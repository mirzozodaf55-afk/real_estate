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
      <div  class="flex justify-between items-center min-w-[50%] h-full absolute left-[20px]">
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
      class="space-y-2 w-full rounded my-[5px]" 
      v-for="(mess,i) of messageList" :key="i" @contextmenu="menuMessage">
        <div class="message flex items-start p-[2px] 
         rounded my-[10px] bg-[none]"
         :class="mess.isMyMessage?'justify-start flex-row-reverse':''">
          <div 
          v-if="mess.type =='message'"
          class="
           ml-2 text-[20px] 
           max-w-[80%]
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
            <p class="text-[13px] mb-[0] mt-[15px] ml-[10px]">{{ mess.time }}</p>
          </div>
           <div @click="showPicture(mess)" v-if="mess.type == 'picture'" class="
           ml-2 text-[20px] text-[white]
           max-w-[65%]
           mr-[5px]
           relative
           top-[15px]
           min-w-[50%]
           cursor-pointer
           min-h-[180px]
           bg-[white]
           h-[100px]
           shadow text-[black] p-[2px]
           overflow-hidden
           text-gray-700
           " 
           style="border-radius: 7px;"
           :style="mess.isMyMessage?`background: darkblue; color:white;border-top-right-radius: 0px;`:'border-bottom-left-radius: 0px;'"
           >
           <img  class="w-full h-full " 
           style="border-radius: 7px;"
           :style="mess.isMyMessage?`border-top-right-radius: 0px;`:'border-bottom-left-radius: 0px;'"
           :src="`http://127.0.0.1:8000/api/filemessage/${mess.name}`" :alt="mess.type"/>
           
           <p class="text-[13px] bg-[black]/50 absolute 
           bottom-[-5px] text-[white] px-[4px] rounded-[3px]
           right-[10px]">{{ mess.time }}</p>
          </div>

          
          <div @click="showPicture(mess)" v-if="mess.type == 'video'" class="
           ml-2 text-[20px] text-[white]
           max-w-[50%]
           mr-[5px]
           relative
           top-[15px]
           min-w-[65%]
           cursor-pointer
           h-[180px]
           bg-[white]
           h-[100px] 
           shadow text-[black] p-[2px]
           text-gray-700
           flex justify-center items-center
           " 
           style="border-radius: 7px;"
           :style="mess.isMyMessage?`background: orange; color:white;border-bottom-right-radius: 0px;`:'border-bottom-left-radius: 0px;'"
           >
           <video ref="videoRef" @click="showVideo(mess)"
           style="border-radius: 7px; object-fit: cover;"
           v-if="mess.type == 'video'"
           :style="mess.isMyMessage?`border-bottom-right-radius: 0px;`:'border-bottom-left-radius: 0px;'"
            :src="`http://127.0.0.1:8000/api/filemessage/original/${mess.name}`"
           class="w-full bg-[black]/80 h-full"
           >
           <div class="bg-[red] h-[30px]">{{ mess.time }}</div>
           <!--  -->
          </video> 
          <p class="text-[13px] bg-[black]/50 absolute 
          bottom-[-5px] text-[white] px-[4px] rounded-[3px]
          right-[10px]">{{ mess.time }}</p>
          <!-- <progress id="progress-bar"></progress> -->
          <div @click="playVideo(mess)" class="flex items-center 
          w-[30px] h-[30px] justify-center
          bg-[black]/50 border absolute rounded-[50%]">
              <font-awesome-icon data-name="video"
            style="pointer-events: none;"
        class="text-white bg-[none] text-[17px] absolute" 
        :icon="['fa', mess.isPlay?'pause':'play']" />
      </div>
        </div>
          </div>
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
          enctype="multipart/form-data"  class="flex space-x-2 w-full"  action=""><input
          v-model="message"
          type="text"
          placeholder="Type a message..."
          class="flex-1 p-2  outline-none rounded-md"
          />
          <input  
          type="file" @change.prevent="handleFileChange"
           style="display: none;" ref="fileInput" id="sendfile" >
          <button id="file"
          @click="open"
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
import Hls from 'hls.js'

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
const userMessage = ref('')
// const value = ref('')
const {user} = getUserByServer(JSON.parse(localStorage.getItem('token')))
console.log(JSON.parse(localStorage.getItem('token')));

watch(user,(value)=>{
  owner.value = value
})
function scrollTobottom(){
  if (scrollBottom.value) {
    scrollBottom.value.scrollTop = scrollBottom.value.scrollHeight
  }
}

function sendTyping(typing){
  $axios.post('/api/typing',
  { 
    value:message.value,
    userId:owner.value.id,
    userName:owner.value.name,
    typing:typing,
  },
)
}
const videoUrl = ref('')
const videoRef = ref(null)
const isPlay = ref(false)

const props = defineProps({
  videoId: String
})

function playVideo(mess){
  const video = event.target.offsetParent.children[0]
  
  
  messageList.value.forEach(elem=>{
    if (elem.type == 'video'&&elem.isPlay) {
      elem.isPlay=false
      mess.isPlay = !mess.isPlay;
      mess.isPlay || video.paused ? video.play(): video.pause()
      document.querySelectorAll('video').forEach(v=>v.pause());
      
    }
  })
  
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

  if (e.target.dataset.name==='picture') {
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
  const formdata = new FormData()
  const file = event.target.files[0];
  formdata.append('userId',route.params.owner_id)
  formdata.append('friendId',route.params.friends_id)
  formdata.append('time',`${h}:${addZero(m)}`)
  
  let files = null;
  if (file && file.type.includes('image')) {
    files = file
    formdata.append('file',files)
    formdata.append('type','picture')

    await fetch('http://127.0.0.1:8000/api/send-file', {
      method: 'POST',
      headers: {
        Authorization: 'Bearer ' + JSON.parse(localStorage.getItem('token')), 
      },
      body: formdata,
    }).then(r=>r.json())
    .then(res=>{
      console.log(formdata.get('time'));
      
      close()
  })
    
  }
  
  // if (file && file.type.includes('video')) {
  //   // files = null
  //   files = file
  //   formdata.append('video',files)
  //   formdata.append('types','video')

  //   await fetch('http://127.0.0.1:8000/api/send-video', {
  //     method: 'POST',
  //     headers: {
  //       Authorization: 'Bearer ' + JSON.parse(localStorage.getItem('token')), 
  //     },
  //     body: formdata,
  //   }).then(r=>r.json())
  //   .then(res=>{
      
  //     close()
  // })
  // }
  if (file && file.type.includes('video')) {
    files = file
    console.log(formdata.get('time'));
    
  // Создаем объект FormData
  // const formdata = new FormData();
  formdata.append('video', file);
  formdata.append('types', 'video');

  // Создаем новый XMLHttpRequest
  const xhr = new XMLHttpRequest();
  
  // Устанавливаем метод и URL для запроса
  xhr.open('POST', 'http://127.0.0.1:8000/api/send-video', true);

  // Устанавливаем заголовок авторизации
  xhr.setRequestHeader('Authorization', 'Bearer ' + JSON.parse(localStorage.getItem('token')));

  // Обработчик прогресса загрузки
  xhr.upload.onprogress = function(event) {
    if (event.lengthComputable) {
      const percent = (event.loaded / event.total) * 100;
      console.log(`Загрузка: ${percent.toFixed(2)}%`);

      // Можно обновить прогрессбар или вывести прогресс в интерфейсе
      // document.getElementById('progress-bar').style.width = `${percent}%`;
    }
  };

  // Обработчик успешной загрузки
  // xhr.onload = function() {
  //   if (xhr.status === 200) {
  //     const res = JSON.parse(xhr.responseText);
  //     console.log('Файл загружен', res);
  //     close();
  //   } else {
  //     console.log('Ошибка при загрузке файла');
  //   }
  // };
  xhr.upload.addEventListener('progress', function (e) {
    if (e.lengthComputable) {
      const progress = (e.loaded / e.total) * 100;
      console.log(`Загрузка: ${progress.toFixed(2)}%`);
      // Здесь можно обновить UI, например, прогресс-бар
      updateProgressBar(progress);
    }
  });

  // Обработчик ошибок
  xhr.onerror = function() {
    console.error('Ошибка при отправке запроса');
  };

  // Отправляем запрос
  xhr.send(formdata);
}

function updateProgressBar(progress) {
  const progressBar = document.getElementById('progress-bar'); // Пример
  progressBar.style.width = `${progress}%`;
  progressBar.textContent = `${progress.toFixed(2)}%`;
  console.log(progress);
  
}
  
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

 typingChanel.bind('userTyping',function(data){
  if (data.data.userId!==owner.value.id) {
    typingMessage.value = 
    data.data.typing?
    `${data.data.userName} печатает...`:'online'
    // userMessage.value = data.data.value
    
    // value.value = data.data.value
  }
  
 })


fileChannel.bind('sendFile', function(data){
  const currentUserId = owner.value.id
  const isMyMessage = currentUserId === data.data.user_id

  messageList.value.push({
      userId:data.data.user_id,
      isMyMessage,
      ...(data.data.type == 'message'?{text:data.data.text}:{name:data.data.text}),
      ...(data.data.type == 'video'?{isPlay:false}:''),
      avatar:data.data.avatar,
      type:data.data.type,
      time:data.data.time
    });
    
})
// Обрабатываем событие
channel.bind('MyEvent', function(data) {
  const currentUserId = owner.value.id
  const isMyMessage = currentUserId === data.data.user_id
    messageList.value.push({
      userId:data.data.user_id,
      ...(data.data.type == 'message'?{text:data.data.text}:{name:data.data.text}),
      isMyMessage,
      avatar:data.data.avatar,
      type:data.data.type,
      time:data.data.time
    });
    
});
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
function sendMessage(e){
  e.preventDefault()
  
    if (message.value.trim()) {
        fetch('http://127.0.0.1:8000/api/send-message',{
          method:'POST',
          headers:{
            "Content-Type":'application/json',
            Authorization:'Bearer '+JSON.parse(localStorage.getItem('token'))
          },
          body:JSON.stringify({
            text:message.value,
            userId:owner.value.id,
            friendId:route.params.friends_id,
            type:'message',
            time:`${addZero(h)}:${addZero(m)}`
          })
        }).then(r=>r.json())
        .then(res=>{
          // messageList.value.push(res)
          avatar.value = res.avatar
          ownerData.value = res
          
          
          
        })
    }
    message.value=''
}


function setColor(){
  for (let i = 0; i < 6; i++) {
  color.value+= hash.value[Math.floor(Math.random()*hash.value.length)]
  color2.value += hash.value[Math.floor(Math.random()*hash.value.length)]
  }
  return '#'+color.value
}


onMounted(()=>{
  
  setTimeout(()=>{
    const el = document.querySelector('#chat-container')
    el.scrollTop += el.scrollHeight 
  },100)
  
  fetch('http://127.0.0.1:8000/api/get-message',{
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
    
    
    res.message.forEach(el => {
  const currentUserId = user.value.id 
  //owner.value.id
  const isMyMessage = currentUserId === el.user_id
  console.log(currentUserId , el.user_id);
  
  messageList.value.push({
          userId:el.user_id,
          ...(el.type == 'message'?{text:el.message}:{name:el.message}),
          ...(el.type =='video')?{isPlay:false}:'',
          avatar:el.avatar.avatar,
          isMyMessage, 
          type: el.type=='message'?'message':el.type=='picture'?'picture':'video',
          time:el.time
        })
        if (el.type=='video') {
                const { getQuality } = useNetworkSpeed()
          const quality = getQuality()
          
        }
      });

  })
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

watch(messageList.value,async ()=>{
  await nextTick()
  scrollTobottom()
})
</script>
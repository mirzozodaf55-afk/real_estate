<style>
#chat-container::-webkit-scrollbar {
  display: none;
}

#chat-container {
  scroll-behavior: smooth;
}

.animate {
  animation: animate .2s ease;
}

@keyframes animate {
  0% {
    opacity: 0;
    transform: translateY(14px);
  }

  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

.hide {
  animation: hide .2s ease;
}

@keyframes hide {
  0% {
    opacity: 1;
    transform: translateY(0);
  }

  100% {
    opacity: 0;
    transform: translateY(14px);
  }
}
</style>
<template>
  <ShowVideo v-if="show_video" />
  <main v-if="!show_video" class="
  w-full h-[100vh]
  sm:h-[640px] sm:min-h-[550px] sm:max-h-[85vh]
  sm:w-[400px] sm:max-w-[92vw]
  mx-auto flex flex-col
  bg-white relative overflow-hidden
  sm:rounded-[20px] sm:border sm:border-[#e5e7eb] sm:shadow-[0_10px_40px_rgba(15,23,42,0.15)]">

    <!-- Header -->
    <header class="shrink-0 relative z-10 h-[60px] flex items-center gap-1 px-2 bg-white border-b border-[#eef0f4]">
      <button @click="navigateTo('/listfriend')"
        class="w-[36px] h-[36px] shrink-0 grid place-items-center rounded-full text-[#64748b] text-[18px] hover:bg-[#f1f5f9] transition-colors">
        <font-awesome-icon :icon="['fa-solid', 'fa-chevron-left']" />
      </button>

      <div v-if="!wait" class="flex items-center gap-[10px] min-w-0 flex-1 px-1">
        <div class="relative shrink-0">
          <div class="w-[42px] h-[42px] rounded-full border border-[#e5e7eb] bg-cover bg-center bg-[#f1f5f9]"
            :style="`background-image:url('${apiBase()}/api/avatar/${friend.avatar}');`"></div>
          <span v-if="typingMessage === 'online'"
            class="absolute bottom-0 right-0 w-[11px] h-[11px] rounded-full bg-[#22c55e] border-2 border-white"></span>
        </div>
        <div class="min-w-0 leading-tight">
          <h5 class="cursor-default truncate text-[15px] font-semibold text-[#152242] m-0">
            {{ friend.name && friend.last_name ? `${friend.name} ${friend.last_name}` : (friend.phone_number || friend.email || "") }}
            <span v-if="isStaff(friend)" data-test="staff-badge"
              class="ml-[4px] align-middle px-[7px] py-[1px] rounded-full bg-[#eef2ff] text-[#4338ca] text-[11px] font-semibold">
              <font-awesome-icon :icon="['fas', 'shield-halved']" class="text-[10px]" /> Администрация
            </span>
          </h5>
          <p class="text-[12.5px] truncate m-0" :class="typingMessage === 'online' ? 'text-[#22c55e]' : 'text-[#0468ff]'">
            {{ typingMessage }}
          </p>
        </div>
      </div>
      <div v-else class="flex-1"></div>

      <button
        class="w-[36px] h-[36px] shrink-0 grid place-items-center rounded-full text-[#64748b] hover:bg-[#f1f5f9] transition-colors">
        <font-awesome-icon :icon="['fas', 'ellipsis-vertical']" />
      </button>
    </header>

    <!-- Messages -->
    <div id="chat-container" ref="scrollBottom" class="flex-1 min-h-0 overflow-y-auto px-3 py-4"
      :style="{ background: wallpaper }">

      <div class="flex mb-2" :class="mess.isMyMessage ? 'justify-end' : 'justify-start'"
        v-for="(mess, i) of messageList" :key="i" @contextmenu="menuMessage">

        <!-- text bubble -->
        <div v-if="mess.type == 'message'" class="max-w-[75%] px-[14px] py-[8px] shadow-sm text-[15px] leading-snug"
          style="word-break: break-word;"
          :class="mess.isMyMessage
            ? 'bg-[#0468ff] text-white rounded-[16px] rounded-br-[4px]'
            : 'bg-white text-[#152242] rounded-[16px] rounded-bl-[4px]'">
          <p class="m-0">{{ mess.text }}</p>
          <div class="flex items-center justify-end gap-1 mt-[2px]">
            <font-awesome-icon v-if="mess.pending" :icon="['fas', 'spinner']" spin-pulse class="text-[10px]"
              :class="mess.isMyMessage ? 'text-white/70' : 'text-[#94a3b8]'" />
            <span v-else class="text-[10.5px]" :class="mess.isMyMessage ? 'text-white/70' : 'text-[#94a3b8]'">{{
              mess.time }}</span>
          </div>
        </div>

        <!-- picture bubble -->
        <div v-else-if="mess.type == 'picture'"
          class="relative w-[210px] max-w-[72%] aspect-[4/3] rounded-[16px] overflow-hidden shadow-sm bg-[#e2e8f0] cursor-pointer"
          :class="mess.isMyMessage ? 'rounded-br-[4px]' : 'rounded-bl-[4px]'">
          <img @click.stop="showPicture(mess)" class="w-full h-full object-cover" :src="mess.urlImage"
            :alt="mess.type" />

          <div v-if="mess.pending && !mess.failed" class="absolute inset-0 flex items-center justify-center bg-black/35">
            <div class="relative w-[44px] h-[44px] rounded-full grid place-items-center"
              :style="`background: conic-gradient(#fff ${(mess.progress || 0) * 3.6}deg, rgba(255,255,255,.28) 0deg)`">
              <div class="absolute inset-[3px] rounded-full bg-black/55 grid place-items-center">
                <span class="text-white text-[10.5px] font-semibold">{{ Math.round(mess.progress || 0) }}%</span>
              </div>
            </div>
          </div>

          <div v-if="mess.failed"
            class="absolute inset-0 flex flex-col items-center justify-center gap-1 bg-red-900/70 px-2 text-center">
            <font-awesome-icon class="text-white text-[18px]" :icon="['fas', 'triangle-exclamation']" />
            <span class="text-white text-[10.5px] leading-tight">{{ mess.errorText || 'Ошибка отправки' }}</span>
          </div>

          <button v-if="mess.pending" @click.stop="removeFile(mess)"
            class="absolute top-[6px] right-[6px] w-[22px] h-[22px] grid place-items-center rounded-full bg-black/55 text-white">
            <font-awesome-icon class="text-[10px]" :icon="['fa', 'xmark']" />
          </button>
          <span v-if="!mess.pending"
            class="absolute bottom-[6px] right-[8px] text-[11px] text-white bg-black/50 px-[6px] py-[1px] rounded-[10px]">{{
              mess.time }}</span>
        </div>

        <!-- video bubble -->
        <div v-else-if="mess.type == 'video'"
          class="relative w-[220px] max-w-[72%] aspect-video rounded-[16px] overflow-hidden shadow-sm bg-black cursor-pointer"
          :class="mess.isMyMessage ? 'rounded-br-[4px]' : 'rounded-bl-[4px]'">
          <video ref="videoRef" @click="showVideo(mess)" :src="mess.urlVideo" class="w-full h-full object-cover"></video>

          <div v-if="mess.pending && !mess.failed" class="absolute inset-0 flex items-center justify-center bg-black/40">
            <div class="relative w-[46px] h-[46px] rounded-full grid place-items-center"
              :style="`background: conic-gradient(#fff ${(mess.progress || 0) * 3.6}deg, rgba(255,255,255,.28) 0deg)`">
              <div class="absolute inset-[3px] rounded-full bg-black/55 grid place-items-center">
                <span class="text-white text-[10.5px] font-semibold">{{ Math.round(mess.progress || 0) }}%</span>
              </div>
            </div>
          </div>

          <div v-if="mess.failed"
            class="absolute inset-0 flex flex-col items-center justify-center gap-1 bg-red-900/70 px-2 text-center">
            <font-awesome-icon class="text-white text-[18px]" :icon="['fas', 'triangle-exclamation']" />
            <span class="text-white text-[10.5px] leading-tight">{{ mess.errorText || 'Не удалось отправить видео'
              }}</span>
          </div>

          <button v-if="mess.pending" @click.stop="removeFile(mess)"
            class="absolute top-[6px] right-[6px] w-[22px] h-[22px] grid place-items-center rounded-full bg-black/55 text-white">
            <font-awesome-icon class="text-[10px]" :icon="['fa', 'xmark']" />
          </button>

          <button v-if="!mess.pending" @click.stop="playVideo(mess)"
            class="absolute inset-0 m-auto w-[40px] h-[40px] grid place-items-center rounded-full bg-black/45 text-white">
            <font-awesome-icon :icon="['fas', mess.isPlay ? 'pause' : 'play']" />
          </button>
          <span v-if="!mess.pending"
            class="absolute bottom-[6px] right-[8px] text-[11px] text-white bg-black/50 px-[6px] py-[1px] rounded-[10px]">{{
              mess.time }}</span>
        </div>
      </div>
    </div>

    <!-- Attach / input -->
    <footer class="relative shrink-0 border-t border-[#eef0f4] bg-white px-2 py-2">
      <div v-if="showFile" class="animate absolute left-0 right-0 bottom-full bg-white
       border-t border-[#eef0f4] shadow-[0_-8px_28px_rgba(15,23,42,0.1)]
       rounded-t-[20px] px-4 pt-5 pb-7 flex items-start justify-center gap-10 z-30">
        <button @click="close"
          class="absolute top-[8px] right-[14px] text-[24px] leading-none text-[#94a3b8] hover:text-[#475569]">&times;</button>

        <button data-name="picture" @click.prevent="open" type="button" class="flex flex-col items-center gap-2">
          <span
            class="pointer-events-none w-[64px] h-[64px] rounded-[18px] grid place-items-center bg-[#eff6ff]">
            <font-awesome-icon class="pointer-events-none text-[26px] text-[#0468ff]" :icon="['fas', 'image']" />
          </span>
          <span class="pointer-events-none text-[12.5px] text-[#475569]">Фото</span>
        </button>

        <button @click.prevent="open" data-name="video" type="button" class="flex flex-col items-center gap-2">
          <span
            class="pointer-events-none w-[64px] h-[64px] rounded-[18px] grid place-items-center bg-[#eff6ff]">
            <font-awesome-icon class="pointer-events-none text-[26px] text-[#0468ff]" :icon="['fa', 'video']" />
          </span>
          <span class="pointer-events-none text-[12.5px] text-[#475569]">Видео</span>
        </button>
      </div>

      <form @submit.prevent="sendMessage" enctype="multipart/form-data" class="flex items-center gap-[6px]">
        <input type="file" @change.prevent="handleFileChange" style="display: none;" ref="fileInput" id="sendfile">

        <button id="file" type="button" @click.prevent="open"
          class="w-[38px] h-[38px] shrink-0 grid place-items-center rounded-full text-[#94a3b8] hover:bg-[#f1f5f9] hover:text-[#0468ff] transition-colors">
          <font-awesome-icon style="pointer-events: none;" :icon="['far', 'folder-closed']" />
        </button>

        <input v-model="message" type="text" placeholder="Написать сообщение..."
          class="flex-1 min-w-0 h-[38px] px-[14px] bg-[#f1f5f9] rounded-full outline-none text-[14.5px] text-[#152242]" />

        <button type="submit" :disabled="!message.trim()"
          class="w-[38px] h-[38px] shrink-0 grid place-items-center rounded-full text-white transition-colors"
          :class="message.trim() ? 'bg-[#0468ff] hover:bg-[#0353cc]' : 'bg-[#cbd5e1]'">
          <font-awesome-icon :icon="['fas', 'paper-plane']" class="text-[14px]" />
        </button>
      </form>
    </footer>
  </main>
</template>
<script setup>
// import Hls from 'hls.js'

const route = useRoute()
const router = useRouter()
const { $axios, $setHeader, $pusher } = useNuxtApp()
const { showNavBar } = useSwitch()
// Чат — полноэкранный вид на мобильном, поэтому прячем общий NavBar,
// иначе он добавляется поверх h-[100vh] чата и обрезает нижнюю часть (поле ввода).
// showNavBar — общий useState на всё приложение, поэтому обязательно возвращаем
// его обратно при уходе со страницы, иначе навбар пропадёт и на остальных страницах.
showNavBar.value = false
onUnmounted(() => {
  showNavBar.value = true
})
const friend = ref({ avatar: '' })
const hash = ref('0123456789abcdef')
const messageList = ref([])
const scrollBottom = ref(null)
const { show_video } = getData()
const message = ref('')
const isTyping = ref(false)
const owner = ref({})
const typingMessage = ref('online')
const wait = ref(false)
const userMessage = ref('')
const messagePending = useState('messagePending', () => false)
// const value = ref('')
const { user } = getUserByServer(getToken())

watch(user, (value) => {
  owner.value = value
})



function scrollTobottom() {
  if (scrollBottom.value) {
    scrollBottom.value.scrollTop = scrollBottom.value.scrollHeight
  }
}

function sendTyping(typing, msg) {
  owner.value = user.value
  $axios.post('/api/typing',
    {
      value: msg,
      userId: owner.value.id,
      friendId: route.params.fid,
      userName: owner.value.name,
      lastName: owner.value.last_name,
      typing: typing,
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

function playVideo(mess) {
  const video = event.target.offsetParent.children[0]

  messageList.value.forEach(elem => {
    elem.isPlay = false
    mess.isPlay = !mess.isPlay;
    if (elem.type == 'video' && elem.isPlay) {
      document.querySelectorAll('video').forEach(v => v.pause());
      mess.isPlay ? video.play() : video.pause()
      setInterval(() => {
        localStorage.setItem('time', video.currentTime)
      }, 1000)
    }

  })

}

function removeFile(file) {
  event.stopPropagation()
  if (file._xhr) {
    file._xhr.abort()
  }
  if (typeof file.urlVideo === 'string' && file.urlVideo.startsWith('blob:')) {
    URL.revokeObjectURL(file.urlVideo)
  }
  messageList.value = messageList.value.filter(mess => mess !== file)
}

function showVideo(mess) {
  // router.push(`show-video-${mess.name}-${event.target.currentTime}`)
  router.push({
    query: { name: mess.name, time: event.target.currentTime }
  })
  event.target.pause()
  mess.isPlay = false
  show_video.value = true


}

$setHeader(getToken())
const { responce } = getData()
const avatar = ref('')
const ownerData = ref()
const fileInput = ref(null)
const hasFile = ref(false)
const showFile = ref(false);
const isSendPhoto = ref(false)
const isSendvideo = ref(false)

const open = (e) => {

  if (e.target.dataset.name === 'picture') {
    document.querySelector('#sendfile').setAttribute('accept', 'image/*')
    // sendTyping(true, '')
    isSendPhoto.value = true

  } else if (e.target.dataset.name === 'video') {
    document.querySelector('#sendfile').setAttribute('accept', 'video/*')
    // sendTyping(true, '')
    isSendvideo.value = true

  }

  if (e.target.id == 'file') {
    showFile.value = true
  } else {
    fileInput.value.click();
  }
  }




function close() {
  const el = document.querySelector('.animate')
  if (el) el.classList.add('hide')
  setTimeout(() => {

    showFile.value = false
  }, 200)
}


const selectFile = ref(null)
const date = new Date()
const d = date.getDay()
const h = date.getHours()
const m = date.getMinutes()
const addZero = zero => zero <= 9 ? '0' + zero : zero;

// Свежее время на момент реальной отправки, а не на момент открытия чата
function currentTime() {
  const now = new Date()
  return `${addZero(now.getHours())}:${addZero(now.getMinutes())}`
}

// Загрузка файла с реактивным прогрессом (используется и для фото, и для видео),
// чтобы вместо console.log процент отображался прямо на пузыре сообщения.
function uploadWithProgress(url, formdata, tempMessage) {
  return new Promise((resolve, reject) => {
    const req = new XMLHttpRequest()
    tempMessage._xhr = req

    req.open('POST', url, true)
    req.setRequestHeader('Authorization', 'Bearer ' + getToken())

    req.upload.onprogress = (e) => {
      if (e.lengthComputable) {
        tempMessage.progress = Math.round((e.loaded / e.total) * 100)
      }
    }

    req.onload = () => {
      if (req.status >= 200 && req.status < 300) {
        let data = {}
        try { data = JSON.parse(req.responseText) } catch (e) { /* тело может быть пустым */ }
        resolve(data)
      } else {
        let msg = req.status === 413
          ? 'Файл слишком большой для отправки'
          : 'Не удалось отправить файл'
        try {
          const data = JSON.parse(req.responseText)
          if (data && data.message) msg = data.message
        } catch (e) { /* ответ не json (например html-страница ошибки) */ }
        reject(new Error(msg))
      }
    }

    req.onerror = () => reject(new Error('Ошибка сети при отправке файла'))
    req.onabort = () => reject(new Error('Отправка отменена'))

    req.send(formdata)
  })
}

const handleFileChange = async (event) => {
  const file = event.target.files[0]
  close()
  if (!file) return

  const isVideo = file.type.includes('video')
  const isImage = file.type.includes('image')
  if (!isVideo && !isImage) return

  // Превью через object URL вместо readAsDataURL: для видео на сотни мегабайт
  // синхронное base64-кодирование файла в память попросту подвесило бы вкладку.
  const previewUrl = URL.createObjectURL(file)

  const tempMessage = reactive({
    userId: parseInt(route.params.uid),
    isMyMessage: true,
    name: file.name,
    type: isVideo ? 'video' : 'picture',
    isTemp: true,
    pending: true,
    progress: 0,
    failed: false,
    errorText: '',
    isPlay: false,
    urlVideo: previewUrl,
    urlImage: previewUrl,
  })
  messageList.value.push(tempMessage)
  await nextTick()
  scrollTobottom()

  const formdata = new FormData()
  formdata.append('userId', route.params.uid)
  formdata.append('friendId', route.params.fid)
  formdata.append('time', currentTime())

  messagePending.value = true
  try {
    if (isImage) {
      formdata.append('file', file)
      formdata.append('type', 'picture')
      await uploadWithProgress(`${apiBase()}/api/send-file`, formdata, tempMessage)
    } else {
      formdata.append('video', file)
      formdata.append('types', 'video')
      await uploadWithProgress(`${apiBase()}/api/send-video`, formdata, tempMessage)
    }
  } catch (err) {
    tempMessage.failed = true
    tempMessage.errorText = err.message || 'Не удалось отправить файл'
  } finally {
    messagePending.value = false
  }
};
import Pusher from 'pusher-js';

// Логируем Pusher в консоль
Pusher.logToConsole = false;

// Инициализируем Pusher
const pusher = new Pusher('local', {
  wsHost: wsHost(),
  wsPort: '6001',
  forceTLS: false,
  disableStats: true,
  cluster: 'mt1',
  enabledTransports: ['ws', 'wss']
});

// Один канал на переписку (см. App\Support\ChatChannel): сообщения, файлы и «печатает…»
// приходят только участникам этого чата, а не всем открытым чатам
const chatChannelName = `chat.${Math.min(+route.params.uid, +route.params.fid)}.${Math.max(+route.params.uid, +route.params.fid)}`
const channel = pusher.subscribe(chatChannelName),
  fileChannel = channel,
  typingChanel = channel

// Пока чат открыт, сообщения этого собеседника не считаются непрочитанными
const { setActiveChat } = useNotifications()
onMounted(() => setActiveChat(route.params.fid))
onUnmounted(() => {
  setActiveChat(null)
  pusher.disconnect()
})

const head = ref('')

typingChanel.bind('userTyping', function (data) {
  owner.value = user.value
  if (data.data.userId !== owner.value.id) {
    typingMessage.value =
      data.data.typing ?
        `${data.data.userName} печатает...` : 'online'
    head.value = data.data.typing ?
      `${data.data.userName[0]}. ${data.data.lastName[0]} печатает...` :
      friend.value.name + ' ' + friend.value.last_name


      if (isSendPhoto.value) {
        typingMessage.value =
          `${data.data.userName} отправляет фото`
      head.value = data.data.typing ?
        `${data.data.userName[0]}. ${data.data.lastName[0]} отправляет фото` :
        friend.value.name + ' ' + friend.value.last_name
      }


      if (isSendvideo.value) {
        typingMessage.value =
          `${data.data.userName} отправляет видео`
      head.value = data.data.typing ?
        `${data.data.userName[0]}. ${data.data.lastName[0]} отправляет видео` :
        friend.value.name + ' ' + friend.value.last_name
      }

  }

  useHead({ title: head.value ? head.value : friend.value.name + ' ' + friend.value.last_name })
})

fileChannel.bind('sendFile', function (data) {
  watch(messageList.value, async () => {
    await nextTick()
    scrollTobottom()
  })
  owner.value = user.value
  const currentUserId = owner.value.id
  const isMyMessage = currentUserId === data.data.user_id
  const idx = messageList.value.findIndex(m => m.isTemp && m.type === data.data.type)

  if (idx !== -1) {
    if (typeof messageList.value[idx].urlVideo === 'string' && messageList.value[idx].urlVideo.startsWith('blob:')) {
      URL.revokeObjectURL(messageList.value[idx].urlVideo)
    }
    messageList.value[idx] = {
      userId: data.data.user_id,
      isMyMessage,
      ...(data.data.type == 'message' ? { text: data.data.text } : { name: data.data.text }),
      ...(data.data.type == 'video' ? { isPlay: false } : ''),
      avatar: data.data.avatar,
      type: data.data.type,
      time: data.data.time,
      urlVideo: `${apiBase()}/api/filemessage/original/${data.data.text}`,
      urlImage: `${apiBase()}/api/filemessage/${data.data.text}`
    }
  } else {
    messageList.value.push({
      userId: data.data.user_id,
      isMyMessage,
      ...(data.data.type == 'message' ? { text: data.data.text } : { name: data.data.text }),
      ...(data.data.type == 'video' ? { isPlay: false } : ''),
      avatar: data.data.avatar,
      type: data.data.type,
      time: data.data.time,
      urlVideo: `${apiBase()}/api/filemessage/original/${data.data.text}`,
      urlImage: `${apiBase()}/api/filemessage/${data.data.text}`
    });
  }

  setTimeout(() => {
    if (!isMyMessage) {
      playNotificationSound()
    }
  }, 200)
})
// Обрабатываем событие

// В случае подписки на канал
channel.bind('pusher:subscription_succeeded', function () {
});


let color = ref(''),
  color2 = ref(''),
  color3 = ref(''),
  color4 = ref('');

// Фон переписки: пастельный в светлой теме, спокойный тёмный — в тёмной (иначе яркое пятно посреди тёмного сайта)
const { theme } = useTheme()
// поверх — узор с домиками (public/patterns), тот же, что в ИИ-помощнике
const wallpaper = computed(() => theme.value === 'dark'
  ? "url('/patterns/doodles-dark.svg') 0 0 / 270px repeat, linear-gradient(160deg, #0f1a2e 0%, #17213a 100%)"
  : `url('/patterns/doodles-light.svg') 0 0 / 270px repeat, linear-gradient(160deg, #${color.value} 0%, #${color2.value} 100%)`)

// Подобранные вручную пастельные пары вместо полностью случайного hex —
// так фон гарантированно остаётся приятным и не спорит по контрасту с пузырями.
const wallpaperPalette = [
  ['e0f2fe', 'ede9fe'],
  ['dcfce7', 'e0f2fe'],
  ['fef9c3', 'fee2e2'],
  ['ede9fe', 'fce7f3'],
  ['e0e7ff', 'dbeafe'],
  ['fdf2f8', 'e0f2fe'],
]

function showPicture(mess) {
  if (mess.type == 'picture') {
    router.push(`/showpicture-${mess.name}`)
  }



}
let s = 0
function menuMessage(e) {
  e.preventDefault()
  s++
  if (s == 1) {
    const menu = document.createElement('div');
    menu.innerHTML = `
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

if (window.navigator.onLine && unSendedMessage.length) {
  unSendedMessage.forEach(mess => sendMessageWithFetch(mess))
  localStorage.setItem('unSendedMessage', JSON.stringify([]))
}

function sendMessage(e) {
  e.preventDefault()
  if (!message.value.trim()) return
  const msg = {
    type: 'message',
    text: message.value,
    userId: owner.value.id,
    friendId: route.params.fid,
    isMyMessage: owner.value.id,
    pending: watch(messagePending, (v) => { return v }),
    isTemp: true
  }
  unSendedMessage.push(msg)
  if (Array.isArray(unSendedMessage) && unSendedMessage.length) {
    unSendedMessage.forEach(mess => messageList.value.push(mess))
    localStorage.setItem('unSendedMessage', JSON.stringify(unSendedMessage))
    unSendedMessage = []
  }
  // if (window.navigator.onLine == false) {
  // }
  // messageList.value.push(msg)
  sendMessageWithFetch(message.value)
  message.value = ''
}

async function sendMessageWithFetch(mess) {
  if (mess.trim()) {
    messagePending.value = true
    await fetch(`${apiBase()}/api/send-message`, {
      method: 'POST',
      headers: {
        "Content-Type": 'application/json',
        Authorization: 'Bearer ' + getToken()
      },
      body: JSON.stringify({
        text: mess,
        userId: owner.value.id,
        friendId: route.params.fid,
        type: 'message',
        time: currentTime()
      })
    }).then(r => r.json()).then(res => {
      avatar.value = res.avatar
      ownerData.value = res
      localStorage.setItem('unSendedMessage', JSON.stringify([]))
    })
    messagePending.value = false
  }
}

channel.bind('MyEvent', function (data) {
  watch(messageList.value, async () => {
    await nextTick()
    scrollTobottom()
  })
  owner.value = user.value
  const currentUserId = owner.value.id
  const isMyMessage = currentUserId === data.data.user_id
  const idx = messageList.value.findIndex(m => m.isTemp && m.text === data.data.text)

  if (idx !== -1) {
    messageList.value[idx] = {
      userId: data.data.user_id,
      ...(data.data.type == 'message' ? { text: data.data.text } : { name: data.data.text }),
      isMyMessage,
      avatar: data.data.avatar,
      type: data.data.type,
      time: data.data.time
    }
  } else {
    messageList.value.push({
      userId: data.data.user_id,
      ...(data.data.type == 'message' ? { text: data.data.text } : { name: data.data.text }),
      isMyMessage,
      avatar: data.data.avatar,
      type: data.data.type,
      time: data.data.time
    });
  }
  if (!isMyMessage) {
    playNotificationSound()
  }

});
function setColor() {
  const pair = wallpaperPalette[Math.floor(Math.random() * wallpaperPalette.length)]
  color.value = pair[0]
  color2.value = pair[1]
  return '#' + color.value
}

const playNotificationSound = () => {
  const audio = new Audio('/voice/notify.mp3')
  // audio.ontimeupdate=()=>audio.currentTime == audio.duration
  // ?audio.pause():audio.play()
  audio.play().catch((e) => {
    console.warn('Ошибка воспроизведения звука:', e)
  })
}


onMounted(async () => {

  wait.value = true
  await fetch(`${apiBase()}/api/get-message`, {
    method: 'POST',
    headers: {
      "Content-Type": 'application/json',
      Authorization: 'Bearer ' + getToken()
    },
    body: JSON.stringify({
      owner: route.params.owner_id,
      friend: route.params.friends_id
    })
  })
    .then(r => r.json())
    .then(res => {
      friend.value = res.users.friend
      console.log(res);


      useHead({ title: `${res.users.friend.name} ${res.users.friend.last_name}` })

      res.message.forEach(el => {
        const currentUserId = user.value.id
        //owner.value.id
        const isMyMessage = currentUserId === el.user_id
        // console.log(currentUserId , el.user_id);

        messageList.value.push({
          userId: el.user_id,
          ...(el.type == 'message' ? { text: el.message } : { name: el.message }),
          ...(el.type == 'video') ? { isPlay: false } : '',
          avatar: el.avatar.avatar,
          isMyMessage,
          type: el.type == 'message' ? 'message' : el.type == 'picture' ? 'picture' : 'video',
          time: el.time,
          urlVideo: `${apiBase()}/api/filemessage/original/${el.message}`,
          urlImage: `${apiBase()}/api/filemessage/${el.message}`
        })
        if (el.type == 'video') {
          const { getQuality } = useNetworkSpeed()
          const quality = getQuality()
        }
      });

    })
  wait.value = false

  await nextTick()
  scrollTobottom()

  setColor()


})

watch(message, async (value) => {
  if (value) {
    sendTyping(true, message.value)
    isTyping.value = true

    setTimeout(() => {
      isTyping.value = false
      sendTyping(false, message.value)
    }, 1000)
  }

})


</script>

<script setup>
import { ref, onMounted } from 'vue'
import Hls from 'hls.js'

const route = useRoute()
const router = useRouter()
const { show_video } = getData()
const currentQuality = ref('360p')
const hlsInstance = ref(null)
const videoRef = ref(null)
const video = videoRef.value
const qualities = [
  { label: '144p', value: '144p' },
  { label: '360p', value: '360p' },
  { label: '480p', value: '480p' },
  { label: '720p', value: '720p' }
]
let hlsUrl = `http://127.0.0.1:8000/filemessage/hls/${route.query.name}_hls/master.m3u8`

function changeQuality(quality) {
  const video = videoRef.value
  const currentTime = video.currentTime
  const wasPlaying = !video.paused

  currentQuality.value = quality
  hlsUrl = `http://127.0.0.1:8000/filemessage/hls/${route.query.name}_hls/${currentQuality.value}.m3u8`

  if (hlsInstance.value) {
    hlsInstance.value.destroy()
    hlsInstance.value = null
  }

  if (Hls.isSupported()) {
    const hls = new Hls()
    hlsInstance.value = hls
    hls.loadSource(hlsUrl)
    hls.attachMedia(video)

    hls.on(Hls.Events.MANIFEST_PARSED, () => {
      video.currentTime = currentTime
      if (wasPlaying) {
        video.play()
      }
    })

    hls.on(Hls.Events.ERROR, (event, data) => {
      console.error('Ошибка HLS:', data)
    })
  } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
    video.src = hlsUrl
    video.addEventListener('loadedmetadata', () => {
      video.currentTime = currentTime
      if (wasPlaying) {
        video.play()
      }
    })
  } else {
    console.error('HLS не поддерживается в этом браузере.')
  }

  console.log(`Качество изменено на: ${currentQuality.value}`)
}

async function checkInternetSpeed() {
  const image = new Image()
  const imageSizeInBytes = 10000 // 50 KB
  const testUrl = `https://upload.wikimedia.org/wikipedia/commons/3/3f/Fronalpstock_big.jpg?cache=${Math.random()}`
  const startTime = Date.now()

  return new Promise(resolve => {
    image.onload = () => {
      const duration = (Date.now() - startTime) / 1000 // в секундах
      const bitsLoaded = imageSizeInBytes * 8
      const speedBps = bitsLoaded / duration
      const speedKbps = speedBps / 1024
      const speedMbps = speedKbps / 1024
      resolve(speedMbps)
    }

    image.onerror = () => resolve(0)

    image.src = testUrl
  })
}

async function autoSelectQuality() {
  const speed = await checkInternetSpeed()
  console.log(`Скорость интернета: ${speed.toFixed(2)} Mbps`)

  let selected = '144p'
  if (speed > 2) selected = '360p'
  if (speed > 4) selected = '480p'
  if (speed > 6) selected = '720p'
  video.play()
  changeQuality(selected)
  console.log(selected);
  
}
onMounted(() => {
  
  const video = videoRef.value


  
  // autoSelectQuality()
  setInterval(()=>{
  localStorage.setItem('time',video.currentTime)
  
 },1000)
  setTimeout(() => {
    document.querySelector('#video-container')?.requestFullscreen()
  }, 100)
})

function close() {
  show_video.value = false
  router.push({ query: {} })
}
const isPLay = ref(false)
function play(){
  // event.target.play()
  
}

function playAndPause(){
  isPLay.value=!isPLay.value
  isPLay.value?videoRef.value.pause():videoRef.value.play()

}
</script>

<template>
  <div id="video-container" class="w-full h-[600px] bg-black">
    <video
      ref="videoRef"
      class="w-full h-full"
      style="background-color: black"
    ></video>
    <button
      class="absolute top-2 right-2 bg-white p-1 rounded"
      @click="close"
    >
      ✕
    </button>
  </div>

  <div class="absolute bottom-2 left-2 bg-opacity-50 bg-black p-2 rounded">
      <button
        v-for="quality in qualities"
        :key="quality.value"
        @click="changeQuality(quality.value)"
        :class="currentQuality === quality.value?'bg-[skyblue] text-black':''"
        class="text-white p-1 m-1 bg-gray-700 rounded"
      >
        {{ quality.label }}
      </button>
      <button 
      class="text-white p-1 m-1 bg-gray-700 rounded"
      @click="playAndPause">{{ isPLay?'play':'pause' }}</button>
    </div>
</template>



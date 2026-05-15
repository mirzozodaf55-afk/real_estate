

<template>
  <div id="video-container" class="w-full h-[600px] bg-black">
    <video
      ref="videoRef"
      class="w-full h-full"
      autoplay
      style="background-color: black"
      :src="`http://127.0.0.1:8000/api/filemessage/original/${route.query.name}`"
    ></video>
    <button
      class="absolute top-2 right-2 bg-white p-1 rounded"
      @click="close"
    >
      ✕
    </button>
  </div>

  <!-- <div class="absolute bottom-2 left-2 bg-opacity-50 bg-black p-2 rounded">
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
    </div> -->
</template>


<script setup>
import { ref, onMounted } from 'vue'
import Hls from 'hls.js'

const route = useRoute()
const router = useRouter()
const { show_video } = getData()
const currentQuality = ref('360p')
const hlsInstance = ref(null)
const videoRef = ref(null)
// const video = videoRef.value
const qualities = [
  { label: '144p', value: '144p' },
  { label: '360p', value: '360p' },
  { label: '480p', value: '480p' },
  { label: '720p', value: '720p' }
]





const video = videoRef.value

onMounted(() => {
  let video = document.querySelector('video') 
  
  video.currentTime = JSON.parse(localStorage.getItem('time'))

  setInterval(()=>{

  console.log(localStorage.getItem('time'));
  
  
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



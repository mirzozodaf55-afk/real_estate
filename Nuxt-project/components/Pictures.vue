
<!-- :style="announData[0].objects == 'Квартира' || announData[0].objects !== 'Комната'? 'margin-top: 80px;' : ''" -->
<template>
  <form
    @change.prevent="saveImages"
    id="forms"
    enctype="multipart/form-data"
    class="picture-room-container"
  >
    <!-- <span>Фото и планировка - от 5 и больше</span> -->
    <div class="picture-room">
      <div>
        <font-awesome-icon style="color: darkgray" :icon="['fas', 'camera']" />
      </div>
      <p>Не добавляйте чужие фото, картинки с водяными знаками и рекламу.</p>
    </div>
    <div class="choose-picture" v-if="pictures.length < 12">
      <button
        @click.prevent="change"
        style="background-color: rgba(15, 72, 157, 0.1); color: #0468ff"
        class="btn w-100 fw-bold font-monospace"
      >
        Выберите файлы
      </button>
      <input type="file" max="12" multiple style="display: none" accept=".jpg, .jpeg, .png, .webp" id="input" />
    </div>
    <p v-for="(item,index) of size" :key="index" class="text-center text-[red] my-[1px] mx-[auto] text-[14px] mb-[0]">
        Не получилось загрузить {{item.name}}: Минимальный размер изображения - 800x400px.
      </p>
    <div
      v-if="pictures.length"
      class="flex overflow-hidden mt-[10px] w-[100%] min-h-[50px] rounded-[13px] border"
    >
      <div
        id="areaBorder"
        class="w-full picture relative  sm:mx-[auto] mx-[auto] flex flex-wrap"
        style="flex-basis: 100%"
        data-id="0"
      >
      
        <div
        v-for="(img, index) of pictures"
        :key="index"
          @dragend="handleDrop(img, pictures)"
          @dragleave.stop="handleDragLeave(pictures)"
          @dragenter.prevent="handleDragEnter(pictures)"
          @dragstart.stop="handleDragStart"
          @dragover.prevent
          @touchstart.stop="handleTouchStart"
          @touchend.stop ="handleTouchEnd(img, pictures)"
          draggable="true"
          id="images"
          
          class="bg-[black] relative flex responsive mx-[auto] m-[10px] w-[48%] h-[150px] sm:h-[170px] sm:w-[250px] sm:h-[170px] md:w-[200px] md:h-[120px] lg:min-w-[180px] lg:h-[120px] overflow-hidden rounded"
        >
        <div class="cursor-grab" >

          <div class="hover transition duration-150 cursor-default z-[2] absolute picture top-[-30px] w-full bg-[black]/50 h-[28px]">
                <div
                :data-id="index"
                    @click.stop="removeImg(img, pictures)"
                    class="cursor-pointer absolute overflow-hidden right-[10px] top-[4px] picture cursor-pointer text-[white] text-shadow text-[14px]"
                      >
                      <font-awesome-icon :icon="['fas', 'trash']" />
                </div>
            <div @click.stop="rotate(img)" :data-id="index" class="absolute picture left-[8px] cursor-pointer top-[2px] text-[white]">
              <font-awesome-icon :icon="['fas', 'rotate-right']" />
            </div>
          </div>
            <img
              :style="`transform: rotate(${img.rotation}deg); object-fit:cover;` "
              :data-id="index"
              class="picture left-[0] absolute text-[white] z-[1] top-[0] w-full h-full"
              :src="img.url?img.url:`http://127.0.0.1:8000/api/image/large/${img.pictures}`"
              :alt="img.url"
            />
        </div>
        </div>
      </div>
    </div>
  </form>
</template>
<script setup>
const props = defineProps({
  image:{
    type:Array,
    required:true
  }
})

function change(event) {
  const file = document.querySelector(".choose-picture > input");
  file.click();
}
const pictures = ref([]);
const {images, isUpload} = getData()
const formD = ref(new FormData());
const size = ref([])
function saveImages(e) {
  const files = e.target.files;
  for (let i = 0; i < files.length; i++) {
    const reader = new FileReader();
      reader.onload = (e) => {
        let img = new Image()
        img.src = e.target.result;
        img.onload = function (event) {
          if (this.width > 800 && this.height > 400) {
            formD.value.append("images[]", files[i]);
            pictures.value.push({
              url: e.target.result,
            position: pictures.value.length,
            rotation:0,
            file: files[i],
          });
          }else{
            size.value.push({
            name:files[i].name,
            width:this.width,
            height:this.height
          })
          }
        } 
      
        images.value = pictures.value;
      } 
          reader.readAsDataURL(files[i]);
    };

        setTimeout(() => {
          update_formdate();
        }, 50);

        
  }


  setTimeout(()=>{
  if (props.image.length) {
    pictures.value = props.image
    images.value = props.image
  }
},1000)
const update_formdate = () => {
  formD.value = new FormData()
  for (let i = 0; i < pictures.value.length; i++) {
      formD.value.append("images[]", pictures.value[i].file);
  }

};



function removeImg(img, picture) {
  picture.splice(picture.indexOf(img), 1);
  size.value = []
}

let currentIndex = null;
let enterIndex = null;
function handleDragStart(e) {
  currentIndex = parseInt(e.target.dataset.id);
  e.target.offsetParent.classList.add('dragging')
}

function handleDragEnter(picture) {
  enterIndex = parseInt(event.target.dataset.id);
  
}
// function handleDragLeave(picture) {
  
// }
let startIndex = null
function handleTouchStart(e) {
  document.body.style.overflow='hidden'
  const touch = e.touches[0];
  currentIndex = parseInt(touch.target.dataset.id);
  const element = document.elementFromPoint(touch.clientX, touch.clientY);
  const targetPhotoId1 = element.closest(".picture").dataset.id;
  startIndex = targetPhotoId1 
}

function handleDrop(img,picture) {
  const draggImage = picture[enterIndex];
  if (currentIndex !== null) {
    picture[enterIndex] =  picture[currentIndex]
    picture[currentIndex] = draggImage
    picture.forEach((item,i)=>{
      item.position = i
    })
    event.target.offsetParent.classList.remove('dragging')
    currentIndex = null;
    enterIndex = null;
  }
}
function handleDragLeave(){
  return
}

function handleTouchEnd(img, picture) {
  document.body.style.overflow = '';
  const touches = event.changedTouches[0];
  const element = document.elementFromPoint(touches.clientX, touches.clientY);
  let targetPhotoId = null;

  // Проверка, что найденный элемент или его родители имеют класс "picture"
  let currentElement = element;
  while (currentElement) {
    if (currentElement.classList.contains("picture")) {
      targetPhotoId = currentElement.dataset.id;
      break;
    }
    currentElement = currentElement.parentElement;
  }

  if (targetPhotoId !== null) {
    const saveElems = picture[startIndex];
    picture[startIndex] = picture[targetPhotoId];
    picture[targetPhotoId] = saveElems;
    startIndex = null;
    picture.forEach((item, index) => {
      item.position = index;
    });
  }
}


function rotate(img){
  if (img) {
    img.rotation += 90
  const images = document.querySelectorAll('img')
  images.forEach(image=>{
    if(image.src === img.url){
      
    }
    
  })
  if (img.rotation >= 360) {
    img.rotation = 0
  }
  }
}

  // if (isUpload.value) {
  //   pictures.value = images.value
  // }else{
  //   images.value = pictures.value
  // }

</script>

<style scoped>
.hover {
  transition: all .3s;
}
.dragging{
  opacity: 0;
  border-radius: 6px;
}
#images:hover .hover{
  top: 0;
}
.lds-spinner {
  color: official;
  display: inline-block;
  position: relative;
  width: 80px;
  height: 80px;
}
.lds-spinner div {
  transform-origin: 40px 40px;
  animation: lds-spinner 1.2s linear infinite;
}
.lds-spinner div:after {
  content: " ";
  display: block;
  position: absolute;
  top: 3px;
  left: 37px;
  width: 6px;
  height: 18px;
  border-radius: 20%;
  background: #006cfd;
}
.lds-spinner div:nth-child(1) {
  transform: rotate(0deg);
  animation-delay: -1.1s;
}
.lds-spinner div:nth-child(2) {
  transform: rotate(30deg);
  animation-delay: -1s;
}
.lds-spinner div:nth-child(3) {
  transform: rotate(60deg);
  animation-delay: -0.9s;
}
.lds-spinner div:nth-child(4) {
  transform: rotate(90deg);
  animation-delay: -0.8s;
}
.lds-spinner div:nth-child(5) {
  transform: rotate(120deg);
  animation-delay: -0.7s;
}
.lds-spinner div:nth-child(6) {
  transform: rotate(150deg);
  animation-delay: -0.6s;
}
.lds-spinner div:nth-child(7) {
  transform: rotate(180deg);
  animation-delay: -0.5s;
}
.lds-spinner div:nth-child(8) {
  transform: rotate(210deg);
  animation-delay: -0.4s;
}
.lds-spinner div:nth-child(9) {
  transform: rotate(240deg);
  animation-delay: -0.3s;
}
.lds-spinner div:nth-child(10) {
  transform: rotate(270deg);
  animation-delay: -0.2s;
}
.lds-spinner div:nth-child(11) {
  transform: rotate(300deg);
  animation-delay: -0.1s;
}
.lds-spinner div:nth-child(12) {
  transform: rotate(330deg);
  animation-delay: 0s;
}
@keyframes lds-spinner {
  0% {
    opacity: 1;
  }
  100% {
    opacity: 0;
  }
}
@media screen and (max-width: 420px) {
  .responsive {
    width: 100%;
    height: 200px;
  }
}
@media screen and (min-width: 320px) {
  .picture-room-container {
    display: flex;
    width: 100%;
    flex-direction: column;
  }
  .picture-room-container > span {
    font-size: 16px;
    font-family: lato, sans-serif;
    letter-spacing: -0.2;
    font-weight: 700;
    line-height: 24px;
  }
  .picture-room {
    display: flex;
    align-items: center;
  }
  .picture-room > p {
    margin-top: 10px;
    width: 35%;
    margin-left: 15px;
    font-size: 12px;
    height: max-content;
  }
}

@media screen and (min-width: 640px) {
  .picture-room-container {
    display: flex;
    width: 100%;
    flex-direction: column;
  }
  .picture-room-container > span {
    font-size: 16px;
    font-family: lato, sans-serif;
    letter-spacing: -0.2;
    font-weight: 700;
    line-height: 24px;
  }
  .picture-room {
    display: flex;
    align-items: center;
  }
  .picture-room > p {
    margin-top: 10px;
    width: 35%;
    margin-left: 15px;
    font-size: 12px;
    height: max-content;
  }
}
</style>
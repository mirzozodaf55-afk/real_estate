
<template>
    <main class="layouts">
         <div class="container-xl mt-4">
             <div class="progress-container">
                 <div class="d-flex justify-content-between mb-2 mt-3">
                     <span class="fw-bold pt-1 2">Ссылка Видео</span>
                     <button class="btn btn-none fs-5 p-0 m-0 text-primary me-2">
                         <font-awesome-icon :icon="['fas', 'circle-question']" />
                     </button>
                 </div>

                 <div class="progress">
                     <div class="progress-bar" role="progressbar" style="width:30%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                 </div>
             </div>
        <div class="rounded-[15px] mx-[auto] my-[10px] sm:max-w-[95%] min-w-[100%]  lg:max-w-[95%] lg:min-w-[70%] lg:shadow-2xl">
            <div  class="mx-[20px] mt-[10px] h-[auto]">
                <div style="margin-top:10px;" v-if="announData.objects == 'Квартира' || announData.objects == 'Комната' || announData.objects =='Квартира в Новостройке'" class="text-container">
                    <span>Параметры Квартиры </span>
                </div>
             <div v-if="announData.objects == 'Квартира' || announData.objects == 'Комната' || announData.objects =='Квартира в Новостройке'" class="quantity-room-container">
                 <p>Количество Комнат</p>
                 <div class="quantity-room">
                     <label class="quantity-room-item" for="1">
                         <input :checked="announData.quantityRoom == 1" @change="quantityRoom" data-name="1" name="radioInput" type="radio" id="1">
                         <span :style="!announData.quantityRoom ?'border:1px solid red; color:red;':announData.quantityRoom == 1 ?'border-color:green;background:white;color:green;':''" class="form-control" >1</span>
                     </label>
                     <label class="quantity-room-item" for="2">
                         <input :checked="announData.quantityRoom == 2" @change="quantityRoom" data-name="2" name="radioInput" type="radio"  id="2">
                         <span :style="!announData.quantityRoom ?'border:1px solid red; color:red;':announData.quantityRoom == 2 ?'border-color:green;background:white;color:green;':''" class="form-control" >2</span>
                     </label>
                     <label class="quantity-room-item" for="3">
                         <input :checked="announData.quantityRoom == 3" @change="quantityRoom" data-name="3" name="radioInput" type="radio"  id="3">
                         <span :style="!announData.quantityRoom ?'border:1px solid red; color:red;':announData.quantityRoom == 3 ?'border-color:green;background:white;color:green;':''" class="form-control" >3</span>
                     </label>
                     <label class="quantity-room-item" for="4">
                         <input :checked="announData.quantityRoom == 4" @change="quantityRoom" data-name="4" name="radioInput" type="radio"  id="4">
                         <span :style="!announData.quantityRoom ?'border:1px solid red; color:red;':announData.quantityRoom == 4 ?'border-color:green;background:white;color:green;':''" class="form-control" >4</span>
                     </label>
                     <label class="quantity-room-item" for="5">
                         <input :checked="announData.quantityRoom == 5" @change="quantityRoom" data-name="5" name="radioInput" type="radio"  id="5">
                         <span :style="!announData.quantityRoom ?'border:1px solid red; color:red;':announData.quantityRoom == 5 ?'border-color:green;background:white;color:green;':''" class="form-control" >5</span>
                     </label>
                 </div>
             </div>

             <div v-if="announData.objects == 'Квартира' || announData.objects == 'Комната' || announData.objects =='Квартира в Новостройке'" class="area-room-container">
                 <div class="area-room">
                    <div class="room">
                     <p>  Общая площадь</p>
                     <div style="flex-direction:column;" class="relative min-w-[200px]">

                        <div :style="!announData.generalArea || mooreArea?'border:1px solid red; color:red;':'border-color:green;color:green'" class="input-area form-control">
                            <input @input="verfied" v-model="announData.generalArea" type="text" v-maska
                                data-maska="####">
                            <div>м<sup class="text-[10px]">2</sup></div>
                        </div>
                    <p style="font-size:11px;" 
                    v-if="mooreArea"
                    class="
                    overflow-hidden
                    whitespace-nowrap
                    leading-[15px] text-[red] font-normal lg:relative md:relative md:left-0 absolute sm:relative sm:left-0 top-[4px] lg:left-0 left-[130px]">Общая площадь должна быть 
                         <br>больше жилой + кухни
                     </p>
                     </div>
                    </div> 
                     

                    <div v-if="announData.objects == 'Квартира' || announData.objects == 'Комната' || announData.objects =='Квартира в Новостройке'" class="room">
                     
                     <p>Жилая площадь</p>
                     <div :style="!announData.liveArea || mooreArea ?'border:1px solid red;color:red':'border-color:green;color:green'" class="input-area form-control">
                         <input @input="verfied" v-model="announData.liveArea" type="text" v-maska data-maska="####">
                         <div>м<sup class="text-[10px]">2</sup></div>
                     </div>
                    </div>
                 </div>
             </div>
             <div v-if="announData.objects == 'Квартира' || announData.objects == 'Комната' || announData.objects =='Квартира в Новостройке'" class="area-room-container mt-5">
                 <div class="area-room">
                     <div class="room">
                     <p>Кухня</p>
                     <div :style="!announData.kitchenArea || mooreArea ?'border:1px solid red; color:red;':'border-color:green; color:green'" class="input-area form-control">
                         <input @input="verfied" v-model="announData.kitchenArea" type="text" v-maska data-maska="####">
                         <div>м<sup class="text-[10px]">2</sup></div>
                     </div>
                    </div>
                 </div>
             </div>


             <div class="video-link-container">
                 <div style="margin-top:24px;">
                     <div style="display:inline;">
                         <span>Видео</span>
                     </div>
                 </div>
             </div>
             <div class="container-input-link">
                 <div style="flex-basis:280px; margin-top:16px">
                     <label for="link-video">
                         Ссылка на Youtube
                     </label>
                     <textarea v-model="announData.linkvideo" class="font-monospace"  id="link-video" rows="1" placeholder="https://"></textarea>
                 </div>
             </div>
             <div class="container mt-4 mb-2 d-flex justify-content-end">
                <next-btn class="btn btn-light text-primary px-4 mx-4" @click.prevent="prew">Назад</next-btn>    
                <next-btn class="btn btn-primary px-4" @click.prevent="next" >Далее</next-btn>
             </div>
             
         </div>
         </div>
        </div>
         
        </main>
</template>

<script setup>


const {announData} = getData()
announData.value = JSON.parse(localStorage.getItem('announ')) || {}

const mooreArea = ref(false)
const quantityRooms = ref(0)
const generalArea = ref('')
const liveArea = ref('')
const kitchenArea = ref('')
const videoLink = ref('')


function quantityRoom (event){
 quantityRooms.value = announData.value.quantityRoom = parseInt(event.target.dataset.name)
 localStorage.setItem('announ',JSON.stringify(announData.value))
}

function verfied(){
    if (announData.value.liveArea) {
        announData.value.liveArea = parseInt(announData.value.liveArea);
    }
    if (announData.value.generalArea) {
        announData.value.generalArea = parseInt(announData.value.generalArea);
    }
    if (announData.value.kitchenArea) {
        announData.value.kitchenArea = parseInt(announData.value.kitchenArea);    
    }
        
        if (
            parseInt(announData.value.liveArea) 
            + parseInt(announData.value.kitchenArea) 
            > parseInt(announData.value.generalArea)
            || parseInt(announData.value.generalArea) 
            < parseInt(announData.value.kitchenArea)
            || parseInt(announData.value.liveArea) 
            > parseInt(announData.value.generalArea) 
            ) {
            mooreArea.value = true
        }else {
            mooreArea.value = false
        }
        localStorage.setItem('announ', JSON.stringify(announData.value))    
    }

function next(){
 if (announData.value.objects == 'Участок') {
    navigateTo('/technicsandfurniture')
 }
 if (announData.value.objects == 'Дом/Дача' || announData.value.objects == 'Коттедж'|| announData.value.objects == 'Часть дома'||announData.value.objects == 'Дом') {
    navigateTo('/technicsandfurniture')
    
 }
 if (announData.value.objects == 'Квартира'||announData.value.objects == 'Комната'||announData.value.objects == 'Квартира в Новостройке') {
     if (
        !announData.value.liveArea 
        ||
        !announData.value.kitchenArea
        ||
        !announData.value.generalArea
        ||
        announData.value.liveArea + announData.value.kitchenArea > announData.value.generalArea
        || !announData.value.quantityRoom
           ) {
            console.log(announData.value.objects);
        navigateTo('/pictures')
     }else{
        localStorage.setItem('announ', JSON.stringify(announData.value))
        navigateTo('/feature')
     }
 }
}
function prew(){
 navigateTo('/map')
}

const router = useRouter()
onMounted(()=>{
})


</script>

<style scoped>
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
-webkit-appearance: none;
margin: 0;
}
@media screen and (min-width:320px) {
    
 .progress{
     height: 7px;
     background-color: rgba(138, 187, 218, 0.24);
 }

 .progress-bar{
     background-image: linear-gradient(to right, #ffe1ff ,#006cfd 99.9%,#006cfd );
     border-top-right-radius: 5px;
     border-bottom-right-radius: 5px;

 }

 .text-container > span{
     font-weight: 700;
     font-size: 16px;
 }
 
 .quantity-room-container{
     width: 100%;
     margin-top: 32px;
 }
 .quantity-room-container > p{
     font-size: 14px;
 }
 .quantity-room{
     display: flex;
     width: 35%;
     /* max-width: 50%; */
     /* width: 43%; */
     justify-content: space-between;
     background-color: #fff;
 }
 .quantity-room >label > input{
     display: none;
 }

 .quantity-room >label > input:checked + span{
     border-color: #006cfd;
     background-color: rgba(192, 192, 192, 0.39);
 }
 .quantity-room-item{
     margin-right: 10px;
 }

 .area-room-container{
     margin-top: 32px;
     width: 100%;
     height: 90px;
 }
 .area-room{
     display: flex;
     width: 80%;
     height: 100%;
     flex-direction: column;
     justify-content: space-between;
 }

.room > p{
 font-size: 14px;
 margin-bottom: 5px;
 }

.input-area{
 display: flex;
 max-width: 45%;
 width: 120px;
 min-width: 120px;
 justify-content: space-between;
}

 .input-area > input{
     border: none;
     outline: none;
     width: 85px;

 }
 .input-area > button{
     border: none;
     outline: none;
     background: none;
 }
 .input-area > button > sup{
     font-size: 10px;
 }

 
 .video-link-container{
     display: flex;
     min-width: 100%;
 }
 .video-link-container  span{
     letter-spacing: -0.2px;
     font-weight: 700;
     line-height: 24px;
     font-size: 16px;
     font-style: normal;
     font-stretch: normal;
 }
 .container-input-link{
     display: flex;
     max-width: 100%;

 }
 .container-input-link label{
     display: block;
     margin-bottom: 6px;
     color: #152242;
     font-size: 14px;
     line-height: 20px;
 }
 .container-input-link textarea{
     height: 40px;
     overflow: hidden;
     color: #152242;
     padding: 8px 12px;
     font-size: 16px;
     font-family: monospace;
     line-height: 24px;
     border: 1px solid #ced1d7;
     outline: none;
     width: 100%;
     resize: none;
     border-radius: 4px;
 }
 .container-sm{
     margin-top: 20px;
 }
}

@media screen and (min-width:640px){
 .container-btn{
         display: flex;
         justify-content: flex-end;
     }
 .layouts{
     display: flex;
     flex-direction: column;
     margin: 0;
     justify-content: center;
     font-family: Lato, sans-serif;
 }
 .container-xl{
     height: max-content;
     display: flex;
     flex-direction: column;
 }
 .progress{
     height: 7px;
     background-color: rgba(138, 187, 218, 0.24);
 }
 /* .progress-container{
     padding: 0 20px;
 } */
 .progress-bar{
     background-image: linear-gradient(to right, #ffe1ff ,#006cfd 99.9%,#006cfd );
     border-top-right-radius: 5px;
     border-bottom-right-radius: 5px;

 }

 
 .text-container > span{
     font-weight: 700;
 }
 
 .quantity-room-container{
     width: 100%;
     margin-top: 32px;
 }
 .quantity-room-container > p{
     font-size: 14px;
 }
 .quantity-room{
     display: flex;
     max-width: 100px;
     /* justify-content: space-between; */
     background-color: #fff;
 }
 .quantity-room > label > input{
     display: none;
 }

 .quantity-room >label > input:checked + span{
     border-color: #006cfd;
     background-color: rgba(192, 192, 192, 0.39);
 }
 

 .area-room-container{
     margin-top: 32px;
     width: 100%;
     height: 90px;
 }
 .area-room{
     display: flex;
     width: 80%;
     height: 100%;
     flex-direction: row;
     justify-content: space-between;
 }

.room > p{
 font-size: 14px;
 margin-bottom: 5px;
 }

.input-area{
 display: flex;
 width: 50%;
 justify-content: space-between;
}

 .input-area > input{
     border: none;
     outline: none;
     width: 70%;

 }
 .input-area > button{
     border: none;
     outline: none;
     background: none;
 }
 .input-area > button > sup{
     font-size: 10px;
 }

 /* .picture-room-container{
     display: flex;
     width: 100%;
     flex-direction: column;
 }
 .picture-room-container > span{
     font-size: 16px;
     font-family: lato, sans-serif;
     letter-spacing: -0.2;
     font-weight: 700;
     line-height: 24px;
 }
 .picture-room{
     display: flex;
     align-items: center;
 }
 .picture-room > p {
     margin-top: 10px;
     width: 35%;
     margin-left: 15px;
     font-size: 12px;
     height: max-content;
 } */
 .video-link-container{
     display: flex;
     min-width: 100%;
 }
 .video-link-container  span{
     letter-spacing: -0.2px;
     font-weight: 700;
     line-height: 24px;
     font-size: 16px;
     font-style: normal;
     font-stretch: normal;
 }
 .container-input-link{
     display: flex;
     max-width: 100%;

 }
 .container-input-link label{
     display: block;
     margin-bottom: 6px;
     color: #152242;
     font-size: 14px;
     line-height: 20px;
 }
 .container-input-link textarea{
     height: 40px;
     overflow: hidden;
     color: #152242;
     padding: 8px 12px;
     font-size: 16px;
     font-family: monospace;
     line-height: 24px;
     border: 1px solid #ced1d7;
     outline: none;
     width: 100%;
     resize: none;
     border-radius: 4px;
 }
}

 .progress{
     height: 7px;
 }
 .progress-bar{
     background-image: linear-gradient(to right, #ffe1ff ,#006cfd 99.9%,#006cfd );
     border-top-right-radius: 5px;
     border-bottom-right-radius: 5px;

 }
 .progress-container{
     padding: 0 20px;
 }
@media screen and (min-width: 1024px) {
 .progress-container{
     width: 30%;
     padding: 15px;
 }
 .progress{
     height: 7px;
 }
     .container-xl{
     display: flex;
     max-width: 1300px;
     flex-direction: row;
     justify-content: space-between;
 }
 .justify-content-between > span{
     font-size: 14px;
 }
 .justify-content-between > button{
     display: none;
 }

  .container-sm{
     width: 67%;
     height: max-content;
     margin-top: 10px;
     margin-bottom: 20px;
     box-shadow: 0 0 30px rgba(0, 0, 0, 0.16);
     padding: 20px 35px;
     border-radius: 8px;
 }


 .quantity-room-container{
     width: 100%;
     margin-top: 32px;
 }
 .quantity-room-container > p{
     font-size: 14px;
 }
 .quantity-room{
     display: flex;
     /* min-width: 10%; */
     justify-content: space-between;
     background-color: #fff;
 }
 .quantity-room > label > input{
     display: none;
 }

 .quantity-room >label > input:checked + span{
     border-color: #006cfd;
     background-color: rgba(192, 192, 192, 0.39);
 }
 .quantity-room-item{
     margin-left: 0;
 }
 
}



</style>
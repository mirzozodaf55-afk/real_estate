<template>
    <form class="layouts">
         <div class="container-xl mt-4">
             <div class="progress-container container">
                 <div class="d-flex justify-content-between mb-2 mt-2">
                     <span class="fw-bold pt-1">Особенности</span>
                     <button class="btn btn-none fs-5 p-0 m-0 text-primary me-2">
                         <font-awesome-icon :icon="['fas', 'circle-question']" />
                     </button>
                 </div>
                 <div class="progress">
                     <div class="progress-bar" role="progressbar" style="width:50%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                 </div>
             </div>

             <div class="container-sm">
                 <div class="text-container " style="font-weight:normal; color:#152242;">
                     <span>Особенности квартиры </span>
                 </div>
                 <div class="container w-100">
                     <div class="col mt-4" style="color:#152242;">
                         <div class="row">
                             <div class="contrainer-balconies">
                                 <div class="balcon w-25 flex flex-col">
                                     <label for="#balcon" class="mb-2 fw-bold" style="font-family:lato, sans-seif">Балкон</label>
                                     
                                     <div class="dropdown">
                                             <button style="font-size: 15px; box-shadow: none;" :style="!announData.balcon  ? 'border-color:red; color:red;':'border-color:green; color:green;'" class="w-[100px] form-select text-left" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                 {{  announData.balcon?announData.balcon:'Балкон' }}
                                             </button>
                                             <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                 <li :style="announData.balcon == 'есть' ? 'color:white; background-color:#0468FF;':''" @click="isBalcon" class="dropdown-item">есть</li>
                                                 <li :style="announData.balcon == 'нет' ? 'color:white; background-color:#0468FF;':''" @click="isBalcon" class="dropdown-item">нет</li>
                                             </ul>
                                         </div>
                                     
                                 </div>
                             </div>
                         </div>


                         <div class="row mt-2">
                             <div class="contrainer-balconies">
                                 <div class="balcon">
                                     <p for="#balcon" class="mb-1 mt-2 fw-bold" style="font-family:lato, sans-seif">Ремонт</p>
                                     

                                     <div class="container-repair  flex-wrap d-flex text-nowrap">
                                         <input :checked="announData.repair =='Без ремонта'" @change="withRepair" data-name="Без ремонта" type="radio" class="btn-check" name="options" id="option1" autocomplete="off">
                                         <label :style="!announData.repair ?'border:1px solid red; border-radius:8px; color:red;':''" class="form-control me-2 my-1" for="option1">Без ремонта</label>

                                         <input :checked="announData.repair =='Обичный'" @change="withRepair" data-name="Обичный" type="radio" class="btn-check" name="options" id="option2" autocomplete="off">
                                         <label :style="!announData.repair ?'border:1px solid red; border-radius:8px; color:red;':''"  class="form-control me-2 my-1" for="option2">Обичный</label>

                                         <input :checked="announData.repair =='Косметический'" @change="withRepair" data-name="Косметический" type="radio" class="btn-check" name="options" id="option3" autocomplete="off">
                                         <label :style="!announData.repair ?'border:1px solid red; border-radius:8px; color:red;':''"  class="form-control me-2 my-1" for="option3">Косметический</label>

                                         <input :checked="announData.repair =='Евро'" @change="withRepair" data-name="Евро" type="radio" class="btn-check" name="options" id="option4" autocomplete="off" >
                                         <label :style="!announData.repair ?'border:1px solid red; border-radius:8px; color:red;':''"  class="form-control me-2 my-1" for="option4">Евро</label>

                                         <input :checked="announData.repair =='Дизайнерксий'" @change="withRepair" data-name="Дизайнерксий" type="radio" class="btn-check" name="options" id="option5" autocomplete="off">
                                         <label :style="!announData.repair ?'border:1px solid red; border-radius:8px; color:red;':''"  class="form-control my-1" for="option5">Дизайнерксий</label>
                                     </div>
                                 </div>
                             </div>
                         </div>

                         <div class="row mt-2">
                             <div class="contrainer-balconies">
                                 <div class="balcon w-25">
                                     <label for="#balcon" class="mb-2 fw-bold" style="font-family:lato, sans-seif">Лифт</label>
                                     <div class="dropdown">
                                             <button style="font-size: 15px; box-shadow: none;" :style="!announData.elevator ? 'border-color:red; color:red;':'border-color:green;color:green'" class="w-[100px] form-select text-left" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                {{ announData.elevator?announData.elevator:'Лифт'}}
                                             </button>
                                             <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                 <li :style="announData.elevator == 'есть' ? 'color:white; background-color:#0468FF;':''" @click="withElevator" class="dropdown-item">есть</li>
                                                 <li :style="announData.elevator == 'нет' ? 'color:white; background-color:#0468FF;':'' " @click="withElevator" class="dropdown-item">нет</li>
                                             </ul>
                                         </div>
                                 </div>
                             </div>
                         </div>

                         <div class="row mt-2">
                             <div class="contrainer-balconies  ">
                                 <div class="balcon">
                                     <p for="#balcon" class="mb-1 mt-2 fw-bold" style="font-family:lato, sans-seif">Парковка</p>
                                     

                                     <div class="container-repair  flex-wrap d-flex text-nowrap">
                                         <input :checked="announData.parking == 'Наземная'" @change="selecetParking" data-parking="Наземная" type="checkbox" class="d-none " name="options" id="sel1" autocomplete="off">
                                         <label :style="!announData.parking?'border:1px solid red; border-radius:8px; color:red':''" class="form-control me-2 my-1" for="sel1">Наземная</label>

                                         <input :checked="announData.parking == 'Многоуровневая'" @change="selecetParking" data-parking="Многоуровневая" type="checkbox" class="d-none " name="options" id="sel2" autocomplete="off">
                                         <label :style="!announData.parking?'border:1px solid red; border-radius:8px; color:red':''" class="form-control me-2 my-1" for="sel2">Многоуровневая</label>

                                         <input :checked="announData.parking == 'Подземная'" @change="selecetParking" data-parking="Подземная" type="checkbox" class="d-none " name="options" id="sel3" autocomplete="off" >
                                         <label :style="!announData.parking?'border:1px solid red; border-radius:8px; color:red':''" class="form-control me-2 my-1" for="sel3">Подземная</label>

                                         <input :checked="announData.parking == 'На крыше'" @change="selecetParking" data-parking="На крыше" type="checkbox" class="btn-check" name="options" id="sel4" autocomplete="off">
                                         <label :style="!announData.parking?'border:1px solid red; border-radius:8px; color:red':''" class="form-control my-1 me-2" for="sel4">На крыше</label>

                                         <input v-if="announData.objects == 'Дом/Дача'" :checked="announData.parking == 'Нет'" @change="selecetParking" data-parking="Нет" type="checkbox" class="btn-check" name="options" id="sel5" autocomplete="off">
                                         <label v-if="announData.objects == 'Дом/Дача'" :style="!announData.parking?'border:1px solid red; border-radius:8px;':''" class="form-control my-1" for="sel5">Нет</label>
                                     </div>
                                 </div>
                             </div>
                         </div>
                     </div>
                 </div>

                 <div class="container mt-4 mb-2 d-flex justify-content-end mx-1 ">
                     <next-btn class="px-4 mx-4 btn btn-light mx-4 text-primary" @click.prevent="navigateTo('/pictures')">Назад</next-btn>
                     <next-btn class="px-4 btn  btn-primary" @click.prevent="next">Далее</next-btn>
                 </div>
             </div>
         </div>
     </form>
     
</template>
<script setup>
const {announData} = getData()
announData.value = JSON.parse(localStorage.getItem('announ'))||{}

function change(){
 const file =  document.querySelector('.choose-picture > input')
 file.click()
}
const balcon = ref('')
function isBalcon(event){
    balcon.value = event.target.textContent 
    if (balcon.value == 'есть') {
       balcon.value = announData.value.balcon = 'есть'
    } else{
       balcon.value = announData.value.balcon = "нет"
        
    };
    localStorage.setItem('announ',JSON.stringify(announData.value))
}

function withRepair(event){
    announData.value.repair = event.target.dataset.name
    localStorage.setItem('announ',JSON.stringify(announData.value))
    
}
 
const elevator = ref('')
function withElevator(event){
    if (event.target.textContent == 'есть') {
        announData.value.elevator = "есть"
    } else{
       announData.value.elevator = "нет"
    }
    localStorage.setItem('announ',JSON.stringify(announData.value))
}

function selecetParking(event){
   announData.value.parking = event.target.dataset.parking
   localStorage.setItem('announ',JSON.stringify(announData.value))
}

function next(){
    if (!announData.value.balcon || !announData.value.repair || !announData.value.elevator || !announData.value.parking) {
        navigateTo('/feature')
    }else{
        if (announData.value.rent == 'Аренда' || announData.value.objects == 'Дом/Дача'||announData.value.objects == 'Коттедж'||announData.value.objects == 'Часть дома') {
        navigateTo('/technicsandfurniture')
        }else{
            navigateTo('description')
        }
        localStorage.setItem('announ', JSON.stringify(announData.value))   
        // navigateTo('/technicsandfurniture')
    }
}

onMounted(() => {
    
})

</script>

<style scoped>
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

 .text-container{
     margin-top: 16px;
     margin-left: 12px;
 }
 .text-container > span{
     font-weight: 500;
     font-size: 16px;
 }

.container-repair{
 width: max-content;
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

 .text-container{
     margin-top: 0px;
 }
 .text-container > span{
     font-weight: 700;
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
     padding: 20px;
     border-radius: 8px;
 }
}
label > span{
 cursor: pointer;
 border: 2px solid rgb(230, 230, 230);
}
label > input:checked + span{
 background-color: rgb(230, 230, 230);
 border: 1px solid #152242;
}

.container-repair{
 width: 100%;
}

.container-repair > .form-control{
 width: max-content;
 cursor: pointer;
 border: 2px solid rgb(230, 230, 230);
}

.container-repair input:checked + .form-control{
  background-color: rgb(230, 230, 230);
 border: 1px solid #152242;
}




</style>
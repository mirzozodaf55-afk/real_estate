<template>
    <form class="layouts" >
         <div  class="container-xl mt-4">
             <div class="progress-container container">
                 <div class="d-flex justify-content-between mb-2 mt-3">
                     <span class="fw-bold pt-1 2">{{ announData.objects !== 'Квартира' && announData.objects !== 'Комната'?'О дома и участка':'Мебель и техника' }}</span>
                     <button class="btn btn-none fs-5 p-0 m-0 text-primary me-2">
                         <font-awesome-icon :icon="['fas', 'circle-question']" />
                     </button>
                 </div>

                 <div class="progress">
                     <div class="progress-bar" role="progressbar" style="width:65%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                 </div>
             </div>

             <div class="container-sm" >
                 <div class="text-container" style="font-weight:normal; color:#152242;">
                     <span>{{ announData.objects !== 'Квартира' && announData.objects !== 'Комната' && announData.objects !=='Квартира в Новостройке' ?'О дома и участка':'В квартире есть' }}</span>
                 </div>
                 <div class="container w-100" >
                     <div class="col mt-4" v-if="announData.objects == 'Квартира' || announData.objects == 'Комната' || announData.objects =='Квартира в Новостройке'" style="color:#152242;">

                           <div class="row mt-2">
                             <div class="preview-window">
                                 <div class="balcon w-25 mt-3" >
                                     <span for="#balcon" class="mb-2 fw-bold text-nowrap" style="font-family:lato, sans-seif">мебель</span>
                                     <div class="d-flex text-nowrap mt-2" style="font-family:lato, sans-serif;">

                                         <label v-for="pos of furniture" :key="pos.id" :for="`${pos.id}`" class="me-2">
                                            <input :data-name="pos.id" @change="selecTechnics" type="checkbox" :id="`${pos.id}`" class="d-none">
                                             <span class="form-control">{{ pos.name }}</span>
                                         </label>
                                     </div>   
                                 </div>
                             </div>
                         </div>

                        <div class="row mt-2">
                             <div class="contrainer-balconies">
                                 <div class="balcon">
                                     <p for="#balcon" class="mb-1 mt-2 fw-bold" style="font-family:lato, sans-seif">Ванная комната</p>
                                        

                                     <div class="d-flex text-nowrap mt-2" style="font-family:lato, sans-serif;">
                                         <label v-for="bathroom of bath" :key="bathroom.id" :for="bathroom.id" class="me-2">
                                             <input  @change="selecTechnics" :data-name="bathroom.id" type="checkbox" :id="bathroom.id" class="d-none">
                                             <span class="form-control">{{ bathroom.name }}</span>
                                         </label>
                                     </div> 
                                 </div>
                             </div>
                         </div>

                         <div class="row mt-2">
                             <div class="contrainer-balconies  ">
                                 <div class="balcon ">
                                     <p for="#balcon" class="mb-1 mt-2 fw-bold" style="font-family:lato, sans-seif">Техника</p>
                                     

                                     <div class="container-repair  flex-wrap d-flex text-nowrap">
                                         <label v-for="cond in c" :for="`option${cond.id}`" :key="cond.id">
                                         <input @change="selecTechnics" :data-name="cond.id" type="checkbox" class="btn-check d-none" name="options" :id="`option${cond.id}`" autocomplete="off">
                                         <span class="form-control me-2 my-1">{{ cond.name }}</span>
                                         </label>
                                     </div>
                                 </div>
                             </div>
                         </div>

                         <div class="row mt-2">
                             <div class="contrainer-balconies  ">
                                 <div class="balcon">
                                     <p for="#balcon" class="mb-1 mt-2 fw-bold" style="font-family:lato, sans-seif">Связь</p>
                                     

                                     <div class="container-repair  flex-wrap d-flex text-nowrap">
                                         <label v-for="tel of contact" :key="tel.id" class="me-2 my-1" :for="`option${tel.id}`">
                                             <input @change="selecTechnics" :data-name="tel.id" type="checkbox" class="btn-check d-none" name="options" :id="`option${tel.id}`" autocomplete="off">
                                            <span class="form-control">{{ tel.name }}</span>
                                            </label>


                                         
                                     </div>
                                 </div>
                             </div>
                         </div>
                     </div>

                    <div class="mt-[40px]" v-else>
                        <div class="overflow-hidden rounded max-w-xlg mx-[auto] ">

                            <div class="block ]">
                                

                                    
                                    <div class="flex flex-col w-[300px] ">
                                    <label class="text-[14px] font-normal mt-[10px] text-[#152242]" for="area-plot">
                                        Площадь участка
                                    </label>

                                    <div :class="!announData.landArea ?'border-[red] text-[red]':'border-[green] text-[green]'" class="h-[40px] w-[250px] flex rounded-1 border-1 bg-[white] overflow-hidden">
                                        <input @input="inputLandArera" v-model="announData.landArea" autocomplete="off" v-maska data-maska="##########" class="border-[0] outline-0 px-[10px] h-full w-[85%]" type="text" id="area-plot">
                                        <select :class="!announData.landArea ?'border-[red]':'border-[green]'" style="-webkit-appearance: none; text-align: center;" class="flex border-l h-full outline-0 items-center justify-center bg-[white] w-[30%]" name="name">
                                                <option value="сот.">сот.</option>
                                                <option value="га">га</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="flex bg-[green]w-full mt-[5px]">
                                    <div class="flex flex-col">
                                        <div class="text-[14px] font-normal mt-[10px] text-[#152242]" for="some-texts">
                                        Категория земель
                                        </div>

                                        <div class="flex items-center flex-wrap w-full mt-[10px]">
                                            <label class="flex mr-[5px] landArea" for="v1">
                                                <input :checked="announData.landCategry == 'Земли населённых пунктов'" @change="selectLandCategory" data-category="Земли населённых пунктов" class="d-none check" type="radio" name="landArea" id="v1">
                                                <span class="py-[9px] px-[5px] bg-white rounded-1 border mt-[5px] ">Земли населённых пунктов</span>
                                            </label>
                                            <label class="flex mr-[5px] landArea" for="v2">
                                                <input :checked="announData.landCategry == 'Земли сельхозназначения'" @change="selectLandCategory" data-category="Земли сельхозназначения" class="d-none check" type="radio" name="landArea" id="v2">
                                                <span class="py-[9px] px-[5px] bg-white rounded-1 border mt-[5px] ">Земли сельхозназначения</span>
                                            </label>
                                            <label class="flex mr-[5px] landArea" for="v3">
                                                <input :checked="announData.landCategry == 'Другое'" @change="selectLandCategory" data-category="Другое" class="d-none check" type="radio" name="landArea" id="v3">
                                                <span class="py-[9px] px-[5px] bg-white rounded-1 border mt-[5px] ">Другое</span>
                                            </label>
                                            
                                        </div>
                                    </div>
                                </div>
                                
                                <div v-if="announData.objects !=='Участок'" class="mt-[20px] flex flex-col w-[300px] ">
                                        <label class="text-[14px] font-normal mt-[10px] text-[#152242]" for="area-home">
                                            Площадь дома
                                        </label>
    
                                        <div :class="!announData.homeArea?'border-[red] text-[red]':'border-[green] text-[green]'" class="h-[40px] w-[200px] flex rounded-1 border-1 bg-[white] overflow-hidden">
                                            <input @input="inputHomeArea" v-model="announData.homeArea" autocomplete="off" v-maska data-maska="####" class="border-[0] outline-0 px-[10px] h-full w-[85%]" type="text" id="area-home">
                                            <span class="flex items-center justify-center bg-[white] w-[15%]">м<sup class="text-[10px]">2</sup></span>
                                            
                                        </div>
                                </div>

                                <div v-if="announData.objects !=='Участок'" class="flex flex-col w-[300px] mt-[5px]">
                                    <label class="text-[14px] font-normal mt-[10px] text-[#152242]" for="quantity-bedroom">
                                        Количество спален
                                    </label>

                                    <div :class="!announData.quantityBedroom?'border-[red] text-[red]':'border-[green] text-[green]'" class="h-[40px] w-[200px] flex rounded-1 border-1 bg-[white] overflow-hidden">
                                        <input @input="chooseQuantityBedroom" v-model="announData.quantityBedroom" v-maska data-maska="#" autocomplete="off" class="border-[0] outline-0 px-[10px] h-full w-full" type="text" id="quantity-bedroom">
                                        <!-- <span class="flex items-center justify-center bg-[white] w-[15%]">м<sup class="text-[10px]">2</sup></span> -->
                                    </div>
                                </div>

                                <div v-if="announData.objects !=='Участок'" class="flex bg-[green]w-full mt-[5px]">
                                    <div class="flex flex-col">
                                        <div class="text-[14px] font-normal mt-[10px] text-[#152242]" for="some-text">
                                        Состояние дома
                                        </div>

                                        <div class="flex flex-wrap w-full">
                                            <label class="mr-[5px] " for="4">
                                                <input :checked="announData.conditionHome == 'Можно жить'" @change="selectConditionHome" data-name="Можно жить" class="d-none check" type="radio" name="radio" id="4">
                                                <span class="form-control bg-white text-[green] rounded-1 border mt-[5px] ">Можно жить</span>
                                            </label>
                                            <label class="mr-[5px] " for="5">
                                                <input :checked="announData.conditionHome == 'Нужен ремонт'" @change="selectConditionHome" data-name="Нужен ремонт" class="d-none check" type="radio" name="radio" id="5">
                                                <span class="form-control bg-white rounded-1 border mt-[5px] ">Нужен ремонт</span>
                                            </label>
                                            <label class="mr-[5px] " for="6">
                                                <input :checked="announData.conditionHome == 'Нужно достроит'" @change="selectConditionHome" data-name="Нужно достроит" class="d-none check" type="radio" name="radio" id="6">
                                                <span class="form-control bg-white rounded-1 border mt-[5px] ">Нужно достроит</span>
                                            </label>
                                            <label class="mr-[5px] " for="7">
                                                <input :checked="announData.conditionHome == 'Нужен капремонт или под снос'" @change="selectConditionHome" data-name="Нужен капремонт или под снос" class="d-none check" type="radio" name="radio" id="7">
                                                <span class="form-control bg-white rounded-1 border mt-[5px] ">Нужен капремонт или под снос</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex bg-[green]w-full mt-[5px]">
                                    <div class="flex flex-col">
                                        <label class="text-[14px] font-normal mt-[10px] text-[#152242]" for="some-texts">
                                        Электричество
                                        </label>

                                        <div class="flex flex-wrap w-full">
                                            <label class="mr-[5px] flex w-[80px] text-center" for="electric1">
                                                <input :checked="announData.electricity =='есть'" @change="selectElectricity" data-name="есть" class="d-none check" type="radio" name="radio2" id="electric1">
                                                <span class="form-control bg-white rounded-1 border mt-[5px]">есть</span>
                                            </label>
                                            <label class="mr-[5px] flex w-[80px] text-center" for="electric2">
                                                <input :checked="announData.electricity =='нет'" @change="selectElectricity" data-name="нет" class="d-none check" type="radio" name="radio2" id="electric2">
                                                <span class="form-control bg-white rounded-1 border mt-[5px]">нет</span>
                                            </label>
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                                
                            </div>
                        </div>  
                 </div>
    
                 <div class="container mt-4 mb-2 d-flex justify-content-end mx-1 ">
                     <next-btn class="px-4 mx-4 btn btn-light mx-4 text-primary" @click.prevent="prew">Назад</next-btn>
                     <next-btn class="px-4 btn  btn-primary" @click.prevent="next">Далее</next-btn>
                 </div>
             </div>

             
         </div>

         
     </form>
     

   
</template>
<script setup>

const {announData} = getData()
announData.value = JSON.parse(localStorage.getItem('announ'))||{}
const facilities = ref([])

const landArea = ref('')
const homeArea = ref('')
const quantityBedroom = ref('')
let checkStyle = false


function inputLandArera(){
    if (announData.value.landArea) {
        announData.value.landArea = parseInt(announData.value.landArea);
    }
    localStorage.setItem('announ',JSON.stringify(announData.value))
}

function selectLandCategory(event){
    announData.value.landCategry = event.target.dataset.category.trim()
    localStorage.setItem('announ',JSON.stringify(announData.value))
}

function inputHomeArea(){
    if (announData.value.homeArea) {
        announData.value.homeArea = parseInt(announData.value.homeArea)
    }
    localStorage.setItem('announ',JSON.stringify(announData.value))
}

function selectConditionHome(event){
    announData.value.conditionHome = event.target.dataset.name.trim()
    localStorage.setItem('announ',JSON.stringify(announData.value))
}

function chooseQuantityBedroom(){
    if (announData.value.quantityBedroom) {
        announData.value.quantityBedroom = parseInt(announData.value.quantityBedroom)
    }
    localStorage.setItem('announ',JSON.stringify(announData.value))
}

function selectElectricity(event){
    announData.value.electricity = event.target.dataset.name.trim().toLowerCase()
    localStorage.setItem('announ',JSON.stringify(announData.value))
}
        const c = ref()
        
        const conditions = fetch('http://127.0.0.1:8000/api/conditions')
        const condition = await conditions
        c.value = await condition.json() 

        const technics = [
        'Кондиционер',
        'Мебель на кухне',
        'Мебель в комнатах',
        'Посудамоечная машина',
        'Стиральная машина',
        'Телевизор',
        'Холодильник',
        ]
        const contact = ref(c.value.filter(item=>{
            return item.name =='Интернет' || item.name == 'Телефон'
        }))

        const furniture = ref(c.value.filter(item=>{
            return item.name == 'На кухне' || item.name == 'В комнатах'
        }))

        const bath = ref(c.value.filter(item=>{
            return item.name == 'Ванна' || item.name == 'Душевая кабина'
        }))


        c.value = c.value.filter(item=>{
            return  technics.indexOf(item.name) !== -1;
        })


onMounted(async ()=>{
    announData.value[0] = JSON.parse(localStorage.getItem('announ'))[0]
    const furn = [
        'Можно с детьми',
        'Можно с животными',
        'Холодильник',
        'Посудамоечная машина',
        'Стиральная машина',
        'В комнатах',
        'На кухне',
        'Кондиционер',
        'Телевизор',
        'Интернет',
        'Телефон',
        'Ванна',
        'Душевая кабина',
    ]
    

    await fetch('http://127.0.0.1:8000/api/create/furniture',{
         method:'post',
         headers:{
           "Content-type":"application/json"
         },
         body: JSON.stringify(furn)
        }).then(res=>res.json())
        .then(res=> res)
        
            setTimeout(()=>{
            let inputs = document.querySelectorAll('.d-none');
            if (announData.value.condition&&announData.value.condition.length) {
                facilities.value = announData.value.condition
                inputs.forEach(elem  =>{
                let checks = elem.dataset.name
                announData.value.condition.forEach((item) => {
                if(parseInt(checks) === item){
                    elem.checked = true
                }
                })
                })
            }
            })
        })


    function selecTechnics(event){
        announData.value.condition = []
        if (event.target.checked) {
            if (Array.isArray(facilities.value)) {  
                 facilities.value.push(parseInt(event.target.dataset.name))
            }
        }
        facilities.value.forEach(item => {
            if (!event.target.checked && parseInt(event.target.dataset.name) === parseInt(item)) {
                facilities.value.splice(facilities.value.indexOf(item),1)
            }
            announData.value.condition = facilities.value
            localStorage.setItem('announ',JSON.stringify(announData.value))           


            c.value = c.value.sort((a, b)=>{
            const nameA = a.name.toUpperCase()
            const nameB = b.name.toUpperCase()

            if (nameA < nameB) {
                return -1
            }
            if (nameA > nameB) {
                return 1
            }
            return 0 
            })

        });

        const elems = document.querySelectorAll('.d-none');
        elems.forEach(elem => {
            announData.value.condition.forEach(val =>{
                if (parseInt(elem.dataset.name) == val) {
                    elem.checked = true
                }
            })
        })

    }   


   
        
        


function next(){   
    if (announData.value.objects == 'Дом/Дача' || announData.value.objects == 'Коттедж'||announData.value.objects == 'Часть дома'||announData.value.objects == 'Дом') {
        if (
            !announData.value.landArea
            ||
            !announData.value.landCategry 
            ||
            !announData.value.homeArea
            ||
            !announData.value.conditionHome 
            ||
            !announData.value.quantityBedroom 
            ||
            !announData.value.electricity
        ) {
            navigateTo('/technicsandfurniture')
        }else navigateTo('/description')
    }else{
        localStorage.setItem('announ', JSON.stringify(announData.value))
        navigateTo('/description')
    }

}
function prew(){
    if (announData.value.objects == 'Дом/Дача' || announData.value.objects == 'Коттедж'||announData.value.objects == 'Дом'||announData.value.objects == 'Часть дома') {
            navigateTo('/pictures')
    }else navigateTo('/feature')
 if (announData.value.objects == 'Участок') {
    navigateTo('/map')
 }
}

function change(){
 const file =  document.querySelector('.choose-picture > input')
 file.click()
}

</script>

<style scoped>

*{
    font-family: Lato,Arial,sans-serif;
}
@media screen and (min-width:320px) {
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
     margin-top: 16px;
     margin-left: 12px;
 }
 .text-container > span{
     font-weight: 500;
     font-size: 16px;
     color: #152242;
 }

.container-repair{
 width: 100vw;
 display: flex;
 flex-wrap: nowrap;
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
     line-height: 24px;
 }  

 .container-repair{
 width: max-content;
 flex-wrap: nowrap;
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
 width: 60%;
}

.container-repair > .form-control{
 width: max-content;
 cursor: pointer;
 border: 2px solid rgb(230, 230, 230);
}

.container-repair input:checked + .form-control{
background-color: rgb(230, 230, 230) !important;
border: 1px solid #152242 !important;
}

.check:checked + .bg-white{
    border-radius: 3px;
    border: 1px solid #152242 !important;
    background: rgb(235, 232, 232) !important;
}

</style>
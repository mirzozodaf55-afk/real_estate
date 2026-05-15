

<script setup>

useHead({
    title:'Объявления'
})
        const isRent = ref("")
        const AnnounOgj2 = ref({})
        const {announData,AnnounOgj} = getData()
        
        announData.value = JSON.parse(localStorage.getItem('announ'))||{}
        
        const typeObject = ref()
        if (!JSON.parse(localStorage.getItem('announ'))) {
            typeObject.value = JSON.parse(localStorage.getItem('announ'))
        }else typeObject.value = {}
        

    function changeText(event){
        const btn = document.querySelector('.field_name')
        btn.textContent = event.target.textContent.trim();
        isRent.value = btn.textContent;
        AnnounOgj.value.rent = btn.textContent
        announData.value.rent = btn.textContent
        console.log(announData.value.rent);
        
        if (AnnounOgj.rent == 'Продажа') {
            AnnounOgj.value.typeRent = 'Длительно'
            announData.value.typeObject = 'Длительно'
        }

        localStorage.setItem('announ',JSON.stringify(announData.value))
    }


    function selectRent(event){
        const btn = document.querySelector('.rent')
        btn.textContent = event.target.textContent.trim();
        isRent.value = btn.textContent;
        AnnounOgj.value.typeRent = btn.textContent
        announData.value.typeRent = btn.textContent
        if (AnnounOgj.value.typeRent == 'Посуточно' && announData.value.typeRent == 'Посуточно') {
            AnnounOgj.value.objects = 'Квартира'
            AnnounOgj.value.Estate = 'Жилая'
            announData.value.objects = 'Квартира'
            announData.value.Estate = 'Жилая'
        }
        localStorage.setItem('announ',JSON.stringify(announData.value))
    }
    function selectEstate(event){
        const btn = document.querySelector('.real_estate')
        btn.textContent = event.target.textContent.trim();
        isRent.value = btn.textContent;
        AnnounOgj.value.Estate = btn.textContent
        announData.value.Estate = btn.textContent
        if (AnnounOgj.value.typeRent == 'Посуточно' && announData.value.typeRent == 'Посуточно') {
            AnnounOgj.value.objects = 'Не выбранно'
            AnnounOgj.value.Estate = 'Жилая'
            announData.value.objects = 'Не выбранно'
            announData.value.Estate = 'Жилая'
        }
        localStorage.setItem('announ',JSON.stringify(announData.value))
    }

    function selectObject(event){
        const btn = document.querySelector('.object')
            isRent.value = btn.textContent;
            AnnounOgj.value.objects = event.target.textContent.trim()
            announData.value.objects = event.target.textContent.trim()
            btn.textContent = event.target.textContent.trim()
            localStorage.setItem('announ', JSON.stringify(announData.value))
            navigateTo('/map')
    }

    const {isShow} = useSwitch()

    function selectObjectFromSale(event){
        typeObject.value = event.target.dataset.name
        AnnounOgj.value.objects = event.target.dataset.name;
        announData.value.objects = event.target.dataset.name;
        localStorage.setItem('announ', JSON.stringify(announData.value))
         navigateTo('/map')
        isShow.value = false
        if (AnnounOgj.value.Estate == 'Коммерческая') {
            navigateTo('/office')
         }
        // if (announData.value[0].objects == 'Участок') {
        //     navigateTo('/landarea')
        // }
    }
    

    
    onMounted(async ()=>{
        if (JSON.parse(localStorage.getItem('announ'))) {
            AnnounOgj.value = JSON.parse(localStorage.getItem('announ'))||{}
        } else
        {
            AnnounOgj.value = {}
        }
    

    const infrastructure = ["автомойка",
   "автосервис",
   "аптека",
   "ателье-одежды",
   "банкомат",
   "бассейн",
   "автомойка",
   "буфет",
   "выставочно-складской комплекс",
   "гостиница",
   "кафе",
   "кинотеатр",
   "конференц-зал",
   "медицинский-центр",
   "минимаркет",
   "нотариальная контора",
   "отделение банка",
   "парк",
   "ресторан",
   "салон красоты",
   "складские помещения",
   "столовой",
   "супермаркет",
   "торговая зона",
   "фитнес-центр",
   "фотосалон",
   "центральная рецепция",
   ];
   const cityData = [
    "Душанбе",
    "Худжанд",
    "Абдурахмони Джоми",
    "Айни",
    "Ашт",
    "Бальджувон",
    "Бободжон Гафуров",
    "Бохтар (Курган-Тюбе)",
    "Бустон (Чкаловск)",
    "Вандж",
    "Варзоб",
    "Вахдат",
    "Вахш",
    "Восе",
    "Гиссар",
    "Горная Матча",
    "Гулистон",
    "Дангара",
    "Дарваз",
    "Деваштич (Ганчи)",
    "Джаббор Расулов",
    "Джайхун (Кумсангир)",
    "Джалолиддини Балхи (Руми)",
    "Джами",
    "Дусти",
    "Зафарабад",
    "Истаравшан",
    "Исфара",
    "Ишкашим",
    "Кубодиён",
    "Канибадам",
    "Куляб",
    "Кушониён",
    
   ]
    
        await fetch('http://127.0.0.1:8000/api/create/infrastructure',{
         method:'post',
         headers:{
           "Content-type":"application/json"
         },
         body: JSON.stringify(infrastructure)
        })

        await fetch('http://127.0.0.1:8000/api/create/city', {
         method:'post',
         headers:{
           "Content-type": "application/json"
         },
         body: JSON.stringify(cityData)
        })

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
        'Баня',
        'Гараж',
        'Бассейн',

    ]
    

    await fetch('http://127.0.0.1:8000/api/create/furniture',{
         method:'post',
         headers:{
           "Content-type":"application/json"
         },
         body: JSON.stringify(furn)
        }).then(res=>res.json())
        .then(res=> res)
    })
</script>

<template>
    <div class="content">
            <div style="flex:1 0 auto">
                <div class="add-form">
                    <div class="add-form_header--mobele">
                        <div class="add-form__title">Новое объявление</div>
                        <!-- <div class="add-form__title">Редактировать объявление</div> -->

                        <div class="add-form__text">
                            Lorem ipsum dolor sit amet consectetur adipisicing elit. Ducimus, accusamus, quaerat magnam qui quasi ea sit nesciunt consectetur quae consequatur assumenda ullam doloremque porro optio? Vel nesciunt nisi quasi blanditiis?
                        </div>
                    </div>

                        <div class="add-form__body fs-5">
                            <form  class="add-form__main">
                                
                            <div class="add-segment">
                            <div class="field__name fs-7">Тип сделки</div>
                                <div class="btn-group">
                                    <button 
                                    class="field_name 
                                    btn btn-light border
                                    btn-sm dropdown-toggle"
                                    type="button"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                        {{`${announData.rent ? announData.rent:'Не выбранно'}`}}
                                    </button>
                                
                                    <ul class="dropdown-menu">
                                        <li :style="announData.rent =='Аренда' ? `background-color: #0468FF; color:white; font-weight:bold;`:''" @click="changeText" class="cursor-pointer dropdown-item item">
                                            Аренда
                                        </li>
                                        <li :style="announData.rent =='Продажа' ? `background-color: #0468FF; color:white; font-weight:bold;`:''" @click="changeText" class="cursor-pointer dropdown-item item">
                                            Продажа
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="add-segment "  v-if="announData.rent && announData.rent == 'Аренда'">
                                <div class="field__name fs-7">Тип аренды</div>
                                    <div class="btn-group">
                                        <button class="field_name rent btn btn-light border btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            {{`${announData.typeRent ? announData.typeRent: 'Не выбранно'}`}}
                                        </button>
                                    <ul class="dropdown-menu">
                                        <li :style="announData.typeRent =='Длительно' ? `background-color: #0468FF; color:white; font-weight:bold;`:''" @click="selectRent" class="cursor-pointer dropdown-item">
                                            Длительно
                                        </li>
                                        <li :style="announData.typeRent =='Несколько месяцев' ? `background-color: #0468FF; color:white; font-weight:bold;`:''" @click="selectRent" class="cursor-pointer dropdown-item">
                                            Несколько месяцев
                                        </li>
                                        <li :style="announData.typeRent =='Посуточно' ? `background-color: #0468FF; color:white; font-weight:bold;`:''" @click="selectRent" class="cursor-pointer dropdown-item">
                                            Посуточно
                                        </li>
                                    </ul>
                                </div>
                            </div>

                             <div class="add-segment" v-if="announData.typeRent || announData.rent == 'Продажа'">
                                    <div class="field__name fs-7">Тип недвижимости</div>
                                <div class="btn-group">
                                    <button class="field_name real_estate btn btn-light border btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        {{ announData.typeRent == 'Посуточно' ? 'Жилая' :'Не выбранно' }}
                                    </button>
                                
                                    <ul class="dropdown-menu">
                                        <li :style="announData.Estate =='Жилая' ? `background-color: #0468FF; color:white; font-weight:bold;`:''" @click="selectEstate" class="dropdown-item cursor-pointer">
                                            Жилая
                                        </li>
                                        <li :style="announData.Estate == 'Коммерческая' ? `background-color:#0468FF; color:white; font-weight:bold;`:''" @click="selectEstate" v-if="AnnounOgj.typeRent !== 'Посуточно'" class="dropdown-item cursor-pointer">
                                            Коммерческая
                                        </li>
                                    </ul>
                                </div>
                            </div>


                            <div class="add-segment" v-if="announData.rent == 'Аренда' && announData.typeRent == 'Посуточно'">
                                    <div class="field__name fs-7">Объект</div>
                                <div class="btn-group">
                                    <button class="object field_name  btn btn-light border btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        {{announData.objects == 'Посуточно' ? 'typeObject.objects' :'Не выбранно' || announData.objects ? announData.objects: 'Не выбранно'}}
                                    </button>
                                
                                    <ul class="dropdown-menu">
                                        <li :style="announData.objects.trim() =='Квартира' ? `background-color: #0468FF; color:white; font-weight:bold;`:''" @click="selectObject" class="dropdown-item">
                                            Квартира
                                        </li>
                                        <li :style="announData.objects.trim() =='Комната' ? `background-color: #0468FF; color:white; font-weight:bold;`:''" @click="selectObject" class="dropdown-item">
                                            Комната
                                        </li>
                                        <li :style="announData.objects.trim() =='Дом/Дача' ? `background-color: #0468FF; color:white; font-weight:bold;`:''" @click="selectObject" class="dropdown-item">
                                            Дом/Дача
                                        </li>
                                    </ul>
                                </div>
                            </div>



                            <div class="containers objects fs-6"  v-if=" announData.Estate && announData.typeRent !== 'Посуточно'">
                                <div class="field__name fw-bold fs-7 ms-3 ">
                                    Объект
                                </div>
                                <div class="all__contents" v-if="announData.Estate == 'Жилая'">

                                    <div class="field__content ">
                                        <div class="type-radio">
                                            <label for="label1">Квартира</label>
                                            <input :checked="announData.objects == `Квартира`" name="house" data-name="Квартира" type="radio" @change="selectObjectFromSale"  id="label1">
                                        </div>
                                        <div v-if="announData.rent == 'Продажа'" class="type-radio">
                                            <label for="label2">Квартира в Новостройке</label>
                                            <input :checked="announData.objects == `Квартира в Новостройке`" name="house" data-name="Квартира в Новостройке" type="radio" @change="selectObjectFromSale"  id="label2">
                                        </div>

                                        <div class="type-radio">
                                            <label for="label3">Комната</label>
                                            <input :checked="announData.objects == `Комната`" name="house" data-name="Комната" type="radio" @change="selectObjectFromSale"  id="label3">
                                        </div>
                                    </div>

                                <div class="field__content">
                                    <div class="type-radio">
                                        <label for="label6">Дом/Дача</label>
                                        <input :checked="announData.objects == `Дом/Дача`" name="house" data-name="Дом/Дача" type="radio" @change="selectObjectFromSale"  id="label6">
                                    </div>
                                    <div class="type-radio">
                                        <label for="label7">Коттедж</label>
                                        <input :checked="announData.objects == `Коттедж`" name="house" data-name="Коттедж" type="radio" @change="selectObjectFromSale"  id="label7">
                                    </div>
                                    <div class="type-radio">
                                        <label for="label9">Часть дома</label>
                                        <input :checked="announData.objects == `Часть дома`" name="house" data-name="Часть дома" type="radio" @change="selectObjectFromSale"  id="label9">
                                    </div>
                                    <div v-if="announData.rent == 'Продажа'" class="type-radio">
                                        <label for="label10">Участок</label>
                                        <input :checked="announData.objects == `Участок`" name="house" data-name="Участок" type="radio" @change="selectObjectFromSale" id="label10">
                                    </div>
                                </div>
                                </div>

                                <!-- ---------- -->

                                <div class="field__content" v-if="announData.Estate == 'Коммерческая'">
                                        <div class="type-radio">
                                            <label for="label1">Офис</label>
                                            <input :checked="announData.objects == `Офис`" data-name="Офис" @change="selectObjectFromSale" name="commercial" type="radio"  id="label1">
                                        </div>
                                        <div class="type-radio">
                                            <label for="label2">Здание</label>
                                            <input :checked="announData.objects == `Здание`" data-name="Здание" @change="selectObjectFromSale" name="commercial" type="radio"  id="label2">
                                        </div>

                                        <div class="type-radio">
                                            <label for="label3">Торговая площадь</label>
                                            <input :checked="announData.objects == `Торговая площадь`" data-name="Торговая площадь"
                                             @change="selectObjectFromSale" name="commercial" type="radio"  id="label3">
                                        </div>
                                        <div class="type-radio">
                                            <label for="label5">Коммерческая земля</label>
                                            <input :checked="announData.objects == `Коммерческая земля`" data-name="Коммерческая земля" @change="selectObjectFromSale" name="commercial" type="radio" id="label5">
                                        </div>
                                        <div class="type-radio">
                                            <label for="label6">Склад</label>
                                            <input :checked="announData.objects == `Склад`" data-name="Склад" @change="selectObjectFromSale" name="commercial" type="radio" id="label6">
                                        </div>
                                    </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
</template>

<style scoped>

.accordion-button{
    padding: 5px;
    /* border: none; */
    outline: none;
    min-width: 180px;
}
.accordion-botton:focus{
    border-color:red;
}
.accordion-body{
    padding: 0;
}

.accordion-body > ul {
    list-style: none;
    display: inline-block;
}

.accordion-body > ul > li{
    font-size: 14px;
    display: block;
    position: relative;
    padding: 7px 40px 7px 0px;
    color: rgba(34,34,34,.75);
    font-weight: 700;
    margin-top: 3px;
    cursor: pointer;
}
    
.content{
    background-color: rgb(244, 244, 244);
    /* position: absolute; */
    top: 50px; 
    display: flex;
    flex-direction: column;
    flex: 1 0 auto;
    min-height: 100vh;
}
.add-form{
    width: 100%;
    margin: 0 auto;
    color: #121212;
    font-weight: 400;
    font-size: 14px;
    line-height: 1.3;
    font-family: Lato, sans-serif;
}
.add-form_header--mobele{
    margin-bottom: 32px;
    width: 100%;
    padding: 30px 42px;
}
.add-form__title{
    font-weight: 700;
    font-size: 27px;
    line-height: 36px;
}
.add-form__text{
    font-size: 16px;
    line-height: 24px;
}
.add-form__body{
    display: flex;
    position: relative;
    border-radius: 2px;
    margin: 10px;
    /* background-color: #000; */
}

.add-form__main{
    background-color: white;
    position: relative;
    flex-shrink: 0;
    width: 100%;
    display: block;
}
.form-select{
    height: min-content;
    border-radius: 4px;
    color: #505152;
    max-width: max-content;
    font-size: 14px;
    padding-top: 3px;
    background-color: transparent;
    padding-bottom: 3px;
    transition: background-color .4s, color .4s ease-in-out;
}
.form-select>.option{
    color: black;
    font-size: 14px;
    background-color: transparent;
}
.containers{
    margin:20px 0;
}
.add-segment{
    margin: 20px 15px;
}
.field__name{
    margin-bottom: 8px;
    flex-shrink: 0;
    padding-top: 6px;
    font-size: 16px;
    width: 180px;
    font-size: 14px;
    font-family: Lato,sans-serif;
    font-weight: 400;
    line-height: 1.3;
}
.all__contents{
    display: flex;
    align-items: flex-start;

}

.objects{
    display: flex;
    flex-direction: column;
}
.field__content{
    display: flex;
    padding: 10px;
    /* margin: auto; */
    flex-direction: column;
}
.type-radio{
    display: flex;
    justify-content:  flex-start;
    flex-direction: row-reverse;
}

.type-radio >label{
    margin: 8px;
}
</style>
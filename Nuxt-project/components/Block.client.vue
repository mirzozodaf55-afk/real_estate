<script setup>
import YandexMap from '~/pages/yandex-map.vue';
const { announData } = getData();
announData.value = JSON.parse(localStorage.getItem('announ'))||{}
const floorObj = ref({
  floor: "",
  floor_in_house: "",
});


const fullAddress = ref([])
const cityName = ref("");
const typeHome = ref('')
const year = ref(0)
const mapObj = ref({});
const mapObj1 = ref({});
const moreThen = ref(false);
const showCities = ref();
const date = new Date()
const curentYear = ref(date.getFullYear())
const loaderMap = ref(false)
const address = ref('')
const mapLoader = ref(false) 
let myMap = null
async function selectCity() {
  const cityUrl = fetch(`http://127.0.0.1:8000/api/city`);
  const c = await cityUrl;
  const city = await c.json();
  showCities.value = city;
}
const selected = ref(false);
 


async function getAddress(){
const suggestResponse = await fetch(`https://suggest-maps.yandex.ru/v1/suggest?apikey=e0aec847-3d84-4153-8955-4d0520b28188&text=${encodeURIComponent(address.value)}&lang=ru_RU&results=10`);
const suggestData = await suggestResponse.json();
fullAddress.value = suggestData.results
}



function isFloor() {
  if (parseInt(mapObj.value.floor) > parseInt(mapObj.value.floorHouse)) {
    moreThen.value = true;
  } else {
    moreThen.value = false;
  }
  announData.value.floor 
  //= floorObj.value.floor = mapObj.value.floor
  announData.value.floorHouse 
  //= floorObj.value.floor_in_house = mapObj.value.floorHouse
  announData.value.year 
  //= year.value = mapObj.value.year
  localStorage.setItem('announ',JSON.stringify(announData.value))

}

function selectType(event) {
  mapObj.value.selectType = event.target.parentElement.textContent;
  announData.value.selectType = event.target.parentElement.textContent;
  typeHome.value = mapObj.value.selectType
  localStorage.setItem('announ',JSON.stringify(announData.value))
}

const router = useRouter();

function move() {
    // announData.value[1] = mapObj.value;
    if (announData.value.objects == 'Участок') {
      navigateTo('/technicsandfurniture')
    }
    if (announData.value.objects == 'Квартира'||announData.value.objects == 'Комната'||announData.value.objects == 'Квартира в Новостройке') {
      
      if (
         announData.value.city == '' 
      || !announData.value.floor 
      || !announData.value.floorHouse 
      || parseInt(announData.value.floor) > parseInt(announData.value.floorHouse)
      || typeHome.value == ''
      || !announData.value.year
      || announData.value.year < 1950
      || announData.value.year > curentYear.value
      ) {
        navigateTo("/map");
        // announData.value[1] = "";
        localStorage.setItem("announ", JSON.stringify(announData.value));
      }else navigateTo("/pictures");
    }
    if(announData.value.objects == 'Дом/Дача'||announData.value.objects == 'Коттедж' || announData.value.objects == 'Участок'||announData.value.objects == 'Дом'){
      if (!announData.value.city
      || !announData.value.floorHouse
      || !typeHome.value
      || !announData.value.year
      || announData.value.year < 1950
      || announData.value.year > curentYear.value
      ) {
        return
      }else navigateTo("/pictures");
    }

    localStorage.setItem('announ',JSON.stringify(announData.value))
  
}

onMounted(() => {
  announData.value = JSON.parse(localStorage.getItem("announ"));
  
    cityName.value = mapObj.value.city
    floorObj.value.floor =  mapObj.value.floor
    floorObj.value.floor_in_house = mapObj.value.floorHouse
    year.value = mapObj.value.year
    typeHome.value = mapObj.value.typeHome

  const inputs = document.querySelectorAll("input");
  window.ymaps.ready(() => {
         myMap = new window.ymaps.Map('map-yandex', {
          center: [38,68],
          zoom: 10,
          controls: ['zoomControl', 'fullscreenControl']
        })
      })
  
});
mapLoader.value = true
async function select(event) {
  loaderMap.value = true
  address.value = event.target.textContent;
  const suggestResponse = await fetch(`https://suggest-maps.yandex.ru/v1/suggest?apikey=e0aec847-3d84-4153-8955-4d0520b28188&text=${encodeURIComponent(address.value)}&lang=ru_RU&results=9`);
const suggestData = await suggestResponse.json();
fullAddress.value = suggestData.results
  const topResult = suggestData.results[0];
  console.log(fullAddress.value);
  
  if (!topResult) {
    console.warn("Ничего не найдено");
    return null;
  }

  const fullText = topResult.title.text + ', ' + (topResult.subtitle?.text || '');
  await ymaps.ready();

  const geoRes = await ymaps.geocode(fullText);
  const firstGeoObject = geoRes.geoObjects.get(0);
  if (!firstGeoObject) {
    console.warn("Не удалось найти координаты через ymaps.geocode");
    return null;
  }

  const coords = firstGeoObject.geometry.getCoordinates();
  console.log("Координаты:", coords);
  myMap.setCenter(coords)

  mapObj.value.coord_x = coords[0] 
  mapObj.value.coord_y = coords[1] 
  announData.value.coord_x = coords[0]
  announData.value.coord_y = coords[1]
  fullAddress.value = []
  loaderMap.value = false
  localStorage.setItem('announ',JSON.stringify(announData.value))
}
mapLoader.value = false
</script>
<template>
  <div class="container-sm">
    <p id="selectcity" class="mb-4 mt-2">Введите адрес</p>

    <form class="mt-3">
      <div class="overflow-hidden mt-2">
          <input type="text" 
          id="suggest"
          class="form-control border-none w-100" 
          placeholder="Укажите город или улица"
          v-model="address" 
          @input="getAddress"> 
      </div>
      <div
      v-if="fullAddress.length"
        class="dropdown rounded mb-5 absolute h-[300px] border"
      >
        

        <ul
          style="height: 300px; overflow: auto"
          class="p-0"
        >
          <li  @click="select" class="my-[5px] py-[10px]"
          style="box-shadow: 0 0 10px silver;"
          v-for="(city,i) of fullAddress" :key="i">
            <a
              class="dropdown-item truncate mx-[10px]"
              href="#"
              >{{ city.subtitle?city.subtitle.text:city.title.text }}</a
            >
          </li>
        </ul>
      </div>
      <div id="map-yandex" class="relative flex justify-center items-center h-[350px] w-full border-[5px] mt-[2px]">
        <div class="flex items-center absolute justify-center" >
          <font-awesome-icon class="text-primary  z-[100]" :icon="['fas', 'location-dot']" />
        </div>
      </div>

      
      <!-- <div
        :style="!announData.city ? `border-radius:8px; border:1px solid red;color:red;` : 'border:1px solid green; color:green;'"
        class="dropdown rounded mb-5"
        @click="selectCity"
      >
        <a
          class="btn form-control border dropdown-toggle-none relative"
          style="box-shadow:none;"
          href="#"
          role="button"
          data-bs-toggle="dropdown"
        >
          <span :class="!announData.city ?'text-[red]':'text-[green]'" class="mx-[auto]">{{
            announData.city ? announData.city : "Укажите город"
          }}</span>
        </a>

        <ul
          style="height: 300px; overflow: auto"
          class="dropdown-menu form-control"
        >
          <li @click="select" v-for="city of showCities" :key="city">
            <a
              :style="
                announData.city == city.name
                  ? 'background-color:#0468FF; color:white; font-weight:bold;'
                  : ''
              "
              class="dropdown-item"
              href="#"
              >{{ city.name }}</a
            >
          </li>
        </ul>
      </div> -->
      <p
        v-if="!announData.city"
        style="
          margin-top: -45px;
          font-family: Lato, Arial, sans-serif;
          font-weight: 400;
          font-size: 11px;
          line-height: 15px;
          color: red;
        "
      >
        Укажите адрес
      </p>
    </form>

    <div class="flex mt-4" id="floor"
    >
      <div class="content">
        <div 
        class="rows" v-if="announData.objects == 'Квартира' || announData.objects =='Комната'|| announData.objects =='Квартира в Новостройке'">
          <span>Этаж</span>
          <input  
            :style="moreThen || !announData.floor || announData.floor > announData.floorHouse ? `border:1px solid red;color:red;` : 'border-color:green;color:green'"
            @input="isFloor"
            type="number"
            v-maska
            data-maska="##"
            v-model="announData.floor"
            
            class="input form-control"
          />
          <p
            v-if="moreThen"
            style="
              margin-top: 3px;
              font-family: Lato, Arial, sans-serif;
              font-weight: 400;
              font-size: 11px;
              line-height: 15px;
              color: red;
            "
          >
            Этаж должен быть меньше или <br />
            равен количеству этажей
          </p>
        </div>
        <div class="rows" v-if="announData.objects !=='Участок'">
          <span>{{ announData.objects == 'Квартира' || announData.objects =='Комната' || announData.objects =='Квартира в Новостройке' ? 'Этаж в дома': 'Количество этажей' }}</span>
          <input
            :style="!announData.floorHouse || announData.floor > announData.floorHouse ? `border:1px solid red;color:red; ` : 'border-color:green; color:green'"
            @input="isFloor"
            type="number"
            v-maska
            data-maska="##"
            v-model="announData.floorHouse"
            class="input focus:outline-none border-none form-control "
          />
        </div>
      </div>
    </div>

    <!-- <div v-if="announData.objects == 'Квартира'" class="flex mt-4" id="number_apartament">
      <div class="rows">
        <span>Номер квартиры</span>
        <input
          :style="!announData.apartmentNumber ? 'border:1px solid red;' : ''"
          v-model="mapObj.apartmentNumber"
          type="number"
          v-maska
            data-maska="#####"
          class="input form-control"
        />
      </div>
    </div> -->

    <div v-if="announData.objects == 'Квартира' || announData.objects =='Комната'" class="flex mt-4" id="year">
      <div class="rows">
        <p> О здании</p>
      </div>
    </div>
    <div class="flex mt-2" v-if="announData.objects !=='Участок'">
      <div class="contents">
        <div class="rows">
          <p>Год постройки</p>
          <input
            :style="`${!announData.year || announData.year < 1950 || announData.year > curentYear ? 'border:1px solid red; color:red;' : 'border-color:green; color:green'}`"
            v-model="announData.year"
            type="number"
            v-maska
            data-maska="####"
            @input="isFloor"
            class="form-control"
            style="box-shadow:none;"
          />
          <p v-if="announData.year < 1950" class="text-[red] mt-[4px]" style="font-size:14px">Укажите год позднее 1950</p>
          <p v-if="announData.year > curentYear" class="text-[red] mt-[4px]" style="font-size:14px;">Укажите год до {{curentYear}}</p>
        </div>
      </div>
    </div>

    <div class="flex" id="type-home" v-if="announData.objects !=='Участок'">
      <div class="type-home mt-4">
        <p>{{ announData.objects !== 'Квартира' || announData.objects !=='Комната' ? 'Материал дома':'Тип дома' }}</p>
        <slot />
        <div class="rows">
          <label for="1">
            <input
              :checked="announData.selectType == 'Кирпичный'"
              @change="selectType"
              name="radioinp"
              type="radio"
              id="1"
            />
            <span
              :style="!announData.selectType ? 'border: 1px solid red; color:red;' : ''"
              class="form-control"
              >Кирпичный</span
            >
          </label>
          <label for="2">
            <input
              :checked="announData.selectType == 'Монолитный'"
              @change="selectType"
              name="radioinp"
              type="radio"
              id="2"
            />
            <span
              :style="!announData.selectType ? 'border: 1px solid red; color:red;' : ''"
              class="form-control"
              >Монолитный</span
            >
          </label>
          <label for="3">
            <input
              :checked="announData.selectType == 'Панельный'"
              @change="selectType"
              name="radioinp"
              type="radio"
              id="3"
            />
            <span
              :style="!announData.selectType ? 'border: 1px solid red; color:red;' : ''"
              class="form-control"
              >Панельный</span
            >
          </label>
          <label for="4">
            <input
              :checked="announData.selectType == 'Блочный'"
              @change="selectType"
              name="radioinp"
              type="radio"
              id="4"
            />
            <span
              :style="!announData.selectType ? 'border: 1px solid red; color:red;' : ''"
              class="form-control"
              >Блочный</span
            >
          </label>
          <label for="5">
            <input
              :checked="announData.selectType == 'Деревянный'"
              @change="selectType"
              name="radioinp"
              type="radio"
              id="5"
            />
            <span
              :style="!announData.selectType ? 'border: 1px solid red; color:red;' : ''"
              class="form-control"
              >Деревянный</span
            >
          </label>
          <label for="6">
            <input
              :checked="announData.selectType == 'Сталинский'"
              @change="selectType"
              name="radioinp"
              type="radio"
              id="6"
            />
            <span
              :style="!announData.selectType ? 'border: 1px solid red; color:red;' : ''"
              class="form-control"
              >Сталинский</span
            >
          </label>
          <label for="7">
            <input
              :checked="announData.selectType == 'Монолитно-кирпичный; color:red;'"
              @change="selectType"
              name="radioinp"
              type="radio"
              id="7"
            />
            <span
              :style="!announData.selectType ? 'border: 1px solid red; color:red;' : ''"
              class="form-control"
              >Монолитно-кирпичный</span
            >
          </label>
        </div>
      </div>
    </div>
    <div class="container mt-3 d-flex justify-content-end mx-1">
      <next-btn class="btn btn-primary px-4" @click="move()"> Далее </next-btn>
    </div>
  </div>
</template>

<style scoped>
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
@media screen and (min-width: 320px) {
  .flex {
    display: flex;
    margin-top: 10px;
  }
  .input {
    width: 200px;
    box-shadow: none;
  }
  .content {
    display: flex;
    width: 70%;
    flex-direction: column;
  }

  .rows > span {
    font-size: 15px;
  }
  .flex > .content > .rows > input {
    width: 200px;
  }
  .flex > .content > .rows {
    margin-bottom: 40px;
  }
  .flex > .rows > p {
    letter-spacing: -1px;
    font-weight: bold;
    font-size: 18px;
    font-family: lato, sans-serif;
  }
  .flex > .contents {
    display: flex;
    width: 85%;
  }
  .flex > .contents > .rows > input {
    width: 100px;
  }
  .type-home > .rows {
    display: flex;
    flex-wrap: wrap;
  }

  .type-home > .rows > label {
    width: max-content;
    margin: 5px;
  }
  label > span {
    width: 100%;
  }
  label > input {
    display: none;
  }
  label > input:checked + span {
    box-shadow: 0 0 5px #006cfd;
    background-color: rgba(192, 192, 192, 0.338);
  }
  label > input:active + span {
    background-color: #00b5fd89;
  }
  .container-btn {
    display: flex;
    justify-content: center;
  }
  .container-btn > .btn {
    width: 100%;
  }
}
@media screen and (min-width: 640px) {
  .container-btn {
    display: flex;
    justify-content: flex-end;
  }

  .container-btn > .btn {
    width: max-content;
  }

  p {
    font-size: 1em;
    font-weight: 470;
    font-family: Lato, sans-serif;
  }

  .name {
    display: flex;
    justify-content: space-between;
    margin-top: 12px;
  }

  .container-sm {
    margin-bottom: 10px;
  }
  .flex {
    display: flex;
    margin-top: 10px;
  }
  .input {
    width: 200px;
  }
  .content {
    display: flex;
    width: 70%;
    flex-direction: row;
    justify-content: space-between;
  }

  .rows > span {
    font-size: 15px;
  }
  .flex > .rows > p {
    letter-spacing: -1px;
    font-weight: bold;
    font-size: 18px;
    font-family: lato, sans-serif;
  }
  .flex > .contents {
    display: flex;
    width: 85%;
    justify-content: space-between;
  }
  .flex > .contents > .rows > input {
    width: 100px;
  }
  .type-home > .rows {
    display: flex;
    flex-wrap: wrap;
  }

  .type-home > .rows > label {
    width: max-content;
    margin: 5px;
  }
  label > span {
    width: 100%;
  }
  label > input {
    display: none;
  }
  label > input:checked + span {
    border-color: #006cfd;
  }
}

.container-sm {
  margin-bottom: 10px;
}

@media screen and (min-width: 1024px) {
  .container-sm {
    width: 67%;
    height: max-content;
    margin-top: 10px;
    margin-bottom: 20px;
    box-shadow: 0 0 30px rgba(0, 0, 0, 0.16);
    padding: 20px 35px;
    border-radius: 8px;
  }
  .container-sm > p {
    font-weight: bold;
  }

  .flex {
    display: flex;
    margin-top: 10px;
  }
  .input {
    width: 200px;
  }
  .content {
    display: flex;
    width: 70%;
    justify-content: space-between;
  }
  .rows > span {
    font-size: 15px;
  }
  .flex > .rows > p {
    letter-spacing: -1px;
    font-weight: bold;
    font-size: 18px;
    font-family: lato, sans-serif;
  }
  .flex > .contents {
    display: flex;
    width: 85%;
    justify-content: space-between;
  }
  .flex > .contents > .rows > input {
    width: 100px;
  }
  .type-home > .rows {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-start;
  }

  .type-home > .rows > label {
    width: max-content;
    margin: 5px;
  }
  label > span {
    width: 100%;
  }
  label > input {
    display: none;
  }
  label > input:checked + span {
    border-color: #006cfd;
    background-color: rgba(192, 192, 192, 0.338);
  }
  label > input:active + span {
    background-color: #00b5fd89;
  }
}
</style>
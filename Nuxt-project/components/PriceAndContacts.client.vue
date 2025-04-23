
<template>
  <form class="layouts">
    <div class="container-xl mt-4">
      <div class="progress-container container">
        <div class="d-flex justify-content-between mb-2 mt-3">
          <span class="fw-bold pt-1 2">Ценна и контакты</span>
          <button class="btn btn-none fs-5 p-0 m-0 text-primary me-2">
            <font-awesome-icon :icon="['fas', 'circle-question']" />
          </button>
        </div>

        <div class="progress">
          <div
            class="progress-bar"
            role="progressbar"
            style="width: 95%"
            aria-valuenow="25"
            aria-valuemin="0"
            aria-valuemax="100"
          ></div>
        </div>
      </div>

      <div class="container-sm">
        <div class="text-container" style="font-weight: normal; color: #152242">
          <span>
            {{
              announData.rent == "Аренда" ? "Ценна и условия сделки" : "Цена"
            }}
          </span>
        </div>
        <div class="container w-100">
          <Pictures class="mt-[10px]" />
          <div class="group-container d-flex f-wrap flex-column mt-3">
            <label for="price" style="font-size: 14px" class="mb-2">{{
              announData.rent == "Аренда" ? "Аренда в месяц" : "Цена"
            }}</label>
            <div
              class="price-container d-flex align-items-center overflow-hidden form-control"
              style="width: 250px; height: 40px;"
              :style="!announData.price || announData.price < 100 ? 'border-color:red;color:red;':'border-color:green;color:green'"
            >
              <input
                @input="enterPrice"
                v-model="announData.price"
                type="tel"
                maxlength="9"
                autocomplete="off"
                id="price"
                style="border: none; outline: none"
              />
              <div
                class="text-[20px] d-flex justify-center bg-[white] top-[-1px] items-center w-[15%] relative right-[-30px]"
              >
                <span class="h-full flex leading-[40px]">c</span>
              </div>
            </div>
              <p v-if="!announData.price || announData.price < 100" class="text-[red] text-[14px]">Введите цена</p>

            <div class="balcon w-25 mt-3" v-if="announData.rent == 'Аренда'">
              <span
                for="#balcon"
                class="mb-2 text-nowrap"
                style="font-family: lato, sans-seif"
                >Условия проживания</span
              >
              <div
                class="d-flex text-nowrap mt-2"
                style="font-family: lato, sans-serif"
              >
                <label
                  v-for="items of cond"
                  :key="items.id"
                  :for="items.id"
                  class="me-2 "
                >
                  <input
                    @change="chooseConditions(items)"
                    :data-name="items.id"
                    type="checkbox"
                    :id="items.id"
                    :checked="items.name == announData.allowAnimals || items.name == announData.allowKids"
                    class="d-none"
                  />
                  <span class="form-control border-1">{{ items.name }}</span>
                </label>
              </div>
            </div>
            <div class="balcon" v-if="announData.rent == 'Аренда'">
              <p
                for="#balcon"
                class="mb-1 mt-2"
                style="font-family: lato, sans-seif"
              >
                Срок аренды
              </p>

              <div class="container-repair flex-wrap d-flex text-nowrap">
                <input
                  @change="choosePeriod"
                  type="radio"
                  class="btn-check"
                  data-name="От года"
                  name="options"
                  id="option5"
                  :checked="announData.period == 'От года'"
                  autocomplete="off"
                />
                <label class="form-control border-1 me-2 my-1" for="option5"
                 :style="!announData.period? 'border:1px solid  red;':''"
                  >От года</label
                >

                <input
                  @change="choosePeriod"
                  type="radio"
                  data-name="Несколько месяцев"
                  class="btn-check"
                  name="options"
                  id="option6"
                  :checked="announData.period == 'Несколько месяцев'"
                  autocomplete="off"
                />
                <label :style="!announData.period? 'border:1px solid red;':''" class="form-control border-1 me-2 my-1" for="option6"
                  >Несколько месяцев</label
                >
              </div>
            </div>
          </div>
        </div>
        <!-- <div draggable="false" class="w-full border flex flex-wrap rounded-[24px] min-h-[100px]">
                    <img class="w-[200px] h-[120px]" v-for="pic of image" :src="pic.url" alt="">
                 </div> -->
        <div class="container mt-4 mb-2 d-flex justify-content-end mx-1">
          <next-btn
            class="px-4 mx-4 btn btn-light text-primary"
            @click.prevent="navigateTo('/description')"
            >Назад</next-btn
          >
          <next-btn
            class="px-4 w-[200px] h-[38px] bg-primary"
            @click.prevent="place"
          >
            <div
            style="pointer-events: none;"
              v-if="loading"
              class="text-[white] spinner-border"
              role="status"
            >
              <span style="pointer-events: none;" class="sr-only">Loading...</span>
            </div>
            <span style="pointer-events: none;" class="text-[white]" v-if="!loading">Разместить</span>
          </next-btn>
        </div>
      </div>
    </div>
  </form>
</template>
<script setup>
const { announData } = getData();
announData.value = JSON.parse(localStorage.getItem('announ'))||{}
const loading = ref(false);

const priceObj = ref({});
const priceObj1 = ref({});
const price = ref();
const { images, isUpload, responce } = getData();
const minPrice = ref(0)
function enterPrice() {
  if (announData.value.price) {
    announData.value.price = parseInt(announData.value.price)
  }
  localStorage.setItem('announ',JSON.stringify(announData.value))
}

const allowKids = ref('')
const allowAnimals = ref('')
function chooseConditions(el) {
  if (event.target.checked) {
    parseInt(event.target.dataset.name);
    if (el.name == 'Можно с детьми') {
      announData.value.allowKids = el.name 
      localStorage.setItem('announ', JSON.stringify(announData.value))  
    }
    if (el.name == 'Можно с животными') {
      announData.value.allowAnimals = el.name 
      localStorage.setItem('announ', JSON.stringify(announData.value))
    }
  }

  if (!event.target.checked) {
    if (el.name == 'Можно с детьми') { 
      announData.value.allowKids = "нет"
      localStorage.setItem('announ', JSON.stringify(announData.value))
    }

    if (el.name == 'Можно с животными') { 
      announData.value.allowAnimals = 'нет'
      localStorage.setItem('announ', JSON.stringify(announData.value))
    }
      announData.value.allowAnimals = allowAnimals.value
  }
}
function choosePeriod(event) {
  announData.value.period = event.target.dataset.name
  localStorage.setItem('announ', JSON.stringify(announData.value))
}
const image = ref([]);
async function place(event) {
  if (!images.value.length) {
    return
  }else{
  event.target.disabled = true
  const progress = document.querySelector(".progress > .progress-bar");
  progress.style.width = "100%";
  // localStorage.setItem("announ", JSON.stringify(announData.value));
  const formD = new FormData();
  for (const item of images.value) {
    formD.append("images[]", item.file);
  }
    formD.append('announs',JSON.stringify(announData.value))
    formD.append("dataImage", JSON.stringify(images.value))

  loading.value = true;   
await fetch("http://127.0.0.1:8000/api/create/announ", {
    method: "post",
    headers: {
      Authorization:'Bearer '+JSON.parse(localStorage.getItem('token')) 
    },
    body:formD
  })
    .then((res) =>{
      if (res.ok) {
        navigateTo('/personal_area/my_announ')
      }
      return res.json()
    })
    .then((announ) => {
      console.log(announ);
    });
    loading.value = false;
    event.target.disabled = false
  }
  }

const arr = [];


const elems = document.querySelectorAll(".d-none");
elems.forEach((elem) => {
  arr.value.forEach((val) => {
    if (parseInt(elem.dataset.name) == val) {
      elem.checked = true;
    }
  });
});

const conditions = fetch("http://127.0.0.1:8000/api/conditions");
const condition = await conditions;
const c = ref(await condition.json());
const cond = ref(
  c.value.filter(
    (item) => item.name == "Можно с детьми" || item.name == "Можно с животными"
  )
);

const router = useRouter();
onMounted(() => {
  announData.value = JSON.parse(localStorage.getItem("announ"));
  // console.log(announData.value);
});
</script>

<style scoped>
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
@media screen and (min-width: 320px) {
  .progress {
    height: 7px;
    background-color: rgba(138, 187, 218, 0.24);
  }
  /* .progress-container{
     padding: 0 20px;
 } */
  .progress-bar {
    background-image: linear-gradient(
      to right,
      #ffe1ff,
      #006cfd 99.9%,
      #006cfd
    );
    border-top-right-radius: 5px;
    border-bottom-right-radius: 5px;
  }

  .text-container {
    margin-top: 16px;
    margin-left: 12px;
  }
  .text-container > span {
    font-weight: 500;
    font-size: 16px;
  }

  .container-repair {
    width: max-content;
  }
}

@media screen and (min-width: 640px) {
  .container-btn {
    display: flex;
    justify-content: flex-end;
  }
  .layouts {
    display: flex;
    flex-direction: column;
    margin: 0;
    justify-content: center;
    font-family: Lato, Arial, sans-serif;
  }
  .container-xl {
    height: max-content;
    display: flex;
    flex-direction: column;
  }
  .progress {
    height: 7px;
    background-color: rgba(138, 187, 218, 0.24);
  }
  /* .progress-container{
     padding: 0 20px;
 } */
  .progress-bar {
    background-image: linear-gradient(
      to right,
      #ffe1ff,
      #006cfd 99.9%,
      #006cfd
    );
    border-top-right-radius: 5px;
    border-bottom-right-radius: 5px;
  }

  .text-container {
    margin-top: 0px;
  }
  .text-container > span {
    font-weight: 700;
  }
}

.progress {
  height: 7px;
}
.progress-bar {
  background-image: linear-gradient(to right, #ffe1ff, #006cfd 99.9%, #006cfd);
  border-top-right-radius: 5px;
  border-bottom-right-radius: 5px;
}
.progress-container {
  padding: 0 20px;
}
@media screen and (min-width: 1024px) {
  .progress-container {
    width: 30%;
    padding: 15px;
  }
  .progress {
    height: 7px;
  }
  .container-xl {
    display: flex;
    max-width: 1300px;
    flex-direction: row;
    justify-content: space-between;
  }
  .justify-content-between > span {
    font-size: 14px;
  }
  .justify-content-between > button {
    display: none;
  }

  .container-sm {
    width: 67%;
    height: max-content;
    margin-top: 10px;
    margin-bottom: 20px;
    box-shadow: 0 0 30px rgba(0, 0, 0, 0.16);
    padding: 20px;
    border-radius: 8px;
  }
}
label > span {
  cursor: pointer;
  border: 2px solid rgb(230, 230, 230);
}
label > input:checked + span {
  background-color: rgb(230, 230, 230);
  border: 2px solid #152242;
}

.container-repair {
  width: 100%;
}

.container-repair > .form-control {
  width: max-content;
  cursor: pointer;
  border: 2px solid rgb(230, 230, 230);
}

.container-repair input:checked + .form-control {
  background-color: rgb(230, 230, 230);
  border: 2px solid #152242;
}
</style>
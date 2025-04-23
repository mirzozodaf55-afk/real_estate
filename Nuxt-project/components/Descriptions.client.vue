
<template>
  <form class="layouts">
    <div class="container-xl mt-4">
      <div class="progress-container container">
        <div class="d-flex justify-content-between mb-2 mt-3">
          <span class="fw-bold pt-1 2">Описание</span>
          <button class="btn btn-none fs-5 p-0 m-0 text-primary me-2">
            <font-awesome-icon :icon="['fas', 'circle-question']" />
          </button>
        </div>

        <div class="progress">
          <div
            class="progress-bar"
            role="progressbar"
            style="width: 86%"
            aria-valuenow="25"
            aria-valuemin="0"
            aria-valuemax="100"
          ></div>
        </div>
      </div>

      <div class="container-sm">
        <div class="text-container" style="font-weight: normal; color: #152242">
          <span>Описание объекта </span>
        </div>
        <div class="container w-100">
          <div class="mt-3 flex">
            <label
              for="exampleFormControlInput1"
              style="font-size: 14px; margin-bottom: 6px; color: #152242"
              class="form-label w-full"
            >
              <h6 class="text-[14px] mb-[3px]">Заголовок</h6>

              <div
                class="border-1 bg-red-500 rounded flex w-full h-[40px] overflow-hidden"
                :style="mooreTitle ? 'border-color:red; color:red' : 'border-color:green; color:green'"
              >
                <input
                  @input="writeTitle"
                  v-model="title"
                  class="w-full px-[10px] outline-0 border-0"
                  type="text"
                  autocomplete="off"
                  id="exampleFormControlInput1"
                  placeholder="Просторная видовая двушка у парка"
                />
                <span
                  style="
                    border-left: none;
                    border-radius: 8px;
                    border-top-left-radius: 0;
                    border-bottom-left-radius: 0;
                  "
                  class="relative px-[3px] flex text-[gray] items-center bg-[white]"
                >
                </span>
              </div>
            </label>
          </div>
          <div style="margin-top: 24px">
            <label
              for="floatingTextarea2"
              style="font-size: 14px; margin-bottom: 3px; color: #152242"
              >Описание</label
            >
            <textarea
              :style="mooreDescription ? 'border:1px solid red;color:red;' : 'border-color:green;color:green;'"
              @input="writeDescription"
              v-model="description"
              autocomplete="off"
              class="form-control"
              id="floatingTextarea2"
              placeholder="Уютная светлая двушка в тихом спальном районе. Окна на красивые цветущие деревья. Свежий ремонт 2020 года делали для себя. Рядом есть детсад. Можно заезжать и жить!"
              style="resize: none; height: 300px;box-shadow: none;"
            >
            </textarea>
            <p
              v-if="mooreDescription"
              :class="mooreDescription ? 'mt-[6px] text-[12px] text-[red]' : ''"
            >
              Напишите хотя бы пару предложений: от 15 до 3 000 букв
            </p>
          </div>
        </div>

        <div class="container mt-4 mb-2 d-flex justify-content-end mx-1">
          <next-btn
            class="px-4 mx-4 btn btn-light mx-4 text-primary"
            @click.prevent="back"
            >Назад</next-btn
          >
          <next-btn class="px-4 btn btn-primary" @click.prevent="next"
            >Далее</next-btn
          >
        </div>
      </div>
    </div>
  </form>
</template>
<script setup>
const { announData } = getData();

const message = ref({});
const message1 = ref({});
const title = ref("");
const description = ref("");
const mooreTitle = ref(false);
const mooreDescription = ref(false);

function change() {
  const file = document.querySelector(".choose-picture > input");
  file.click();
}

function writeTitle() {
  if (title.value.length < 6) {
    mooreTitle.value = true;
  } else mooreTitle.value = false;
  announData.value.title = title.value
  localStorage.setItem('announ',JSON.stringify(announData.value))
}

function writeDescription() {
  if (description.value.length < 15) {
    mooreDescription.value = true;
  } else {
    mooreDescription.value = false;
  }
  announData.value.description = description.value
  localStorage.setItem('announ',JSON.stringify(announData.value))

}

const route = useRoute();

function next() {
    if (announData.value.title.length < 6 || announData.value.description.length<15) {
      return
    }else navigateTo("/price");
        
    if (announData.value.objects == 'Участок') {
      localStorage.setItem("announ", JSON.stringify(announData.value));
    }
  }


        function back() {
          if (announData.value.rent == "Аренда" || announData.value.objects == 'Коттедж'|| announData.value.objects == 'Дом/Дача') {
            navigateTo("/technicsandfurniture");
          } else {
            navigateTo("/feature");
          }
          if (announData.value.objects == 'Участок') {
            navigateTo('/technicsandfurniture')
          }
        }




const router = useRouter();
onMounted(() => {
  announData.value = JSON.parse(localStorage.getItem('announ'))||{}

  if (announData.value.title && announData.value.title.length < 6 || !title.value && title.value.length < 6) {
    mooreTitle.value = true;
  } else mooreTitle.value = false;

  if (announData.value.description && announData.value.description.length < 15||!description.value && description.value.length < 15) {
    mooreDescription.value = true;
  } else {
    mooreDescription.value = false;
  }
  title.value = announData.value.title
  description.value = announData.value.description
});
</script>

<style scoped>
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
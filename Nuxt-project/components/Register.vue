<template>
  <div
    style="z-index: 150"
    class="fixed top-0 right-0 left-0 bottom-0 w-[100%] h-[100%] bg-black/50 flex justify-center items-center"
  >
    <div class="h-[max-content] p-[15px] w-[350px] bg-white relative flex-col">
      <div class="block h-[35px] flex items-center justify-end w-full relative">
        <font-awesome-icon
          @click="hideModal"
          class="text-[24px] h-full text-[#a1a7b3] font-thin absolute top-0 cursor-pointer"
          :icon="['fas', 'xmark']"
        />
      </div>
      <div
        style="font-family: Lato, Arial, sans-serif"
        class="w-full h-[max-content] mb-[24px] text-center mt-[20px] mx-[auto]"
      >
        <h3
        v-if="!sendDataUSer"
          style="white-space: wrap"
          class="text-[#152242] text-[22px] font-bold"
        >
          Войти или<br />
          зарегистрировать
        </h3>
        <h3
        v-if="sendDataUSer"
          style="white-space: wrap"
          class="text-[#152242] text-[20px] font-bold"
        >
          Подтверждение входа
        </h3>
        <span v-if="sendDataUSer" class="text-[14px] mt-[5px]">
          <div>Подтвердите номер телефона</div> 
        <div>{{tel.phone}} введите код из смс</div>
        <div @click="change"><a style="text-decoration:none;" class="text-primary">Изменить номер</a></div>
        </span>
      </div>
      <form class="w-[80%] h-[max-content] flex flex-col mx-auto">
        <input
          v-if="!sendDataUSer"
          v-maska
          data-maska="+(992) ##-###-##-##"
          v-model="tel.phone"
          class="form-control"
          type="text"
          style="box-shadow:none;"
          placeholder="phone"
        />
        <input
        v-if="sendDataUSer"
          v-maska
          v-model="verified"
          @input="getCode"
          data-maska="******"
          class="form-control text-[20px]"
          type="text"
          style="box-shadow:none;"
          placeholder="Код"
        />
        <span style="text-decoration:none;" v-if="time" class="text-center decoration-0 text-[silver] mt-[6px] text-[13px]">Отправить код повторно через <b>{{time}}</b> сек.</span>
        <a style="text-decoration:none;" v-if="!time && sendDataUSer" @click.prevent="createUser" class="text-center text-primary mt-[6px] text-[13px]">Отправить код повторно</a>
        <button
        v-if="sendDataUSer"
          @click.prevent
          style="font-family: Lato, Arial, sans-serif"
          class="mt-[25px] bg-primary text-white py-[5px] rounded-1 flex justify-center h-[40px] text-[rgba(13,110,253,1)] py-[6px] items-center"
          id="continue"
          :style="verified.length === 6 ?'opacity:0.5;':'opacity:1;'"
          >
        <span v-if="!loader">Продолжить</span> 
          
          <div v-if="loader" class="spinner-border" role="status">
            <span class="sr-only">Loading...</span>
          </div>
        </button>
        
        <button
        v-if="!sendDataUSer"
          @click.prevent="createUser"
          :class="tel.phone.length >= 19 ? 'bg-primary text-white py-[5px]' : 'bg-light'"
          style="font-family: Lato, Arial, sans-serif"
          class="mt-[10px] rounded-1 flex justify-center h-[40px] text-[rgba(13,110,253,1)] py-[6px] items-center"
        >
          <span v-if="!loader">Получить код</span> 
          
          <div v-if="loader" class="spinner-border" role="status">
            <span class="sr-only">Loading...</span>
          </div>
        </button>
        
        <button
        v-if="!sendDataUSer"
          style="font-family: Lato, Arial, sans-serif"
          class="btn-light text-primary btn mt-[10px]"
          @click.prevent
        >
          Другим способом
        </button>
      </form>

      <div class="help w-[80%] flex justify-center mx-auto mt-[70px] mb-[20px]">
        <a
          style="font-family: Lato, Arial, sans-serif"
          href="#"
          class="no-underline text-[14px]"
          >Нужна помощь?</a
        >
      </div>
    </div>
  </div>
</template>

<style scoped>
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}


</style>
<script setup>
const loader = ref(false);
const { responce,direction } = getData();
const { showMadoal,active } = useSwitch();
const verified = ref('')
function hideModal() {
  const { showMadoal, active } = useSwitch();
  document.body.style.overflow = "scroll";
  tel.value = "";
  showMadoal.value = false;
}
const sendDataUSer = ref(false)

const tel = ref({
  phone: "(992)",
});
let time = ref(0)
async function createUser() {
  loader.value = true;
  verified.value =''
  if (tel.value.phone.length >= 19) {
    await fetch("http://127.0.0.1:8000/api/create_user", {
      method: "post",
      headers: {
        "Content-type": "application/json",
      },
      body: JSON.stringify({
        phone: tel.value.phone.replace(/[^0-9]/g, "").slice(3, 12),
      }),
    })
      .then((res) => {
        if (res.ok) {
          time.value = 59
          // window.location.replace(direction.value)
          // active.value = false
          sendDataUSer.value = true
          let timer = setInterval(()=>{
          time.value--;
          if (time.value < 1) {
            // sendDataUSer.value = false;
            clearInterval(timer); // Остановка таймера, когда time.value достигает 0
            timer = null; // Сброс таймера
          }
          },1000)
        } else{
          sendDataUSer.value = false
        }
        return res.json();
      })
      .then((res) => {
        // console.log(res);
        setTimeout(()=>{
          verified.value = res[0]
          responce.value = res[1];
        localStorage.setItem("token", JSON.stringify(responce.value));
        console.log(res);
        
        },3000)
      });
  }
  // else{
  //   showMadoal.value = false
  // }
  // tel.value.phone = "";
  loader.value = false;
}
async function getCode(){
  if (verified.value.length === 6) {
  loader.value = true;
    await fetch("http://127.0.0.1:8000/api/verify-user", {
      method: "post",
      headers: {
        "Content-type": "application/json",
      },
      body: JSON.stringify({
        code: parseInt(verified.value),
        phone:tel.value.phone.replace(/[^0-9]/g, "").slice(3, 12),
      }),
    })
      .then((res) =>res.json())
      .then((res) => {
        
        // showMadoal.value = false;
        responce.value = res;
        window.location.replace(direction.value)
        // localStorage.setItem("owner", JSON.stringify(responce.value));
        
      });
  loader.value = false;
  }
}
  function change(){
    sendDataUSer.value = false
    time.value = 1
    tel.value.phone = '+(992)'
  }
// }
</script>
<style scoped>
.button {
  position: relative;
  padding: 8px 16px;
  background: #009579;
  border: none;
  outline: none;
  border-radius: 2px;
  cursor: pointer;
}

.button:active {
  background: #007a63;
}

.button__text {
  font: bold 20px "Quicksand", san-serif;
  color: #ffffff;
  transition: all 0.2s;
}

.button--loading .button__text {
  visibility: hidden;
  opacity: 0;
}

.button--loading::after {
  content: "";
  position: absolute;
  width: 16px;
  height: 16px;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  margin: auto;
  border: 4px solid transparent;
  border-top-color: #ffffff;
  border-radius: 50%;
  animation: button-loading-spinner 1s ease infinite;
}

@keyframes button-loading-spinner {
  from {
    transform: rotate(0turn);
  }

  to {
    transform: rotate(1turn);
  }
}




</style>
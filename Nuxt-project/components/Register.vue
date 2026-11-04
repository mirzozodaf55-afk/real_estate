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
          {{ t('Войти или') }}<br />
          {{ t('зарегистрировать') }}
        </h3>
        <h3
        v-if="sendDataUSer"
          style="white-space: wrap"
          class="text-[#152242] text-[20px] font-bold"
        >
          {{ t('Подтверждение входа') }}
        </h3>
        <span v-if="sendDataUSer" class="text-[14px] mt-[5px]">
          <div>{{ t('Мы отправили код на почту') }}</div>
        <div><b>{{ email }}</b></div>
        <div class="text-[13px] text-[#94a3b8]">{{ t('Не пришло? Проверьте папку «Спам»') }}</div>
        <div @click="change"><a style="text-decoration:none;" class="text-primary cursor-pointer">{{ t('Изменить почту') }}</a></div>
        </span>
      </div>
      <!-- Публичное демо: письма с кодом на бесплатном хостинге не уходят — вход одной кнопкой под готовой ролью -->
      <div v-if="demo" class="w-[85%] mx-auto flex flex-col gap-[8px]" data-test="demo-login">
        <p class="text-center text-[14px] text-[#64748b] mb-[4px]">{{ t('Демо без регистрации — выберите, кем войти') }}</p>
        <button v-for="r of demoRoles()" :key="r.role" type="button" @click="loginDemo(r.role)" :disabled="!!demoBusy"
          class="text-left rounded-[10px] border border-[#e2e8f0] bg-white hover:border-[#2563eb] hover:bg-[#f8fafc] px-[14px] py-[10px] disabled:opacity-60">
          <span class="block text-[15px] font-semibold text-[#152242]">{{ demoBusy === r.role ? '…' : t(r.label) }}</span>
          <span class="block text-[13px] text-[#64748b]">{{ t(r.hint) }}</span>
        </button>
        <p v-if="error" class="text-[red] text-[13px] text-center mt-[6px] mb-0">{{ t(error) }}</p>
      </div>
      <form v-else class="w-[80%] h-[max-content] flex flex-col mx-auto" @submit.prevent="sendDataUSer ? getCode() : createUser()">
        <input
          v-if="!sendDataUSer"
          v-model.trim="email"
          class="form-control"
          type="email"
          autocomplete="email"
          style="box-shadow:none;"
          :placeholder="t('Электронная почта')"
        />
        <input
        v-if="sendDataUSer"
          v-maska
          v-model="verified"
          @input="getCode"
          data-maska="######"
          inputmode="numeric"
          autocomplete="one-time-code"
          class="form-control text-[20px] text-center tracking-[6px]"
          type="text"
          style="box-shadow:none;"
          :placeholder="t('Код')"
        />
        <p v-if="error" class="text-[red] text-[13px] text-center mt-[6px] mb-0">{{ t(error) }}</p>
        <span style="text-decoration:none;" v-if="time" class="text-center decoration-0 text-[silver] mt-[6px] text-[13px]">{{ t('Отправить код повторно через') }} <b>{{time}}</b> {{ t('сек.') }}</span>
        <a style="text-decoration:none;" v-if="!time && sendDataUSer" @click.prevent="createUser" class="text-center text-primary mt-[6px] text-[13px]">{{ t('Отправить код повторно') }}</a>
        <button
        v-if="sendDataUSer"
          @click.prevent="getCode"
          :disabled="verified.length !== 6 || loader"
          style="font-family: Lato, Arial, sans-serif"
          class="mt-[25px] bg-primary text-white py-[5px] rounded-1 flex justify-center h-[40px] text-[rgba(13,110,253,1)] py-[6px] items-center"
          id="continue"
          :style="verified.length === 6 ?'opacity:1;':'opacity:0.5;'"
          >
        <span v-if="!loader">{{ t('Продолжить') }}</span> 
          
          <div v-if="loader" class="spinner-border" role="status">
            <span class="sr-only">Loading...</span>
          </div>
        </button>
        
        <button
        v-if="!sendDataUSer"
          @click.prevent="createUser"
          :disabled="!emailValid || loader"
          :class="emailValid ? 'bg-primary text-white py-[5px]' : 'bg-light'"
          style="font-family: Lato, Arial, sans-serif"
          class="mt-[10px] rounded-1 flex justify-center h-[40px] text-[rgba(13,110,253,1)] py-[6px] items-center"
        >
          <span v-if="!loader">{{ t('Получить код') }}</span> 
          
          <div v-if="loader" class="spinner-border" role="status">
            <span class="sr-only">Loading...</span>
          </div>
        </button>
        <!-- Вход через Google / Apple: кнопки только для настроенных на сервере провайдеров (/api/auth/providers) -->
        <template v-if="!sendDataUSer && (providers.google || providers.apple)">
          <div class="flex items-center gap-[10px] mt-[18px] mb-[12px] text-[13px] text-[#94a3b8]">
            <span class="flex-1 h-px bg-[#e2e8f0]"></span>{{ t('или') }}<span class="flex-1 h-px bg-[#e2e8f0]"></span>
          </div>
          <button v-if="providers.google" type="button" @click="loginGoogle" :disabled="!!socialBusy" data-test="login-google"
            class="h-[44px] rounded-[10px] border border-[#e2e8f0] bg-white hover:bg-[#f8fafc] flex items-center justify-center gap-[10px] text-[15px] font-semibold text-[#152242] disabled:opacity-60">
            <font-awesome-icon v-if="socialBusy === 'google'" :icon="['fas', 'spinner']" spin-pulse />
            <svg v-else width="20" height="20" viewBox="0 0 48 48" aria-hidden="true">
              <path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3C33.7 32.7 29.2 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.2 7.9 3.1l5.7-5.7C34 6.1 29.3 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.4-.4-3.5z"/>
              <path fill="#FF3D00" d="m6.3 14.7 6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.8 1.2 7.9 3.1l5.7-5.7C34 6.1 29.3 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/>
              <path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2C29.2 35.1 26.7 36 24 36c-5.2 0-9.6-3.3-11.3-8l-6.5 5C9.5 39.6 16.2 44 24 44z"/>
              <path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-.8 2.2-2.2 4.2-4.1 5.6l6.2 5.2C37 39.2 44 34 44 24c0-1.3-.1-2.4-.4-3.5z"/>
            </svg>
            {{ t('Продолжить с Google') }}
          </button>
          <button v-if="providers.apple" type="button" @click="loginApple" :disabled="!!socialBusy" data-test="login-apple"
            class="apple-btn mt-[10px] h-[44px] rounded-[10px] bg-black hover:bg-[#1f1f1f] flex items-center justify-center gap-[10px] text-[15px] font-semibold text-white disabled:opacity-60">
            <font-awesome-icon v-if="socialBusy === 'apple'" :icon="['fas', 'spinner']" spin-pulse />
            <svg v-else width="18" height="18" viewBox="0 0 384 512" fill="currentColor" aria-hidden="true">
              <path d="M318.7 268.7c-.2-36.7 16.4-64.4 50-84.8-18.8-26.9-47.2-41.7-84.7-44.6-35.5-2.8-74.3 20.7-88.5 20.7-15 0-49.4-19.7-76.4-19.7C63.3 141.2 4 184.8 4 273.5q0 39.3 14.4 81.2c12.8 36.7 59 126.7 107.2 125.2 25.2-.6 43-17.9 75.8-17.9 31.8 0 48.3 17.9 76.4 17.9 48.6-.7 90.4-82.5 102.6-119.3-65.2-30.7-61.7-90-61.7-91.9zm-56.6-164.2c27.3-32.4 24.8-61.9 24-72.5-24.1 1.4-52 16.4-67.9 34.9-17.5 19.8-27.8 44.3-25.6 71.9 26.1 2 49.9-11.4 69.5-34.3z"/>
            </svg>
            {{ t('Продолжить с Apple') }}
          </button>
        </template>
      </form>

      <div class="help w-[80%] flex justify-center mx-auto mt-[70px] mb-[20px]">
        <a
          style="font-family: Lato, Arial, sans-serif"
          href="#"
          class="no-underline text-[14px]"
          >{{ t('Нужна помощь?') }}</a
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
  document.body.style.overflow = "scroll";
  email.value = "";
  showMadoal.value = false;
}
const sendDataUSer = ref(false)

const email = ref("")
const error = ref("")
const emailValid = computed(() => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value))
let time = ref(0)
let timer = null
const token = useCookie('token')

async function readMessage(res, fallback) {
  try {
    const data = await res.json()
    return data.message || fallback
  } catch (e) {
    return fallback
  }
}

// Шаг 1: просим сервер отправить код на почту. В ответ сервер больше ничего секретного не отдаёт.
async function createUser() {
  if (!emailValid.value || loader.value) return
  error.value = ''
  verified.value = ''
  loader.value = true
  try {
    const res = await fetch(`${apiBase()}/api/create_user`, {
      method: "post",
      headers: { "Content-type": "application/json", Accept: "application/json" },
      body: JSON.stringify({ email: email.value }),
    })
    if (!res.ok) {
      error.value = res.status === 429
        ? 'Слишком много запросов, подождите минуту'
        : await readMessage(res, 'Не удалось отправить код')
      return
    }
    sendDataUSer.value = true
    time.value = 59
    clearInterval(timer)
    timer = setInterval(() => {
      time.value--
      if (time.value < 1) {
        clearInterval(timer)
        timer = null
      }
    }, 1000)
  } catch (e) {
    error.value = 'Нет связи с сервером'
  } finally {
    loader.value = false
  }
}

// Шаг 2: проверяем код — только теперь сервер выдаёт токен.
async function getCode(){
  if (verified.value.length !== 6 || loader.value) return
  error.value = ''
  loader.value = true
  try {
    const res = await fetch(`${apiBase()}/api/verify-user`, {
      method: "post",
      headers: { "Content-type": "application/json", Accept: "application/json" },
      body: JSON.stringify({ email: email.value, code: verified.value }),
    })
    if (!res.ok) {
      error.value = await readMessage(res, 'Неверный код')
      verified.value = ''
      return
    }
    const data = await res.json()
    localStorage.setItem("token", JSON.stringify(data.token))
    token.value = data.token
    responce.value = data.user
    window.location.replace(direction.value || '/')
  } catch (e) {
    error.value = 'Нет связи с сервером'
  } finally {
    loader.value = false
  }
}
// --- Публичное демо: вход под готовым аккаунтом (composables/useDemo.js)
const demo = isDemo()
const demoBusy = ref('')
async function loginDemo(role) {
  if (demoBusy.value) return
  demoBusy.value = role
  error.value = ''
  try {
    await demoLogin(role)
  } catch (e) {
    error.value = e.message
    demoBusy.value = ''
  }
}

// --- Вход через Google / Apple
const providers = ref({ google: null, apple: null })
const socialBusy = ref('')
onMounted(async () => {
  try {
    const res = await fetch(`${apiBase()}/api/auth/providers`, { headers: { Accept: 'application/json' } })
    if (res.ok) providers.value = await res.json()
  } catch (e) { /* без провайдеров остаётся вход по коду */ }
})

function loadScript(src) {
  return new Promise((resolve, reject) => {
    if (document.querySelector(`script[src="${src}"]`)) return resolve()
    const el = document.createElement('script')
    el.src = src
    el.onload = resolve
    el.onerror = () => reject(new Error('script'))
    document.head.appendChild(el)
  })
}

// после провайдера — то же, что после кода из письма
async function finishSocial(path, body) {
  const res = await fetch(`${apiBase()}/api${path}`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
    body: JSON.stringify(body),
  })
  if (!res.ok) {
    error.value = await readMessage(res, 'Не удалось войти, попробуйте ещё раз')
    return
  }
  const data = await res.json()
  localStorage.setItem('token', JSON.stringify(data.token))
  token.value = data.token
  responce.value = data.user
  window.location.replace(direction.value || '/')
}

async function loginGoogle() {
  error.value = ''
  // Google пускает только https или localhost — по IP в локальной сети он покажет «origin_mismatch»
  if (location.protocol !== 'https:' && location.hostname !== 'localhost') {
    error.value = `Вход через Google работает только по адресу http://localhost:${location.port || 80} или на сайте с https. Сейчас открыт ${location.host} — войдите по коду из письма.`
    return
  }
  socialBusy.value = 'google'
  try {
    await loadScript('https://accounts.google.com/gsi/client')
    // окно Google со своей кнопкой: получаем access_token, сервер сам спросит у Google, кто это
    const client = window.google.accounts.oauth2.initTokenClient({
      client_id: providers.value.google,
      scope: 'openid email profile',
      callback: async (resp) => {
        try {
          if (resp.error || !resp.access_token) {
            error.value = 'Вход через Google отменён'
            return
          }
          await finishSocial('/auth/google', { access_token: resp.access_token })
        } catch (e) {
          error.value = 'Нет связи с сервером'
        } finally {
          socialBusy.value = ''
        }
      },
      error_callback: (e) => {
        // закрыли окно или браузер заблокировал всплывающее окно
        error.value = e?.type === 'popup_failed_to_open' ? 'Браузер заблокировал окно Google — разрешите всплывающие окна' : 'Вход через Google отменён'
        socialBusy.value = ''
      },
    })
    client.requestAccessToken()
  } catch (e) {
    error.value = 'Google сейчас недоступен'
    socialBusy.value = ''
  }
}

async function loginApple() {
  error.value = ''
  socialBusy.value = 'apple'
  try {
    await loadScript('https://appleid.cdn-apple.com/appleauth/static/jsapi/appleid/1/ru_RU/appleid.auth.js')
    window.AppleID.auth.init({
      clientId: providers.value.apple.client_id,
      redirectURI: providers.value.apple.redirect,
      scope: 'name email',
      usePopup: true,
    })
    const resp = await window.AppleID.auth.signIn()
    await finishSocial('/auth/apple', {
      id_token: resp.authorization.id_token,
      first_name: resp.user?.name?.firstName,
      last_name: resp.user?.name?.lastName,
    })
  } catch (e) {
    // popup_closed_by_user — просто закрыли окно
    if (e?.error !== 'popup_closed_by_user') error.value = 'Не удалось войти через Apple'
  } finally {
    socialBusy.value = ''
  }
}

  function change(){
    sendDataUSer.value = false
    error.value = ''
    verified.value = ''
    time.value = 0
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
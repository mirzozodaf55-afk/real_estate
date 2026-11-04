// Язык сайта: читаем из cookie до первой отрисовки (и на сервере, и в браузере), при смене — пишем обратно и меняем <html lang>
export default defineNuxtPlugin(() => {
  const cookie = useCookie('lang', { maxAge: 60 * 60 * 24 * 365, sameSite: 'lax' })
  const { lang } = useLang()
  if (LANGS.some((l) => l.code === cookie.value)) lang.value = cookie.value
  // публичное демо смотрят заказчики с Upwork — без сохранённого выбора открываем по-английски
  else if (useRuntimeConfig().public.demoMode) lang.value = 'en'

  watch(lang, (code) => { cookie.value = code })
  useHead({ htmlAttrs: { lang: computed(() => lang.value) } })
})

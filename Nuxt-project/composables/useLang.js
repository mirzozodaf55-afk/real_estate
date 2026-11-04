// Языки сайта: русский (основной) и английский. Выбор хранится в cookie 'lang' (plugins/lang.js).
// Ключ перевода — сам русский текст: t('Найти') → 'Search'. Нет перевода — показываем русский, ничего не ломается.
// Значения из базы («Продажа», «Квартира»…) остаются русскими — переводим только подписи: t(item.deal_type).
import en from '~/locales/en.js'

export const LANGS = [
  { code: 'ru', label: 'Русский', short: 'RU' },
  { code: 'en', label: 'English', short: 'EN' },
]
const DICTS = { en }

// useState — своё значение на каждый запрос (SSR), без утечки языка между посетителями
const langState = () => useState('lang', () => 'ru')

/** t('Привет, {name}!', { name }) — перевод и подстановка {…} */
export function t(text, params) {
  if (text === null || text === undefined) return ''
  const key = String(text)
  const lang = langState().value
  let out = (lang !== 'ru' && DICTS[lang]?.[key]) || key
  if (params) out = out.replace(/\{(\w+)\}/g, (m, name) => (params[name] ?? m))
  return out
}

/** Множественное число: tn(5, ['объявление', 'объявления', 'объявлений']) → «объявлений» / «listings» */
export function tn(n, forms) {
  if (langState().value === 'en') {
    const one = DICTS.en[forms[0]] || forms[0]
    const many = DICTS.en[forms[2]] || DICTS.en[forms[1]] || forms[2]
    return Math.abs(n) === 1 ? one : many
  }
  return plural(n, forms)
}

/**
 * Текст уведомления с сервера: «VIP до 10.11.2026», «Сомнительные фото: <вывод ИИ>» — сервер пишет по-русски,
 * переводим известную часть, вывод ИИ о фото берём английский (announ.photo_check.summary_en), если есть
 */
export function tNotice(n) {
  const msg = String(n?.message ?? '')
  if (langState().value === 'ru') return msg
  const promo = msg.match(/^(VIP|ТОП) до (.+)$/)
  if (promo) return t('{x} до {d}', { x: t(promo[1]), d: promo[2] })
  const photo = msg.match(/^(Фото не соответствуют объявлению|Сомнительные фото): /)
  if (photo) return `${t(photo[1])}: ${n.announ?.photo_check?.summary_en || msg.slice(photo[0].length)}`
  return t(msg)
}

/** Превью сообщения чата с сервера: «📷 Фото» / «🎬 Видео» переводим, текст собеседника — нет */
export function tPreview(text) {
  return ['📷 Фото', '🎬 Видео'].includes(text) ? t(text) : text
}

export function useLang() {
  const lang = langState()
  return {
    lang,
    setLang: (code) => { if (LANGS.some((l) => l.code === code)) lang.value = code },
    locale: computed(() => (lang.value === 'en' ? 'en-US' : 'ru-RU')),
    t,
    tn,
  }
}

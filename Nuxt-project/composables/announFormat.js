// Форматирование для объявлений, уведомлений и сохранённых поисков — на языке сайта (t, tn — composables/useLang.js)

export function plural(n, forms) {
  const mod10 = n % 10
  const mod100 = n % 100
  if (mod10 === 1 && mod100 !== 11) return forms[0]
  if (mod10 >= 2 && mod10 <= 4 && (mod100 < 10 || mod100 >= 20)) return forms[1]
  return forms[2]
}

export function formatNumber(n) {
  return n === null || n === undefined || n === '' ? '' : Number(n).toLocaleString(useLang().locale.value)
}

export function priceSuffix(announ) {
  if (announ?.deal_type !== 'Аренда') return ''
  return ' ' + t(announ.rental_type === 'Посуточно' ? '/ сутки' : '/ мес')
}

export function dealLabel(announ) {
  if (announ?.deal_type === 'Аренда') return t(announ.rental_type === 'Посуточно' ? 'Посуточно' : 'Аренда')
  return t(announ?.deal_type || 'Объявление')
}

// «2-комн. · 54 м² · 5/9 эт.» — для карточек объявлений
export function announFacts(a) {
  const parts = []
  if (a.quantity_room) parts.push(t('{n}-комн.', { n: a.quantity_room }))
  else if (a.quantity_bedrooms) parts.push(`${a.quantity_bedrooms} ${tn(a.quantity_bedrooms, ['спальня', 'спальни', 'спален'])}`)
  const area = a.total_area || a.house_area || a.building_area
  if (area) parts.push(`${area} ${t('м²')}`)
  if (a.land_area) parts.push(t('участок {n} сот.', { n: a.land_area }))
  if (a.floor && a.floor_in_house) parts.push(t('{a}/{b} эт.', { a: a.floor, b: a.floor_in_house }))
  else if (a.floor_in_house) parts.push(`${a.floor_in_house} ${tn(a.floor_in_house, ['этаж', 'этажа', 'этажей'])}`)
  return parts.join(' · ')
}

/** «5 мин. назад»; ago = false — без «назад» («ждёт 5 мин.») */
export function timeAgo(dateString, ago = true) {
  const minutes = Math.floor((Date.now() - new Date(dateString).getTime()) / 60000)
  const suffix = (s) => (ago ? t('{x} назад', { x: s }) : s)
  if (minutes < 1) return t('только что')
  if (minutes < 60) return suffix(t('{n} мин.', { n: minutes }))
  const hours = Math.floor(minutes / 60)
  if (hours < 24) return suffix(t('{n} ч.', { n: hours }))
  const days = Math.floor(hours / 24)
  if (days < 7) return suffix(`${days} ${tn(days, ['день', 'дня', 'дней'])}`)
  if (days < 30) return suffix(t('{n} нед.', { n: Math.floor(days / 7) }))
  return new Date(dateString).toLocaleDateString(useLang().locale.value, { day: 'numeric', month: 'long', year: 'numeric' })
}

const DEAL_LABELS = { 'Продажа': 'Купить', 'Аренда': 'Снять', 'Посуточно': 'Посуточно' }
const MAIN_KEYS = ['dealType', 'typeObject', 'buildingType', 'quantityRoom', 'priceFrom', 'priceTo', 'min_price', 'max_price', 'q', 'typeRealEstate']

// Человекочитаемое описание сохранённого поиска: «Снять · Квартира · 1, 2 комн. · до 5 000 сомони»
export function describeFilters(f = {}) {
  const asArray = (v) => (v === undefined || v === null || v === '' ? [] : Array.isArray(v) ? v : [v])
  const parts = []
  if (f.dealType) parts.push(t(DEAL_LABELS[f.dealType] || f.dealType))
  if (f.typeObject) parts.push(t(f.typeObject))
  else if (asArray(f.buildingType).length) parts.push(asArray(f.buildingType).map((x) => t(x)).join(', '))
  if (f.typeRealEstate && !f.typeObject) parts.push(t(f.typeRealEstate))
  const rooms = asArray(f.quantityRoom).map(String).sort()
  if (rooms.length) parts.push(t('{n} комн.', { n: rooms.map((r) => (r === '4' ? '4+' : r)).join(', ') }))
  const from = f.priceFrom ?? f.min_price
  const to = f.priceTo ?? f.max_price
  if (from && to) parts.push(t('{a}–{b} сомони', { a: formatNumber(from), b: formatNumber(to) }))
  else if (from) parts.push(t('от {n} сомони', { n: formatNumber(from) }))
  else if (to) parts.push(t('до {n} сомони', { n: formatNumber(to) }))
  if (f.q) parts.push(`«${f.q}»`)
  const extra = Object.keys(f).filter((k) => !MAIN_KEYS.includes(k) && asArray(f[k]).length).length
  if (extra) parts.push(`+${extra} ${tn(extra, ['фильтр', 'фильтра', 'фильтров'])}`)
  return parts.join(' · ') || t('Все объявления')
}

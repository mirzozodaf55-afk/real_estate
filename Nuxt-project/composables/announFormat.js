// Форматирование для объявлений, уведомлений и сохранённых поисков

export function plural(n, forms) {
  const mod10 = n % 10
  const mod100 = n % 100
  if (mod10 === 1 && mod100 !== 11) return forms[0]
  if (mod10 >= 2 && mod10 <= 4 && (mod100 < 10 || mod100 >= 20)) return forms[1]
  return forms[2]
}

export function formatNumber(n) {
  return n === null || n === undefined || n === '' ? '' : Number(n).toLocaleString('ru-RU')
}

export function priceSuffix(announ) {
  if (announ?.deal_type !== 'Аренда') return ''
  return announ.rental_type === 'Посуточно' ? ' / сутки' : ' / мес'
}

export function dealLabel(announ) {
  if (announ?.deal_type === 'Аренда') return announ.rental_type === 'Посуточно' ? 'Посуточно' : 'Аренда'
  return announ?.deal_type || 'Объявление'
}

// «2-комн. · 54 м² · 5/9 эт.» — для карточек объявлений
export function announFacts(a) {
  const parts = []
  if (a.quantity_room) parts.push(`${a.quantity_room}-комн.`)
  else if (a.quantity_bedrooms) parts.push(`${a.quantity_bedrooms} ${plural(a.quantity_bedrooms, ['спальня', 'спальни', 'спален'])}`)
  const area = a.total_area || a.house_area || a.building_area
  if (area) parts.push(`${area} м²`)
  if (a.land_area) parts.push(`участок ${a.land_area} сот.`)
  if (a.floor && a.floor_in_house) parts.push(`${a.floor}/${a.floor_in_house} эт.`)
  else if (a.floor_in_house) parts.push(`${a.floor_in_house} ${plural(a.floor_in_house, ['этаж', 'этажа', 'этажей'])}`)
  return parts.join(' · ')
}

export function timeAgo(dateString) {
  const minutes = Math.floor((Date.now() - new Date(dateString).getTime()) / 60000)
  if (minutes < 1) return 'только что'
  if (minutes < 60) return `${minutes} мин. назад`
  const hours = Math.floor(minutes / 60)
  if (hours < 24) return `${hours} ч. назад`
  const days = Math.floor(hours / 24)
  if (days < 7) return `${days} ${plural(days, ['день', 'дня', 'дней'])} назад`
  if (days < 30) return `${Math.floor(days / 7)} нед. назад`
  return new Date(dateString).toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' })
}

const DEAL_LABELS = { 'Продажа': 'Купить', 'Аренда': 'Снять', 'Посуточно': 'Посуточно' }
const MAIN_KEYS = ['dealType', 'typeObject', 'buildingType', 'quantityRoom', 'priceFrom', 'priceTo', 'min_price', 'max_price', 'q', 'typeRealEstate']

// Человекочитаемое описание сохранённого поиска: «Снять · Квартира · 1, 2 комн. · до 5 000 сомони»
export function describeFilters(f = {}) {
  const asArray = (v) => (v === undefined || v === null || v === '' ? [] : Array.isArray(v) ? v : [v])
  const parts = []
  if (f.dealType) parts.push(DEAL_LABELS[f.dealType] || f.dealType)
  if (f.typeObject) parts.push(f.typeObject)
  else if (asArray(f.buildingType).length) parts.push(asArray(f.buildingType).join(', '))
  if (f.typeRealEstate && !f.typeObject) parts.push(f.typeRealEstate)
  const rooms = asArray(f.quantityRoom).map(String).sort()
  if (rooms.length) parts.push(`${rooms.map((r) => (r === '4' ? '4+' : r)).join(', ')} комн.`)
  const from = f.priceFrom ?? f.min_price
  const to = f.priceTo ?? f.max_price
  if (from && to) parts.push(`${formatNumber(from)}–${formatNumber(to)} сомони`)
  else if (from) parts.push(`от ${formatNumber(from)} сомони`)
  else if (to) parts.push(`до ${formatNumber(to)} сомони`)
  if (f.q) parts.push(`«${f.q}»`)
  const extra = Object.keys(f).filter((k) => !MAIN_KEYS.includes(k) && asArray(f[k]).length).length
  if (extra) parts.push(`+${extra} ${plural(extra, ['фильтр', 'фильтра', 'фильтров'])}`)
  return parts.join(' · ') || 'Все объявления'
}

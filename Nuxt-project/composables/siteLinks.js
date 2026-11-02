// Разделы сайта — одни и те же для мобильной главной, бокового меню и верхнего меню.
// Ведут в список объявлений (/list-announ) с готовым фильтром — те же параметры понимает сервер (AnnounFilter).
export const CATEGORY_LINKS = [
  { label: 'Купить', query: { dealType: 'Продажа' }, icon: 'tag' },
  { label: 'Снять', query: { dealType: 'Аренда' }, icon: 'key' },
  { label: 'Посуточно', query: { dealType: 'Посуточно' }, icon: 'calendar-day' },
  { label: 'Новостройки', query: { dealType: 'Продажа', typeObject: 'Квартира в Новостройке' }, icon: 'city' },
  { label: 'Дома и дачи', query: { typeObject: 'Дом/Дача' }, icon: 'house' },
  { label: 'Коммерческая', query: { typeRealEstate: 'Коммерческая' }, icon: 'briefcase' },
]

export const categoryRoute = (c) => ({ path: '/list-announ', query: c.query })

// Быстрые ссылки главной (и мобильной, и десктопной)
const BUY = { dealType: 'Продажа' }
const RENT = { dealType: 'Аренда' }
export const QUICK_LINKS = [
  { title: 'Купить квартиру', links: [
    { label: '1-комнатные', query: { ...BUY, typeObject: 'Квартира', quantityRoom: '1' } },
    { label: '2-комнатные', query: { ...BUY, typeObject: 'Квартира', quantityRoom: '2' } },
    { label: '3-комнатные', query: { ...BUY, typeObject: 'Квартира', quantityRoom: '3' } },
    { label: 'В новостройках', query: { ...BUY, typeObject: 'Квартира в Новостройке' } },
  ] },
  { title: 'Снять квартиру', links: [
    { label: '1-комнатные', query: { ...RENT, typeObject: 'Квартира', quantityRoom: '1' } },
    { label: '2-комнатные', query: { ...RENT, typeObject: 'Квартира', quantityRoom: '2' } },
    { label: 'Комнаты', query: { ...RENT, typeObject: 'Комната' } },
  ] },
  { title: 'Посуточно', links: [
    { label: 'Квартиры посуточно', query: { dealType: 'Посуточно', typeObject: 'Квартира' } },
    { label: 'Дома посуточно', query: { dealType: 'Посуточно', typeObject: 'Дом/Дача' } },
  ] },
  { title: 'Коммерческая недвижимость', links: [
    { label: 'Офисы', query: { typeObject: 'Офис' } },
    { label: 'Торговые площади', query: { typeObject: 'Торговая площадь' } },
    { label: 'Склады', query: { typeObject: 'Склад' } },
  ] },
]

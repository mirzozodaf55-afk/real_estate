// Черновик подачи объявления: общий на все шаги (getData().announData) и сохраняется в localStorage 'announ',
// чтобы после обновления страницы или возврата на шаг назад ничего не терялось.
// Ключи (rent, objects, generalArea, floorHouse…) — те, что ждёт сервер (CreateAnnounController), не переименовывать.

export const FLATS = ['Квартира', 'Комната', 'Квартира в Новостройке']
export const HOUSES = ['Дом/Дача', 'Коттедж', 'Часть дома', 'Дом']

export function useAnnounDraft() {
  const { announData } = getData()
  if (process.client) {
    try {
      announData.value = JSON.parse(localStorage.getItem('announ')) || {}
    } catch (e) {
      announData.value = {}
    }
    watch(announData, (v) => {
      try { localStorage.setItem('announ', JSON.stringify(v || {})) } catch (e) { /* приватный режим */ }
    }, { deep: true })
  }
  return announData
}

/** flat | house | land | commercial — от этого зависят шаги и поля */
export function announKind(d) {
  if (d?.Estate === 'Коммерческая') return 'commercial'
  if (d?.objects === 'Участок') return 'land'
  if (HOUSES.includes(d?.objects)) return 'house'
  return 'flat'
}

/** Шаги для текущего типа объекта — по ним строится список слева и кнопки «Назад/Далее» */
export function announSteps(d) {
  const kind = announKind(d)
  const steps = [{ path: '/announ', label: 'Тип объявления' }]
  if (kind === 'commercial') return [...steps, { path: '/office', label: 'Объект, фото и цена' }]
  steps.push({ path: '/map', label: kind === 'land' ? 'Где участок' : 'Адрес и дом' })
  if (kind === 'flat') {
    steps.push({ path: '/pictures', label: 'Комнаты и площадь' }, { path: '/feature', label: 'Ремонт и удобства' })
    if (d?.rent === 'Аренда') steps.push({ path: '/technicsandfurniture', label: 'Мебель и техника' })
  } else {
    steps.push({ path: '/technicsandfurniture', label: kind === 'land' ? 'Участок' : 'Дом и участок' })
  }
  steps.push({ path: '/description', label: 'Описание' }, { path: '/price', label: 'Фото и цена' })
  return steps
}

export function announStepNav(d, currentPath) {
  const steps = announSteps(d)
  const index = steps.findIndex((s) => s.path === currentPath)
  return {
    steps,
    index,
    prev: index > 0 ? steps[index - 1].path : null,
    next: index >= 0 && index < steps.length - 1 ? steps[index + 1].path : null,
  }
}

// Подписи для значений, которые хранятся в базе с опечатками (их ждут фильтры поиска — менять нельзя)
export const REPAIR_OPTIONS = [
  { value: 'Без ремонта', label: 'Без ремонта' },
  { value: 'Обичный', label: 'Обычный' },
  { value: 'Косметический', label: 'Косметический' },
  { value: 'Евро', label: 'Евроремонт' },
  { value: 'Дизайнерский', label: 'Дизайнерский' },
]

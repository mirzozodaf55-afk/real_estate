// Оформление сайта, которое меняет админ (фон главной): загружаем один раз на всё приложение
let request = null

export function useSiteSettings() {
  const settings = useState('siteSettings', () => ({ hero_image: null, hero_overlay: 55, loaded: false }))

  function reload() {
    request = fetch(`${apiBase()}/api/site-settings`, { headers: { Accept: 'application/json' } })
      .then((r) => (r.ok ? r.json() : null))
      .then((d) => { if (d) settings.value = { ...d, loaded: true } })
      .catch(() => {})
    return request
  }

  if (process.client && !settings.value.loaded && !request) reload()
  return { settings, reload }
}

// Затемнение поверх фото: тот же синий оттенок, что и у фона без фото; слева плотнее — там текст
export function heroOverlayStyle(percent) {
  const o = Math.min(Math.max(Number(percent) || 0, 0), 85) / 100
  return `linear-gradient(100deg, rgba(8, 24, 64, ${o}) 0%, rgba(8, 30, 90, ${(o * 0.8).toFixed(3)}) 55%, rgba(4, 60, 180, ${(o * 0.55).toFixed(3)}) 100%)`
}

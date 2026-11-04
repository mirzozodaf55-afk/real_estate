// https://nuxt.com/docs/api/configuration/nuxt-config
// NUXT_SSR=false — статический сайт без сервера Node (публичное демо на Netlify, см. netlify.toml)
const ssr = process.env.NUXT_SSR !== 'false'

export default defineNuxtConfig({
  ssr,
  // app:{
  //   baseURL: '/',
  // },
  nitro: {
    preset: ssr ? 'node-server' : 'static'
  },
  runtimeConfig: {
    public: {
      // Адрес Laravel API. Пусто — тот же хост, с которого открыт сайт, порт 8000 (см. composables/apiBase.js),
      // так сайт работает и с localhost, и с телефона по 192.168.x.x. На проде задай NUXT_PUBLIC_API_BASE.
      // Значения из окружения берём и при сборке: у статического сайта нет сервера, который прочитал бы их потом
      apiBase: process.env.NUXT_PUBLIC_API_BASE || '',
      // Пусто — свой laravel-websockets (composables/usePusher.js); ключ — облачный Pusher
      pusherKey: process.env.NUXT_PUBLIC_PUSHER_KEY || '',
      pusherCluster: process.env.NUXT_PUBLIC_PUSHER_CLUSTER || 'mt1',
      // публичное демо: вход одной кнопкой, панель «Смотреть как…» (components/DemoBar.client.vue)
      demoMode: process.env.NUXT_PUBLIC_DEMO_MODE === 'true',
    }
  },
  devServer: {
    host: '0.0.0.0',  // Чтобы сервер был доступен для всех IP
    port: 3000
  },
  devtools: { enabled: false },
  css: [
    '@fortawesome/fontawesome-svg-core/styles.css',
    "bootstrap/dist/css/bootstrap.min.css",
    // '@/assets/css/style.css'
    // тёмная тема: перекрашивает палитру сайта, когда на <html> есть класс .dark (composables/useTheme.js)
    '~/assets/css/dark.css',
  ],

  // 
  modules: [
  'nuxt-icon',
  '@nuxtjs/tailwindcss',
],
build: {
  transpile: ['vue-remix-icons'],
  // chunkSizeWafrningLimit: 1000,
  // rollupOptions: {
  //   output: {
  //     manualChunks(id) {
  //       if (id.includes('node_modules')) {
  //         return 'vendor';
  //       }
  //     },
  //   }
  // },
},
plugins: ["~/plugins/pusher.client.js"],
  app: {
    baseURL:'/',
    head: {
      title: "Domly",
      meta: [
      ],
      script: [
        // тема ставится до отрисовки, иначе при тёмной теме страница на мгновение вспыхивает белым
        {
          innerHTML: "try{var t=localStorage.getItem('theme');if(t==='dark'||(!t&&matchMedia('(prefers-color-scheme: dark)').matches))document.documentElement.classList.add('dark')}catch(e){}",
          tagPosition: 'head',
        },
        {
          src: "https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js",
        }
         
        // ./public/bootstrap.bundle.min.js
        ],

      link: [{ rel: "icon", type: "image/svg+xml", href: "/logo.svg" }],
      style: [
      ],
      noscript: [
      ]
    },
  },
})

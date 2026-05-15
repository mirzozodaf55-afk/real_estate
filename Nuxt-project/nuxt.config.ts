// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  ssr:true,
  // app:{
  //   baseURL: '/',
  // },
  nitro: {
    preset: 'node-server'
  },
  // runtimeConfig: {
  //   public: {
  //     apiBase: 'http://127.0.0.1:8000', // ← твой Laravel backend
  //   }
  // },
  devServer: {
    host: '0.0.0.0',  // Чтобы сервер был доступен для всех IP
    port: 3000
  },
  devtools: { enabled: false },
  css: [
    '@fortawesome/fontawesome-svg-core/styles.css',
    "bootstrap/dist/css/bootstrap.min.css",
    // '@/assets/css/style.css'
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
      title: "Авторизация Регистрация",
      meta: [
      ],
      script: [
        {
          src: "https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js",
        } 
         
        // ./public/bootstrap.bundle.min.js
        ],

      link: [],
      style: [
      ],
      noscript: [
      ]
    },
  },
})

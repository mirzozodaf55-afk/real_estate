export default defineNuxtPlugin((nuxtApp) => {
  nuxtApp.provide("pusher", createPusher());
});

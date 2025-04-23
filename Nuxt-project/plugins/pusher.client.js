import Pusher from "pusher-js";

export default defineNuxtPlugin((nuxtApp) => {
  const pusher = new Pusher("local", {
    wsHost: "127.0.0.1:",
    wsPort: 6001,
    forceTLS: false,
    disableStats: true,
    enabledTransports: ["ws", "wss"],
    cluster: "mt1",
  });

  nuxtApp.provide("pusher", pusher);
});

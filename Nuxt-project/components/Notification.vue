<template>
<div class="w-full h-[100vh] bg-[pink]"></div>
</template>
<script setup>
import Pusher from 'pusher-js';
// Логируем Pusher в консоль
Pusher.logToConsole = false;
const {user} = getUserByServer()

// Инициализируем Pusher
const pusher = new Pusher('local', {
  wsHost:'127.0.0.1',
  wsPort:'6001',
  forceTLS:false,
  disableStats:true,
  cluster: 'mt1',
  enabledTransports:['ws','wss']
});
const channel = pusher.subscribe(`user.${user.value.id}`)
channel.bind('notify', (data)=>{
  console.log(data);
  
})
</script>
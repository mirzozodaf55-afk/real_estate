<template>
    <div class="p-6">
      <h1 class="text-3xl font-bold mb-4">Админпанель</h1>
  
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <AdminCard title="Пользователи" description="Управление пользователями" />
        <AdminCard title="Сообщения" description="Просмотр сообщений чатов" />
        <AdminCard title="Видео" description="Управление видео" />
        <!-- Добавь другие блоки по желанию -->
      </div>
    </div>
  </template>
  
  <script setup>
  import AdminCard from '~/components/AdminCard.vue'
  </script>
  
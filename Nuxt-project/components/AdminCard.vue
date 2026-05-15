<template>
    <div class="rounded-2xl shadow-md p-4 hover:shadow-lg transition bg-white dark:bg-gray-800">
      <h2 class="text-xl font-semibold mb-1">{{ title }}</h2>
      <p class="text-gray-500 dark:text-gray-300">{{ description }}</p>
    </div>
  </template>
  
  <script setup>
  defineProps({
    title: String,
    description: String,
    error: Object
  })
  </script>
  
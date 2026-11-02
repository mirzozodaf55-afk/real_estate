import axios from 'axios';

export default defineNuxtPlugin(() => {
  const instance = axios.create({
    baseURL: useRuntimeConfig().public.apiBase as string,
    timeout: 155000,
    headers: { 
        'Content-Type': 'application/json',
        //Authorization: 'Bearer ' + '3|PXWQktZLRG7twwAthX6V1Y8cG6suPbhXvgGBILfS9d7f99a9',
    }
  });

  const setHeader = (token: string) => {
    instance.defaults.headers.common['Authorization'] = `Bearer ${token}`;
  };

  return {
    provide: {
      axios: instance,
      setHeader
    }
  };
});

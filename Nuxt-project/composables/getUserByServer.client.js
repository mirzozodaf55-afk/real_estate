// export function getUserByServer(){
//     const user = ref(null)
//     const loading = ref(false)
//     const error = ref(null)
//     // let owner = JSON.parse(localStorage.getItem('owner'))[1]||{avatar:'avatar'}

//      fetch('http://127.0.0.1:8000/api/get_user',{
//         method:'GET',
//         headers:{
//             'Content-Type':'application/json',
//             Authorization:'Bearer ' + JSON.parse(localStorage.getItem('token'))
//         }
//     }).then(r=>r.json())
//     .then(res=>{
//         user.value = res;
//         loading.value = true    
//     })
//     .catch(err => {
//       error.value = err
//       loading = false
      
//     })

//     return {user,loading,error}

// }

import { ref } from 'vue'

const user = ref(null)
const loading = ref(false)
const loaded = ref(false) // чтобы знать, делали ли уже запрос

export function getUserByServer(token) {
  if (!loaded.value) {
    loading.value = true
    fetch('http://127.0.0.1:8000/api/get_user', {
      headers: {
        Authorization: 'Bearer ' + token
      }
    })
      .then(r => r.json())
      .then(res => {
        user.value = res
      })
      .catch(err => {
        console.error('Ошибка при получении user:', err)
      })
      .finally(() => {
        loading.value = false
        loaded.value = true
      })
  }

  return { user, loading }
}

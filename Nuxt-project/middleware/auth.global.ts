
export default defineNuxtRouteMiddleware(async (to, from) => {
    // const {$fetch} = useNuxtApp() 
    
    try {
        const res = await fetch('http://127.0.0.1:8000/api/check-auth',{
            headers: {
                Authorization: `Bearer ${localStorage.getItem('token')}`,
              },
            // credentials:'include',
        }).then(r=>r.json()).then(res=>res)
        console.log(res);
        
        if(!localStorage.getItem('token')){
            return navigateTo('/')
        }
    }
    catch (error){
        // navigateTo('/')
        console.log(error);
        
    }


    
})
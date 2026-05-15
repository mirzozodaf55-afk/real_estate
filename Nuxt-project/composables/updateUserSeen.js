export function updateUserSeen(){
    setInterval(()=>{
        getUserByServer()
    },5000)

    return ['data']
}

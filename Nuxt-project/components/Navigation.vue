<template>
    <div class="navigations">
            <ul class="list" @click="show">
                <li v-if="!user.id" class="service" @click="logOut()">
                    <div>
                        <font-awesome-icon style="font-size:1.15em;" :icon="['fas', 'right-to-bracket']" />
                    </div>
                    <a>{{ t('Войти') }}</a>
                </li>
                <li v-if="user.id" class="flex w-[200px] ml-[5px] items-center" @click="logOut">
                    <div class="user"  :style="`background-image: url('${apiBase()}/api/avatar/${user.avatar}');`">

                    </div>
                    <span class="ml-[10px] font-bold" >
                        <span style="font-family: Lato,Arial,sans-serif;">{{ user.name || user.last_name ? `${user.last_name || ''} ${user.name || ''}`.trim() : (user.phone_number ? `+992 ${user.phone_number}` : user.email) }}</span>
                    </span>
                </li>
                    <li class="bottom"></li>
                
                    <Services/>
                
            </ul>
        </div>
</template>
<script setup>
const {isShow,active,showMadoal} = useSwitch()
const {responce,direction} = getData()
const {user, loading} = getUserByServer()
const data = ref()
user.value = user.value || {name: '', last_name: '', phone_number: ''}
watch(user,(value)=>{
    user.value = user.value || {name: '', last_name: '', phone_number: ''}
    data.value = value

})

    const phone = ref()
    function logOut(){
        if (data.value && data.value.id) {
        active.value = false
        document.body.style.overflow ='auto';    
        navigateTo('/personal_area/profile')
        isShow.value = false
        showMadoal.value = false
    } else  {
        showMadoal.value = true
        direction.value = '/personal_area/profile'
        }
        
    }
    onMounted(()=>{
        user.value = user.value || {name: '', last_name: '', phone_number: ''}
        data.value = user.value

    })
</script>

<style scoped>

.navigations {
    width: 100%;    
    position: absolute;
    top: 55px;
}
.list{
    margin: 0;
    padding: 0;
    list-style-type: none;
    overflow-y: auto;
}
.list > .service > a{
    color: #152242;
    text-decoration: none;
    font-family: Lato,Arial,sans-serif;
    font-size: 17px;
    font-weight: 550;
    margin-left: 20px;
    letter-spacing: -.5px;
}
.service{
    /* height: 25px; */
    width: 100%;
    display: flex;
    align-items: center;
    cursor: pointer;
    color: #152242;
    padding: 14px 16px;
    font-weight: 500;
    font-size: 20px;
    line-height: 22px;
}
.bottom{
    padding: 14px 16px;
}
.bottom::before{
    display: block;
    position: absolute;
    left: 7px;
    content: '';
    width: 95%;
    height: 1.3px;
    background-color: #e8e9ec;
    }

    
    .user{
        max-width: 80px;
        /* min-width: 35px; */
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background-position: center;
        background-size: cover;
        background-repeat: no-repeat;
        outline: none;
        cursor: pointer;
        margin-left: 10px;
    }
</style>
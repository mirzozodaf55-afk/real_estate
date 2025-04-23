<template>
    <div class="layouts">
        <announ/>
    </div>
</template>

<style scoped>

.layouts{
        overflow: auto; 
        display: flex;
        flex-direction: column;
        position: relative;
        z-index: 92;
        /* height: 100vh; */
    }


</style>
<script setup>
onMounted(()=>{
    document.body.style.overflow = 'auto';
})
</script>

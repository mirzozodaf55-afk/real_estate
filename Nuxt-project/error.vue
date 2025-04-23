<template>
<NavBar />
    <div class="conatiner">
        <div class="content">

        <h1 class="text-danger" >404</h1>
        <p>this page is not exist</p>
        </div>
    </div>
</template>
<style scoped>
.conatiner{
    height: 100vh;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    display: flex;
    background-color: pink;
}
.content{
    width: max-content;
    padding: auto 20px;
}
.content h1{
    font-size: 5.2em;
}
.content >p{
    font-weight: 500;
}
</style>
<script setup>

</script>

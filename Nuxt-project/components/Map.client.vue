<template>
        <div class="layouts">
            <div class="container mt-4">
                <div class="progress-container">
                    <div class=" d-flex justify-content-between mb-2 mt-3 mx-auto ">
                        <span class="fw-bold pt-1 2">Расположение</span>
                        <button class="btn btn-none fs-5 p-0 m-0 text-primary me-2">
                            <font-awesome-icon :icon="['fas', 'circle-question']" />
                        </button>
                    </div>

                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width:10%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                <ClientOnly>
                    <Block/>
                </ClientOnly>
            </div>
        </div>
</template>
<script setup>

</script>

<style scoped>


@media screen and (min-width:640px){
    .layouts{
        display: flex;
        flex-direction: column;
        margin: 0;
        justify-content: center;
        font-family: Lato, sans-serif;
    }
    .container{
        height: max-content;
        display: flex;
        flex-direction: column;
    }
    .progress{
        height: 7px;
        background-color: rgba(138, 187, 218, 0.24);
    }
    .progress-container{
        padding: 0 20px;
    }
    .progress-bar{
        background-image: linear-gradient(to right, #ffe1ff ,#006cfd 99.9%,#006cfd );
        border-top-right-radius: 5px;
        border-bottom-right-radius: 5px;

    }
    
}

    .progress{
        height: 7px;
    }
    .progress-bar{
        background-image: linear-gradient(to right, #ffe1ff ,#006cfd 99.9%,#006cfd );
        border-top-right-radius: 5px;
        border-bottom-right-radius: 5px;

    }
    .progress-container{
        padding: 0 20px;
    }
@media screen and (min-width: 1024px) {
    .progress-container{
        width: 30%;
        padding: 15px;
    }
    .progress{
        height: 7px;
    }
        .container{
        display: flex;
        max-width: 1300px;
        flex-direction: row;
        justify-content: space-between;
    }
    .justify-content-between > span{
        font-size: 14px;
    }
    .justify-content-between > button{
        display: none;
    }
}


</style>
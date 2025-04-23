<template>
    <li class="bottom">
            <ul class="list-item">
                <li class="item"><a href="#">Аренда</a></li>
                <li class="item"><a href="#">Продажа</a></li>
                <li class="item"><a href="#">Новостройки</a></li>
                <li class="item"><a href="#">Посуточно</a></li>
                <li class="item"><a href="#">Коммерческая</a></li>
            </ul>
        </li>
</template>
<style scoped>
.list-item > .item > a{
    display: block;
    cursor: pointer;
    padding: 14px 0px;
    color: #152242;
    font-family: Lato,Arial,sans-serif;
    font-size: 16px;
    line-height: 18px;
    font-weight: 400;
    text-decoration: none;
}
.list-item{
    min-height:60vh;
    margin: 0;
    margin-top: 15px;
    padding: 0;
    list-style: none;
}
.bottom{
    margin-top: 18px;
    margin-bottom: 10px;
    padding: 14px 16px;
    /* min-height: auto; */
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
</style>
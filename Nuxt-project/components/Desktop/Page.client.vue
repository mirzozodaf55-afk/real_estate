<template>
    <main >

        <section class="w-full">
            <div class="pt-8  relative w-full" style="height:490px;">
        <div class="absolute top-0 left-0 bottom-0 z-2 w-full h-full bg-black/30">
        </div>
        <div class="w-full h-full bg-cover block absolute top-0 left-0 z-1 bg-no-repeat bg-center" style="background-image: url('/Nuxt-project/assets/images/photo.jpeg');"></div>    
        <div class="relative z-3 py-0 px-16 mt-20">
            <div class="relative my-0 mx-auto w-100 max-w-6xl">
                <div class="relative z-5">
                    <div class="my-0 mx-auto">
                        <div class="mb-10 text-white font-bold text-4xl" style="text-shadow:0 2px 4px rgba(0,0,0,.5);line-height: 1.21;"> 
                            Если недвижимость, то Циан
                        </div>
                        <ul class="list flex m-0 p-0 list-none">
                            <li :class="filter.dealType == type.value ? 'select' : '' " @click="isActive(type)" v-for="type of dealType" :key="type.name" class="cursor-pointer font-bold bg-black/40 items"  style="padding: 9px 16px 16px; margin-bottom:-8px; border-radius:4px 4px 0 0;" >{{ type.name }}</li>
                        </ul>

                        <div class="flex relative w-[100%] rounded-lg bg-white" style=" height:60px; box-shadow: 0 10px 20px 0 rgba(0,0,0,.1)">
                            <div  class="flex w-full">
                                <div class="form-select relative hover:bg-sky-100" style="height:100%; border-right:1px solid #f4f4f4; min-width:140px; max-width:316px;">
                                    <button @click="showCategory" class="text-btn truncate" style="border-top-left-radius:8px; line-height:1.22; border-bottom-left-radius: 8px;">
                                        {{ typeObject }}
                                    </button>

                                    <div class="drop-down radio-group ml-[-12px]" v-if="showCat">
                                        <div style="margin-top:16px;">
                                            <div class="flex flex-wrap" style="margin-top:-4px;margin-bottom:-4px;">

                                                <input type="radio" class="btn-check" name="options" id="option1" :checked="showR">
                                                <label @click="showRes" style="margin-top:-4px; margin-bottom:4px; border-top-left-radius:5px;border-bottom-left-radius:5px;" class="cursor-pointer px-2 py-1 border-1" for="option1">Жилая</label>

                                                <input type="radio" class="btn-check" name="options" id="option2"  :checked="showC" >
                                                <label @click="showCom" style="margin-top:-4px; margin-bottom:4px; border-top-right-radius:5px;border-bottom-right-radius:5px;" class="cursor-pointer px-2 py-1 border-1" for="option2">коммерческая</label>
                                            </div>

                                            <Commercial v-if="showC"/>
                                            <Resident v-if="showR"/>
                                        </div>
                                    </div>
                                </div>


                                <div class="form-select quantity-room relative hover:bg-sky-100"  style="height:100%; border-right:1px solid #f4f4f4; min-width:140px; max-width:316px;">
                                    <button @click="showQuantity" class="text-btn truncate">{{quantityRoom.join('-')+' комнать'}}</button>
                                    <DesktopQuantityRoom v-if="showQuan"/>
                                </div>

                                <div class="w-[156px]">
                                    <button @click="showPrice" class="form-select hover:bg-sky-100 h-[100%] border-0" style="color:#7A7A7A; font-size:14px; box-shadow:none;">Цена</button>
                                    <DesktopPrice v-if="showP"/>
                                </div>

                                <div style="flex:1 0 380px;border-left:1px solid silver" >
                                    <div class="relative ">
                                        <div class="relative">
                                            <input @input="getCity" v-model="city"  @click="search" class="addres text-[16px] overflow-hidden" type="text" maxlength="255" autocomplete="off" placeholder="Город, адрес, район, улица и др.">
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="flex w-[100%] justify-flex-end items-flex-end">
                            <span class="flex grow justify-end">
                                <span class="relative cursor-pointer" style="margin:12px 0 0;">
                                    <a class="bg-white
                                     flex
                                     justify-center
                                     transition
                                     
                                     border-0 rounded
                                     py-0 px-[16px] min-w-[112px]
                                     h-[40px]
                                     text-[#152242]
                                     font-bold
                                     items-center
                                     hover:text-[#0468ff]
                                     "
                                     
                                     style="line-height:40px; text-decoration:none;" href="#">Показать на карте</a>
                                </span>
                                <button @click="next" class="relative cursor-pointer" style="margin:12px 0 0; margin-left:8px;">
                                    <a class="
                                     flex
                                     justify-center
                                     
                                     transition
                                     border-0 rounded
                                     py-0 px-[16px] min-w-[112px]
                                     h-[40px]
                                     font-semibold
                                     items-center
                                     bg-[#0468ff]
                                     text-base
                                     hover:bg-[#055de1]
                                     text-white
                                     "
                                     
                                     style="font-family: Lato,Arial,sans-serif; line-height:40px; text-decoration:none;" href="#">Найти</a>
                                </button>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> 
        </section>

        <section>
            <div class="max-h-[100vh] mb-[20px] container-2xl" >
                <div class=" h-full ">

                <h2 class="
                overflow-hidden
                font-bold
                whitespace-nowrap
                text-elipsis
                text-[26px]
                leading-[33px]
                mx-2
                font-[550]

                " style="font-family: Lato,Arial,sans-serif;  margin:20px 0 16px;">Полезные ссылки</h2>

                <div class="grid frid-rows-4 ">
                    <div class="flex w-[100%] mt-2 h-[200px] ">
                        <div class="w-[50%] overflow-hidden rounded-[4px] flex h-full m-2 mx-2 shadow" >
                            <div class="w-[50%] h-full ">
                                <img class="w-full h-full " src="https://i.ytimg.com/vi/zumJJUL_ruM/maxresdefault.jpg" alt="">
                            </div>
                            <div class="w-[50%] h-full ">
                                <div class="relative box-border bg-center bg-cover w-full h-full" style="padding:17px 16px 12px 24px;">
                                    <h5 style="margin: 0 0 3px;" class="text-[black] font-bold text-[16px] leading-[18px]">
                                        Купить квартиру
                                    </h5>
                                    <ul class="m-0 p-0 list-none">
                                        <li style="margin-bottom:2px;">
                                            <a style="font-family:Lato,Arial,sans-serif; border-bottom:1px solid transparent;" class="no-underline min-h-[18px] font-[100] text-[14px] text-[#2b87db] leading-[18px] transition cursor-pointer " href="#">1 - комнатные</a>
                                            <span class="ml-[7px] text-[#7a7a7a] text-[13px]">0</span>

                                        </li>
                                        <li style="margin-bottom:2px;">
                                            <a style="font-family:Lato,Arial,sans-serif; border-bottom:1px solid transparent;" class="no-underline min-h-[18px] font-[100] text-[14px] text-[#2b87db] leading-[18px] transition cursor-pointer " href="#">2 - комнатные</a>
                                            <span class="ml-[7px] text-[#7a7a7a] text-[13px]">0</span>
                                        </li>
                                        <li style="margin-bottom:2px;">
                                            <a style="font-family:Lato,Arial,sans-serif; border-bottom:1px solid transparent;" class="no-underline min-h-[18px] font-[100] text-[14px] text-[#2b87db] leading-[18px] transition cursor-pointer " href="#">3 - комнатные</a>
                                            <span class="ml-[7px] text-[#7a7a7a] text-[13px]">0</span>
                                        </li>
                                        <li style="margin-bottom:2px;">
                                            <a style="font-family:Lato,Arial,sans-serif; border-bottom:1px solid transparent;"
                                            class="no-underline min-h-[18px] font-[100] text-[14px] text-[#2b87db] leading-[18px] transition cursor-pointer " href="#">
                                            Свободная планировка</a>
                                            <span class="ml-[7px] text-[#7a7a7a] text-[13px]">0</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="w-[50%] flex h-full overflow-hidden m-2 rounded-[4px] shadow" >
                            <div class="w-[50%] h-full ">
                                <img class="w-full h-full " src="https://foyr.com/learn/wp-content/uploads/2021/12/how-to-choose-the-exterior-colors-of-a-house.png" alt="">
                            </div>
                            <div class="w-[50%] h-full ">
                                <div class="relative box-border bg-center bg-cover w-full h-full" style="padding:17px 16px 12px 24px;">
                                    <h5 style="margin: 0 0 3px;" class="text-[black] font-bold text-[16px] leading-[18px]">
                                        Снять квартиру
                                    </h5>
                                    <ul class="m-0 p-0 list-none">
                                        <li style="margin-bottom:2px;">
                                            <a style="font-family:Lato,Arial,sans-serif; border-bottom:1px solid transparent;" class="no-underline min-h-[18px] font-[100] text-[14px] text-[#2b87db] leading-[18px] transition cursor-pointer " href="#">1 - комнатные</a>
                                            <span class="ml-[7px] text-[#7a7a7a] text-[13px]">0</span>
                                        </li>
                                        <li style="margin-bottom:2px;">
                                            <a style="font-family:Lato,Arial,sans-serif; border-bottom:1px solid transparent;" class="no-underline min-h-[18px] font-[100] text-[14px] text-[#2b87db] leading-[18px] transition cursor-pointer " href="#">2 - комнатные</a>
                                            <span class="ml-[7px] text-[#7a7a7a] text-[13px]">0</span>
                                        </li>
                                        <li style="margin-bottom:2px;">
                                            <a style="font-family:Lato,Arial,sans-serif; border-bottom:1px solid transparent;" class="no-underline min-h-[18px] font-[100] text-[14px] text-[#2b87db] leading-[18px] transition cursor-pointer " href="#">3 - комнатные</a>
                                            <span class="ml-[7px] text-[#7a7a7a] text-[13px]">0</span>

                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex w-[100%] mt-2 h-[200px] ">
                        <div class="w-[50%] flex h-full overflow-hidden m-2 rounded-[4px] shadow" >
                            <div class="w-[50%] h-full ">
                                <img class="w-full h-full " src="https://assets.thesparksite.com/uploads/sites/205/2021/02/pexels-photo-1396122.jpeg" alt="">
                            </div>
                            <div class="w-[50%] h-full ">
                                <div class="relative box-border bg-center bg-cover w-full h-full" style="padding:17px 16px 12px 24px;">
                                    <h5 style="margin: 0 0 3px;" class="text-[black] font-bold text-[16px] leading-[18px]">
                                        Снять посуточно
                                    </h5>
                                    <ul class="m-0 p-0 list-none">
                                        <li style="margin-bottom:2px;">
                                            <a style="font-family:Lato,Arial,sans-serif; border-bottom:1px solid transparent;" class="no-underline min-h-[18px] font-[100] text-[14px] text-[#2b87db] leading-[18px] transition cursor-pointer " href="#">Квартиры посуточно</a>
                                            <span class="ml-[7px] text-[#7a7a7a] text-[13px]">0</span>
                                        </li>
                                        <li style="margin-bottom:2px;">
                                            <a style="font-family:Lato,Arial,sans-serif; border-bottom:1px solid transparent;" class="no-underline min-h-[18px] font-[100] text-[14px] text-[#2b87db] leading-[18px] transition cursor-pointer " href="#">Коттеджи на сутки</a>
                                            <span class="ml-[7px] text-[#7a7a7a] text-[13px]">0</span>
                                        </li>
                                        <li style="margin-bottom:2px;">
                                            <a style="font-family:Lato,Arial,sans-serif; border-bottom:1px solid transparent;" class="no-underline min-h-[18px] font-[100] text-[14px] text-[#2b87db] leading-[18px] transition cursor-pointer " href="#">Комнаты на сутки </a>
                                            <span class="ml-[7px] text-[#7a7a7a] text-[13px]">0</span>

                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="w-[50%] flex h-full overflow-hidden m-2 rounded-[4px] shadow" >
                            <div class="w-[50%] h-full ">
                                <img class="w-full h-full " src="https://lh3.googleusercontent.com/ls8o3EBkV3cRF6MoXXXtiUxXVQErWvEiVDRdp2ghaN9CulBqPbpV604k2Eg46PFsWh7lGXPh7XiEsEmCs7HmoAl0PvLzxyUceXr9xbDNpwic3xOBREOnXyESBlvxmA9L17LfPJAL" alt="">
                            </div>
                            <div class="w-[50%] h-full ">
                                <div class="relative box-border bg-center bg-cover w-full h-full" style="padding:17px 16px 12px 24px;">
                                    <h5 style="margin: 0 0 3px;" class="text-[black] font-bold text-[16px] leading-[18px]">
                                        Коммерческая недвижимость
                                    </h5>
                                    <ul class="m-0 p-0 list-none">
                                        <li style="margin-bottom:2px;">
                                            <a style="font-family:Lato,Arial,sans-serif;
                                            border-bottom:1px solid transparent;"
                                            class="no-underline min-h-[18px] font-[100]
                                            text-[14px] text-[#2b87db] leading-[18px]
                                            transition cursor-pointer " href="#">
                                            Купить склад</a>
                                            <span class="ml-[7px] text-[#7a7a7a] text-[13px]">0</span>
                                        </li>
                                        <li style="margin-bottom:2px;">
                                            <a style="font-family:Lato,Arial,sans-serif; border-bottom:1px solid transparent;"
                                            class="no-underline min-h-[18px] font-[100] text-[14px] text-[#2b87db] leading-[18px] 
                                            transition cursor-pointer " href="#">Купить офис</a>
                                            <span class="ml-[7px] text-[#7a7a7a] text-[13px]">0</span>
                                        </li>
                                        <li style="margin-bottom:2px;">
                                            <a style="font-family:Lato,Arial,sans-serif; border-bottom:1px solid transparent;"
                                            class="no-underline min-h-[18px] font-[100] text-[14px] text-[#2b87db] leading-[18px]
                                            transition cursor-pointer " href="#">Арендовать офис</a>
                                            <span class="ml-[7px] text-[#7a7a7a] text-[13px]">0</span>
                                        </li>
                                        <li style="margin-bottom:2px;">
                                            <a style="font-family:Lato,Arial,sans-serif; border-bottom:1px solid transparent;"
                                            class="no-underline min-h-[18px] font-[100] text-[14px] text-[#2b87db] leading-[18px]
                                            transition cursor-pointer " href="#">Арендовать помещение</a>
                                            <span class="ml-[7px] text-[#7a7a7a] text-[13px]">0</span>
                                        </li>
                                        <li style="margin-bottom:2px;">
                                            <a style="font-family:Lato,Arial,sans-serif; border-bottom:1px solid transparent;"
                                            class="no-underline min-h-[18px] font-[100] text-[14px] text-[#2b87db] leading-[18px]
                                            transition cursor-pointer " href="#">Арендовать торговую площадь</a>
                                            <span class="ml-[7px] text-[#7a7a7a] text-[13px]">0</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </div>
        </section>
<section class="mt-[19px]">
    <div class="w-full mx-[auto]  mt-[10px] h-[max-content]">
                    <div class="h-full mt-[120px]"> 
                    <span style="font-family:Lato,Arial,sans-serif;" class="mx-[10px] block text-[#152242] font-bold text-[28px]">Могут подойти</span>
                    </div>
            
            <div class="mt-[24px] w-[1200px] mx-auto  mb-[10px]">
                <div class="flex justify-center">
                    <div class="mx-[10px]">
                                <div class="my-[10px] w-[320px]">  
                                    <a href="#" class="block overflow-hidden border rounded-[12px]  h-[400px] no-underline">
                                        <div class="h-[300px]">
                                            <div class="flex relative w-full h-full">
                                                <div class="w-full h-full m-0 p-0 relative items-scretch">
                                                    <img class="h-full w-full" src="https://images.adsttc.com/media/images/5014/01bd/28ba/0d3b/4500/0b8c/large_jpg/stringio.jpg?1414576505" alt="">
                                                </div>
                                            </div>
                                        </div>

                                        <div style="margin:8px 12px 12px;" class="flex flex-col">
                                            <span class="font-bold leading-[22px] text-[22px] text-[#152242] block ">1500 c</span>
                                            <span class="overflow-hidden text-[#152242] text-[16px] leading-[24px] text-ellipsis whitespace-nowrap">
                                                1-комн. кв. 23м 4/5 этаж
                                            </span>
                                            <span class="overflow-hidden mt-1 text-[rgba(21,34,66,.6)] text-[17px]">Душанбе</span>
                                        </div>
                                    </a>
                                </div>
                    </div>
                    <div class="mx-[10px]">
                        <div class="my-[10px] w-[320px]">
                                    <a href="#" class="block overflow-hidden border rounded-[12px]  h-[400px] no-underline">
                                        <div class="h-[300px]">
                                            <div class="flex relative w-full h-full">
                                                <div class="w-full h-full m-0 p-0 relative items-scretch">
                                                    <img class="h-full w-full" src="https://img.freepik.com/premium-vector/vector-living-room-interior-with-big-windows-sofa-armchair-with-old-style-pendulum-clock_730604-489.jpg?w=2000" alt="">
                                                </div>
                                            </div>
                                        </div>

                                        <div style="margin:8px 12px 12px;" class="flex flex-col">
                                            <span class="font-bold leading-[22px] text-[22px] text-[#152242] block ">1500 c</span>
                                            <span class="overflow-hidden text-[#152242] text-[16px] leading-[24px] text-ellipsis whitespace-nowrap">
                                                1-комн. кв. 23м 4/5 этаж
                                            </span>
                                            <span class="overflow-hidden mt-1 text-[rgba(21,34,66,.6)] text-[17px]">Хучанд</span>
                                        </div>
                                    </a>
                                </div>
                    </div>
                    <div class="mx-[10px]">
                        <div class="my-[10px] w-[320px]">
                                    <a href="#" class="block overflow-hidden border rounded-[12px]  h-[400px] no-underline">
                                        <div class="h-[300px]">
                                            <div class="flex relative w-full h-full">
                                                <div class="w-full h-full m-0 p-0 relative items-scretch">
                                                    <img class="h-full w-full" src="https://media.istockphoto.com/id/1142747548/photo/modern-house-interior-design.jpg?s=170667a&w=0&k=20&c=mBDJY0o0fojTcvfKBfp6qbKcRB0BmHbA4IRqSDF72uU=" alt="">
                                                </div>
                                            </div>
                                        </div>

                                        <div style="margin:8px 12px 12px;" class="flex flex-col">
                                            <span class="font-bold leading-[22px] text-[22px] text-[#152242] block ">1500 c</span>
                                            <span class="overflow-hidden text-[#152242] text-[16px] leading-[24px] text-ellipsis whitespace-nowrap">
                                                1-комн. кв. 23м 4/5 этаж
                                            </span>
                                            <span class="overflow-hidden mt-1 text-[rgba(21,34,66,.6)] text-[17px]">Варзоб</span>

                                        </div>
                                    </a>
                                </div>
                    </div>
                    <div class="mx-[10px]">
                        <div class="my-[10px] w-[320px]">
                                    <a href="#" class="block overflow-hidden border rounded-[12px] h-[400px] no-underline">
                                        <div class="h-[300px]">
                                            <div class="flex relative w-full h-full">
                                                <div class="w-full h-full m-0 p-0 relative items-scretch">
                                                    <img class="h-full w-full" src="https://images.unsplash.com/photo-1604014237800-1c9102c219da?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8OXx8aG91c2UlMjBpbnRlcmlvcnxlbnwwfHwwfHx8MA%3D%3D&w=1000&q=80" alt="">
                                                </div>
                                            </div>
                                        </div>

                                        <div style="margin:8px 12px 12px;" class="flex flex-col">
                                            <span class="font-bold leading-[22px] text-[22px] text-[#152242] block ">1500 c</span>
                                            <span class="overflow-hidden text-[#152242] text-[16px] leading-[24px] text-ellipsis whitespace-nowrap">
                                                1-комн. кв. 23м 4/5 этаж
                                            </span>
                                            <span class="overflow-hidden mt-1 text-[rgba(21,34,66,.6)] text-[17px]">Айни</span>
                                        </div>
                                    </a>
                                </div>
                    </div>
                </div>
                <div class="flex justify-center">
                    <div class="mx-[10px] ">
                        <div class=" my-[10px] w-[320px]">
                                    <a href="#" class="block overflow-hidden border rounded-[12px]  h-[400px] no-underline">
                                        <div class="h-[300px]">
                                            <div class="flex relative w-full h-full">
                                                <div class="w-full h-full m-0 p-0 relative items-scretch">
                                                    <img class="h-full w-full" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR1WFFDg4MyR3YxQPpL2MegAxGGCh6cE1NDoSqPRI_qp-HNBR-I0Az-8ax-YymnKvckHg4&usqp=CAU" alt="">
                                                </div>
                                            </div>
                                        </div>

                                        <div style="margin:8px 12px 12px;" class="flex flex-col">
                                            <span class="font-bold leading-[22px] text-[22px] text-[#152242] block ">1500 c</span>
                                            <span class="overflow-hidden text-[#152242] text-[16px] leading-[24px] text-ellipsis whitespace-nowrap">
                                                1-комн. кв. 23м 4/5 этаж
                                            </span>
                                            <span class="overflow-hidden mt-1 text-[rgba(21,34,66,.6)] text-[17px]">Бохтар</span>
                                        </div>
                                    </a>
                                </div>
                    </div>
                    <div class="mx-[10px] ">
                        <div class="my-[10px] w-[320px]">
                                    <a href="#" class="block overflow-hidden border rounded-[12px]  h-[400px] no-underline">
                                        <div class="h-[300px]">
                                            <div class="flex relative w-full h-full">
                                                <div class="w-full h-full m-0 p-0 relative items-scretch">
                                                    <img class="h-full w-full" src="https://media.gettyimages.com/id/157193888/photo/luxury-home-interior.jpg?s=612x612&w=gi&k=20&c=lkJ2V1HcTqcgVtFMayKPG1XDFwtUJsqt2ZstSShAU3E=" alt="">
                                                </div>
                                            </div>
                                        </div>

                                        <div style="margin:8px 12px 12px;" class="flex flex-col">
                                            <span class="font-bold leading-[22px] text-[22px] text-[#152242] block ">1500 c</span>
                                            <span class="overflow-hidden text-[#152242] text-[16px] leading-[24px] text-ellipsis whitespace-nowrap">
                                                1-комн. кв. 23м 4/5 этаж
                                            </span>
                                            <span class="overflow-hidden mt-1 text-[rgba(21,34,66,.6)] text-[17px]">Вахдат</span>
                                        </div>
                                    </a>
                                </div>
                    </div>
                    <div class="mx-[10px] ">
                        <div class="my-[10px] w-[320px]">
                                    <a href="#" class="block overflow-hidden border rounded-[12px] h-[400px] no-underline">
                                        <div class="h-[300px]">
                                            <div class="flex relative w-full h-full">
                                                <div class="w-full h-full m-0 p-0 relative items-scretch">
                                                    <img class="h-full w-full" src="https://www.mydomaine.com/thmb/Y8PWyIprwISsOVMXTYX05K3XJcw=/1080x0/filters:no_upscale():strip_icc()/TheBradburyProject-e8a49ddbbe43459e8dfe7359e0512082.jpg" alt="">
                                                </div>
                                            </div>
                                        </div>

                                        <div style="margin:8px 12px 12px;" class="flex flex-col">
                                            <span class="font-bold leading-[22px] text-[22px] text-[#152242] block ">1500 c</span>
                                            <span class="overflow-hidden text-[#152242] text-[16px] leading-[24px] text-ellipsis whitespace-nowrap">
                                                1-комн. кв. 23м 4/5 этаж
                                            </span>
                                            <span class="overflow-hidden mt-1 text-[rgba(21,34,66,.6)] text-[17px]">Гисар</span>
                                        </div>
                                    </a>
                                </div>
                    </div>
                    <div class="mx-[10px] ">
                            <div class="my-[10px] w-[320px]">
                                    <a href="#" class=" block overflow-hidden border rounded-[12px] h-[400px] no-underline">
                                        <div class="h-[300px]">
                                            <div class="flex relative w-full h-full">
                                                <div class="w-full h-full m-0 p-0 relative items-scretch">
                                                    <img class="h-full w-full" src="https://img.atlasobscura.com/tOzOP0kmp5WAau5Zb3JPPlFqRhahsP9k7LEMmDK_iJU/rt:fit/w:1280/q:81/sm:1/scp:1/ar:1/aHR0cHM6Ly9hdGxh/cy1kZXYuczMuYW1h/em9uYXdzLmNvbS91/cGxvYWRzL2Fzc2V0/cy81YmI4OWQ1NTJm/ZjNkMDM1ZjVfc2h1/dHRlcnN0b2NrXzE1/NjA3MTcxLmpwZw.jpg" alt="">
                                                </div>
                                            </div>
                                        </div>

                                        <div style="margin:8px 12px 12px;" class="flex flex-col">
                                            <span class="font-bold leading-[22px] text-[22px] text-[#152242] block ">1500 c</span>
                                            <span class="overflow-hidden text-[#152242] text-[16px] leading-[24px] text-ellipsis whitespace-nowrap">
                                                1-комн. кв. 23м 4/5 этаж
                                            </span>
                                            <span class="overflow-hidden mt-1 text-[rgba(21,34,66,.6)] text-[17px]">Вандж</span>
                                        </div>
                                    </a>
                            </div>
                    </div>
                </div>
            </div>
    </div>

 </section>
    </main>
    
</template>
<script setup>
    const {typeObject,quantityRoom, direction } = getData()
    const filter = JSON.parse(sessionStorage.getItem('filter')) || {}
    
    filter.dealType = 'Продажа'
    filter.typeObject = 'Квартира в новостройке'
    filter.quantityRoom = []
    sessionStorage.setItem('filter',JSON.stringify(filter))
    typeObject.value = JSON.parse(sessionStorage.getItem('filter')).typeObject || ''
const dealType = ref([
    {name:'Купить', value:"Продажа"},
    {name:'Снять', value:"Аренда"},
    {name:'Посуточно',value:'Посуточно'},
    {name:'Оценить',value:'Оценить'},
    {name:'Разместить объявление',value:'Разместить объявление'},
])


// setTimeout(()=>{

// },100)
const showP = ref(false)
const showCat = ref(false)
const showQuan = ref(false)
function isActive(type){
    const lists = document.querySelectorAll('.list-none > .items');
    lists.forEach((item)=>{
        if (item.textContent == type.name) {
            item.classList.add('select')
            showQuan.value = showCat.value = showP.value = false
        } else {
            item.classList.remove('select')
        }
        if (type.name == 'Разместить объявление') {
                const {active,showMadoal} = useSwitch()
                document.body.style.overflow ='hidden';
                direction.value = '/announ'
                showMadoal.value = true 
                localStorage.removeItem('announ')
        }
    })
    filter.dealType = type.value
    sessionStorage.setItem('filter',JSON.stringify(filter))
}
function showCategory() {
    showP.value = false

    showCat.value = !showCat.value;
    showQuan.value = false;
}
function showQuantity(){
    showCat.value = false;
    showQuan.value = !showQuan.value;
    showP.value = false
}


const showC = ref(false)
const showR = ref(true)
function showRes(){    
    showR.value = true;
    showC.value = false
}

function showCom(){
    showC.value = true
    showR.value = false
}




function showPrice(){
    showP.value = !showP.value;
    showCat.value = false;
    showQuan.value = false;
 
}

const city = ref('')

function search(){
    showCat.value = false;
    showQuan.value = false;
    showP.value = false
}

function getCity(){
    filter.city = city.value
    sessionStorage.setItem('filter', JSON.stringify(filter))
}

const {isShow} = useSwitch() 
function next(){
    window.location.replace('/list')
    isShow.value = false
}
onMounted(()=>{
    city.value = filter.city
})
</script>


<style scoped>
    .list > li{
        color: white;
    }
    .flex > li:hover{
        background-color: #fff;
        color: black;
    }
    .quantity-room{
        flex: 0 0 156px;
        max-width: 156px;
    }
    .text-btn{
    cursor: pointer;
    border: none;
    background: none;
    padding: 16px 38px 20px 24px;
    width: 100%;
    overflow: hidden;
    color: #121212;
    font-size: 14px;
    line-height: 1.15;
    text-align: left;
    /* text-overflow: ellipsis; */
    /* white-space: nowrap; */
    }
    .drop-down{
    position: relative;
    left: 0;
    z-index: 10;
    /* margin-top: 5px; */
    box-shadow: 0 10px 20px 0 rgba(0,0,0,.1);
    border-radius: 4px;
    background-color: #fff;
    padding: 16px;
    min-width: 300px;
    }
    .list-drop{
    margin: 0;
    display: flex;
    justify-content: space-around;
    margin: 0;
    padding: 0 ;
    }
    .list-drop > li >label{
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .list-drop > li > input:checked + label{
        background-color: #e9f3fb;
    }
    .drop-btn{
    cursor: pointer;
    border: 1px solid #f4f4f4;
    border-radius: 4px;
    background: none;
    width: 35px;
    height: 35px;
    color: #121212;
    font-size: 16px;
    text-align: center;
    }
    .radio-group input:checked + label{
        background-color: #f4f4f4;
    }
    .price-btn{
    cursor: pointer;
    border: none;
    background: none;
    padding: 20px 38px 20px 24px;
    width: 100%;
    overflow: hidden;
    color: #121212;
    font-size: 14px;
    line-height: 1.43;
    text-align: left;
    text-overflow: ellipsis;
    white-space: nowrap;
    }
    .addres{
    cursor: text;
    border: none;
    background: none;
    padding: 20px 24px;
    width: 100%;
    height: 100%;
    border: none;
    outline: none;
    overflow: hidden;
    color: #121212;
    line-height: 1.43;
    font-family: inherit;
    text-align: left;
    text-overflow: ellipsis;
    white-space: nowrap;
    }

    .select{
        background-color:#f1f1f1;
        color:#152242 !important;
        font-weight: 900;
    }
</style>


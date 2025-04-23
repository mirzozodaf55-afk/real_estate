<template>
  <div class="min-h-[100vh]  bg-[#f4f4f4] p-[10px]">
    <div class="container-xlg lg:container-lg">
      <div class="h-[50px] flex mt-[10px]">
        <h1 class="text-[27px] block md:mx-[0] lg:w-full lg:max-w-[1000px] lg:mx-[auto] mt-[8px] font-bold">
          Новое объявление
        </h1>
      </div>

      <div class="min-h-[100vh] mt-[30px] bg-[white] lg:max-w-[1000px] lg:mx-[auto]">
        <div class="relative z-1 mt-[10px] border-top-2 mb-[0]">
          <div
            class="text-[#121212] font-[400] text-[14px] leading-[1.3] py-[10px] px-[16px]"
          >
            <div class="relative my-[10px]">
              <div class="mb-[10px] sm:font-[551] sm:text-[16px] text-[13px] leading-[16px]">
                Адрес или название бизнес-центра
              </div>
            </div>

            <div class="text-[#121212] font-[400] text-[14px] leading-[1.3]">
              <form class="mt-3">
                <div class="dropdown mb-5" :class="!announData.city ? 'border-1 border-[red] text-[red] rounded-[6px]':'border-1 rounded-[6px] border-[green] text-[green]'">
                  <a
                    class="btn form-control border dropdown-toggle-none position-relative"
                    href="#"
                    role="button"
                    data-bs-toggle="dropdown"
                    style="box-shadow:none;"
                    :style="!announData.city ?'color:red':'color:green'"
                  >
                    <span class="mx-[auto]">{{announData.city ? announData.city :'Укажите город'}}</span>
                  </a>

                  <ul
                    style="height:300px; overflow: auto"
                    class="dropdown-menu form-control"
                  >
                    <li  :data-name="c.name" v-for="c of city" :key="c.id">
                      <a @click="chooseCity" class="dropdown-item" href="#">{{ c.name }}</a>
                    </li>
                  </ul>
                </div>
                <p
                v-if="!announData.city"
                  style="
                    margin-top: -45px;
                    font-family: Lato, Arial, sans-serif;
                    font-weight: 400;
                    font-size: 11px;
                    line-height: 15px;
                    color: red;
                  "
                >
                  Укажите адрес
                </p>
              </form>

              <div
                id="yandex"
                class="
                w-full flex
                items-center
                justify-center
                h-[350px]
                border-1  
                "
              >
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-[white] mt-[20px] lg:max-w-[1000px] lg:mx-[auto]">
        <div class="p-[10px]">
          <div
            class="mb-[20px] text-[14px] leading-[20px] text-[rgba(21,34,66,.6)] font-weight"
          >
            Обычное объявление
          </div>
        </div>

        <div class="p-[10px]">
          <div class="flex flex-col">
            <div
            v-if="announData.objects !== 'Коммерческая земля'"
              class="mb-[8px] shrink-[0] pt-[6px] w=[180px] text-[14px] leading-[16px]"
            >
              {{announData.objects == 'Здание'||announData.objects == 'Коммерческая земля'?'Площадь':'Общая площадь'}}
            </div>

            <div v-if="announData.objects !== 'Коммерческая земля'" :style="!announData.generalArea ? 'border:1px solid red;color:red;':'border:1px solid green;color:green;'" class="flex h-[30px] rounded-1 px-[4px] w-[90px]">
              <input
                v-model="announData.generalArea"
                @input="getTotalArea"
                class="border-0 outline-0 w-[100%] text-[14px] font-[450] text-[gray]"
                v-maska
                data-maska="####"
                type="text"
                :style="!announData.generalArea ? 'color:red;':'color:green;'"
              />
              <span class="my-[auto]">м<sup class="text-[10px]">2</sup></span>
            </div>
          </div>

          <div v-if="announData.objects !== 'Коммерческая земля'" class="flex flex-col mt-[20px]">
            <div
              class="mb-[8px] shrink-[0] pt-[6px] w=[180px] text-[14px] leading-[16px]"
            
            >
              {{announData.objects !== 'Здание'?'Этаж':'Этаж в здании'}}
            </div>
            <div class="flex ">
              <div :style="!announData.floor || announData.floor > announData.floorFrom  ? 'border:1px solid red;':'border:1px solid green'" class="flex h-[30px] rounded-1 px-[4px] w-[90px]">
                <input
                  v-model="announData.floor"
                  @input="getFloor"
                  class="border-0 my-[auto] h-[24px] outline-0 w-[100%] text-[14px] font-[450] text-[gray]"
                  v-maska
                  data-maska="###"
                  :style="!announData.floor || announData.floor > announData.floorFrom ? 'color: red;':'color:green;'"
                  type="text"
                />
              </div>
              <p v-if="announData.floor > announData.floorFrom" class="text-[red] ml-[10px] leading-[14px] text-[11px]">этаж должен быть меньше <br> или равен количество этажей</p>
            </div>
          </div>

          <div v-if="announData.objects !== 'Здание' && announData.objects !== 'Коммерческая земля' " class="flex flex-col">
            <div
              class="mb-[8px] shrink-[0] pt-[6px] w=[180px] text-[14px] leading-[16px]"
            >
              Из
            </div>

            <div :style="!announData.floorFrom || announData.floor > announData.floorFrom  ? 'border:1px solid red;':'border:1px solid green'" class="flex rounded-1 h-[30px]  px-[4px] w-[90px]">
              <input
              @input="getFloorFrom"
              v-model="announData.floorFrom"
                class="border-0 my-[auto] h-[24px] outline-0 w-[100%] text-[14px] font-[450] text-[gray]"
                v-maska
                :style="!announData.floorFrom || announData.floor > announData.floorFrom  ? 'color:red;':'color:green;'"
                data-maska="###"
                type="text"
              />
            </div>
          </div>

          <div v-if="announData.objects !== 'Коммерческая земля'" class="flex flex-col mt-[10px]">
            <div
              class="mb-[8px] shrink-[0] pt-[6px] w=[180px] text-[14px] leading-[16px]"
            >
              Высота потолков
            </div>
            <div
              class="flex rounded-1 h-[30px] items-center px-[4px] w-[90px]"
            :style="!announData.CeilingHeight ? 'border:1px solid red;color:red;':'border:1px solid green;color:green;'"
            >
              <input
                v-model="announData.CeilingHeight"
                class="border-0 outline-0 w-[100%] text-[14px] font-[450] text-[gray]"
                v-maska
                :style="!announData.CeilingHeight ? 'color: red;':'color:green;'"
                @input="getCeilingHeight"
                data-maska="####"
                type="text"
              />
              <span>м</span>
            </div>
          </div>

          <div  class="flex flex-col mt-[10px]">
            <div v-if="announData.objects !== 'Здание' && announData.objects !== 'Коммерческая земля' && announData.objects !== 'Склад'"
              class="mb-[8px] shrink-[0] pt-[6px] w=[180px] text-[14px] leading-[16px]"
            >
              Планировка
            </div>

            <div v-if="announData.objects !== 'Здание' &&announData.objects !== 'Коммерческая земля' && announData.objects !== 'Склад'" :style="!announData.layout || announData.layout == 'Не выбранно'? 'border:1px solid red;color:red;':'border:1px solid green;color:green;'" class="flex rounded-1 w-[max-content]">
              <select
              @change="selectLayout"
                style="-webkit-appearance: none"
                class="border-1 h-[30px] bg-[white] rounded-1 outline-0 px-[10px] py-[1px] text-[14px]"
                name="plan"
              >
                <option :selected="announData.layout =='Не выбранно'" style="color:black;" class="text-[14px]" >Не выбранно</option>
                <option :selected="announData.layout =='Кабинетная'" style="color:black;" class="text-[14px]">Кабинетная</option>
                <option :selected="announData.layout =='Открытая'" style="color:black;" class="text-[14px]">Открытая</option>
                <option :selected="announData.layout =='Коридорная'" style="color:black;" class="text-[14px]">Коридорная</option>
                <option :selected="announData.layout =='Смешанная'" style="color:black;" class="text-[14px]">Смешанная</option>
              </select>
            </div>

            <div
            v-if="announData.objects !== 'Коммерческая земля'"
              class="mb-[8px] shrink-[0] pt-[6px] mt-[15px] w-[180px] text-[14px] leading-[16px]"
            >
              {{announData.objects == 'Здание'?'Ремонт':'Состояние'}}
            </div>

            <div v-if="announData.objects !== 'Коммерческая земля'" :style="!announData.State || announData.State == 'Не выбранно'  ? 'border:1px solid red;color:red;':'border:1px solid green;color:green;'" class="flex rounded-1 w-[max-content]">
              <select
              @change="selectState"
                style="-webkit-appearance: none"
              
                class="h-[30px] bg-[white] rounded-1 outline-0 px-[10px] py-[1px] text-[14px]"
                name="plan"
              >
                <option :selected="announData.State=='Не выбранно'" class="text-[14px] text-[black]">Не выбранно</option>
                <option :selected="announData.State=='Офисная отделка'" v-if="announData.objects !== 'Здание'&&announData.objects !== 'Склад'" class="text-[14px] text-[black]">Офисная отделка</option>
                <option :selected="announData.State=='Под чистовую отделку'" v-if="announData.objects !== 'Здание'" class="text-[14px] text-[black]">Под чистовую отделку</option>
                <option :selected="announData.State=='Требуется капитальный ремонт'" v-if="announData.objects !== 'Здание'" class="text-[14px] text-[black]">
                  Требуется капитальный ремонт
                </option>
                <option :selected="announData.State=='Требуется косметический ремонт'" v-if="announData.objects !== 'Здание'" class="text-[14px] text-[black]">
                  Требуется косметический ремонт
                </option>
                <option class="text-[black]" :selected="announData.State=='Типовой'" v-if="announData.objects == 'Здание'||announData.objects == 'Склад'">Типовой</option>
                <option class="text-[black]" :selected="announData.State=='Дизайнерский'" v-if="announData.objects == 'Здание'">Дизайнерский</option>
                <option class="text-[black]" :selected="announData.State=='Под чистовую отделку'" v-if="announData.objects == 'Здание' || announData.objects !== 'Склад'">Под чистовую отделку</option>
                <option class="text-[black]" :selected="announData.State=='Нужен капистальный'" v-if="announData.objects == 'Здание' &&announData.objects == 'Склад'">Нужен капистальный</option>
                <option class="text-[black]" :selected="announData.State=='Нужен Косметический'" v-if="announData.objects == 'Здание' &&announData.objects == 'Склад'">Нужен Косметический</option>
              </select>
            </div>

            <div
            v-if="announData.objects !== 'Коммерческая земля'"
              class="mb-[8px] shrink-[0] pt-[6px] mt-[15px] w-[180px] text-[14px] leading-[16px]"
            >
              Мебель
            </div>

            <div v-if="announData.objects !== 'Коммерческая земля'" :style="!announData.Furniture || announData.Furniture == 'Не выбранно'  ? 'border:1px solid red;color:red;':'border:1px solid green;color:green;'" class="flex rounded-1 w-[max-content]">
              <select
              @change="selectFurniture"
                style="-webkit-appearance: none"
                class="border-1 bg-[white] h-[30px] rounded-1 outline-0 px-[10px] py-[1px] text-[14px]"
                name="plan"
              >
                <option :selected="announData.Furniture =='Не выбранно'" class="text-[14px] text-[black]">Не выбранно</option>
                <option :selected="announData.Furniture =='Есть'" class="text-[14px] text-[black]">Есть</option>
                <option :selected="announData.Furniture =='Нет'" class="text-[14px] text-[black]">Нет</option>
              </select>
            </div>


            <div
            v-if="announData.objects == 'Здание' && announData.objects !== 'Коммерческая земля'"
              class="mb-[8px] shrink-[0] pt-[6px] mt-[15px] w-[180px] text-[14px] leading-[16px]"
            >
              Лифт
            </div>

            <div v-if="announData.objects == 'Здание'" :style="!announData.elevator || announData.elevator == 'Не выбранно'  ? 'border:1px solid red;color:red;':'border:1px solid green;color:green;'" class="flex rounded-1 w-[max-content]">
              <select
              @change="selectElevator"
                style="-webkit-appearance: none"
                class="border-1 bg-[white] h-[30px] rounded-1 outline-0 px-[10px] py-[1px] text-[14px]"
                name="plan"
              >
                <option :selected="announData.elevator == 'Не выбранно'" class="text-[14px] text-[black]">Не выбранно</option>
                <option :selected="announData.elevator == 'Есть'" class="text-[14px] text-[black]">Есть</option>
                <option :selected="announData.elevator == 'Нет'" class="text-[14px] text-[black]">Нет</option>
              </select>
            </div>

            <div
            v-if="announData.objects == 'Здание'"
              class="mb-[8px] shrink-[0] pt-[6px] mt-[15px] w-[180px] text-[14px] leading-[16px]"
            >
              Отопление
            </div>

            <div v-if="announData.objects == 'Здание'" :style="!announData.heating || announData.heating == 'Не выбранно'  ? 'border:1px solid red;color:red;':'border:1px solid green;color:green;'" class="flex rounded-1 w-[max-content]">
              <select
              @change="selectHeating"
                style="-webkit-appearance: none"
                class="border-1 bg-[white] h-[30px] rounded-1 outline-0 px-[10px] py-[1px] text-[14px]"
                name="plan"
              >
                <option :selected="announData.heating == 'Не выбранно'" class="text-[14px] text-[black]">Не выбранно</option>
                <option :selected="announData.heating == 'Есть'" class="text-[14px] text-[black]">Есть</option>
                <option :selected="announData.heating == 'Нет'" class="text-[14px] text-[black]">Нет</option>
              </select>
            </div>


            <div
            v-if="announData.objects == 'Здание'"
              class="mb-[8px] shrink-[0] pt-[6px] mt-[15px] w-[180px] text-[14px] leading-[16px]"
            >
              Система пожаротушения
            </div>

            <div v-if="announData.objects == 'Здание'" :style="!announData.FireSystem || announData.FireSystem == 'Не выбранно'  ? 'border:1px solid red;color:red;':'border:1px solid green;color:green;'" class="flex rounded-1 w-[max-content]">
              <select
              @change="selectFireSystem"
                style="-webkit-appearance: none"
                class="border-1 bg-[white] h-[30px] rounded-1 outline-0 px-[10px] py-[1px] text-[14px]"
                name="plan"
              >
                <option :selected="announData.FireSystem == 'Не выбранно'" class="text-[14px] text-[black]">Не выбранно</option>
                <option :selected="announData.FireSystem == 'Есть'" class="text-[14px] text-[black]">Есть</option>
                <option :selected="announData.FireSystem == 'Нет'" class="text-[14px] text-[black]">Нет</option>
              </select>
            </div>

            <div
              v-if="announData.objects !== 'Коммерческая земля'"
              class="mb-[8px] shrink-[0] pt-[6px] mt-[15px] w-[180px] text-[14px] leading-[16px]"
            >
              Парковка
            </div>

            <div v-if="announData.objects !== 'Коммерческая земля'" :style="!announData.parking || announData.parking == 'Не выбранно'? 'border:1px solid red;color:red;':'border:1px solid green;color:green;'" class="flex rounded-1 w-[max-content]">
              <select
                @change="selectParking"
                style="-webkit-appearance: none"
                class="border-1 bg-[white] rounded-1 h-[30px] outline-0 px-[10px] py-[1px] text-[14px]"
                name="plan"
              >
                <option :selected="announData.parking =='Не выбранно'" class="text-[14px] text-[black]">Не выбранно</option>
                <option :selected="announData.parking =='Наземная'" class="text-[14px] text-[black]">Наземная</option>
                <option :selected="announData.parking =='Многоуровневая'" class="text-[14px] text-[black]">Многоуровневая</option>
                <option :selected="announData.parking =='Подземная'" class="text-[14px] text-[black]">Подземная</option>
                <option :selected="announData.parking =='На крыше'" class="text-[14px] text-[black]">На крыше</option>
              </select>
            </div>

            <div class="flex flex-col mt-[10px]" v-if="announData.objects !== 'Коммерческая земля'">
              <div
              v-if="announData.objects !== 'Здание'"
                class="mb-[8px] shrink-[0] pt-[6px] w-[180px] text-[14px] leading-[16px]"
              >
                Количество мест
              </div>
              
              <div
              v-if="announData.objects !== 'Здание'"
               :style="!announData.numberSeats ? 'border:1px solid red;color:red;' : 'border: 1px solid green;color:green'"
               class="flex rounded-1 h-[30px] px-[4px] w-[100px]">
                <input
                :style="!announData.numberSeats ? 'color:red;' : 'color:green;'"
                  @input="getQuantitiySeats"
                  v-model="announData.numberSeats"
                  class="border-0 outline-0 w-[100%] text-[14px] font-[450] text-[gray]"
                  v-maska
                  data-maska="####"
                  type="text"
                />
              </div>
            </div>
            <div></div>
          </div>
        </div>
        <!-- ///////////// -->
        <div class="mx-[2px] border-top bg-[white] min-h-[10vh] mt-[13px]">
          <div class="px-[10px]">
            <div class="mt-[24px] font-bold text-[16px] leading-[16px]">
              
              {{announData.objects !== 'Здание' && announData.objects !== 'Коммерческая земля'?'О здании':''}}
            </div>

            <div>
              <div v-if="announData.objects !== 'Здание' && announData.objects !== 'Коммерческая земля'" class="flex flex-col mb-[10px] mt-[10px]">
                <div
                  class="mb-[8px] shrink-[0] pt-[6px] w-[180px] text-[14px] leading-[16px]"
                >
                  Год постройки
                </div>

                <div
                :style="!announData.yearConstruction ? 'border:1px solid red;' : 'border: 1px solid green;'"
                 class="flex rounded-1 h-[30px] px-[4px] w-[100px]">
                  <input
                    @input="getYearConstruction"
                    v-model="announData.yearConstruction"
                    class="border-0 outline-0 w-[100%] text-[14px] font-[450] text-[gray]"
                    v-maska
                    :style="!announData.yearConstruction ? 'color: red;' : 'color: green;'"
                    data-maska="####"
                    type="text"
                  />
                </div>
              </div>
            </div>
            <div>
              <div v-if="announData.objects !== 'Здание' && announData.objects !== 'Коммерческая земля'" class="flex flex-col mt-[10px]">
                <div
                  class="mb-[8px] shrink-[0] pt-[6px] w=[180px] text-[14px] leading-[16px]"
                >
                  Площадь здания
                </div>

                <div
                  :style="!announData.buildingArea ? 'border:1px solid red;color:red' : 'border: 1px solid green;color:green'"
                  class="flex rounded-1 h-[30px] items-center px-[4px] w-[90px]"
                >
                  <input
                  @input="getBuildingArea"
                    class="border-0 outline-0 w-[100%] text-[14px] font-[450] text-[gray]"
                    v-model="announData.buildingArea"
                    v-maska
                    :style="!announData.buildingArea ? 'color:red;' : 'color:green'"
                    data-maska="####"
                    type="text"
                  />
                  <span>м</span>
                </div>
              </div>
              <div class="flex flex-col mb-[10px] mt-[10px]">
                <div
                  class="mb-[8px] shrink-[0] pt-[6px] w=[180px] text-[14px] leading-[16px]"
                >
                  {{announData.objects !== 'Коммерческая земля'?'Участок':'Площадь'}}
                </div>

                <div
                  :style="!announData.landArea ? 'border:1px solid red;color:red;' : 'border: 1px solid green;color:green;'"
                  class="flex rounded-1 h-[30px] mb-[18px] items-center px-[4px] w-[100px]"
                >
                  <input
                    @input="getLandArea"
                    class="border-0 outline-0 w-[100%] text-[14px] font-[450] text-[gray]"
                    v-model="announData.landArea"
                    v-maska
                    :style="!announData.landArea ? 'color:red;' : 'color:green;'"
                    data-maska="####"
                    type="text"
                  />
                  <span class="text-[14px]">сот</span>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div v-if="announData.objects !== 'Здание'&&announData.objects !== 'Коммерческая земля'&&announData.objects !== 'Склад'" class="px-[10px] border-y-[2px] min-h-[100vh]">
          <div>
            <div class="mt-[24px] font-bold text-[16px] leading-[16px]">
              Инфраструктура
            </div>
          </div>

          <div class="flex xsm:flex-wrap mt-[10px] max-w-[65%] h-full">
            <div class="flex justify-between h-[max-content] w-[100%]">
              <div style="flex: 1 1 flex-col">
                <label
                  class="flex mt-[14px]"
                  :for="data1.id"
                  v-for="data1 of firstData"
                  :key="data1.id"
                >
                  <input
                  @change="selectInfrastructure"
                    :data-name="data1.id"
                    type="checkbox"
                    class="flexable"
                    :id="data1.id"
                  />
                  <span
                    style="font-family: Lato, Arial, sans-serif"
                    class="text-[14px] ml-[10px] media"
                    >{{ data1.name }}</span
                  >
                </label>
              </div>
              <div class="ml-[15px] flex flex-col">
                <label
                  class="flex mt-[14px]"
                  :for="data2.id"
                  v-for="data2 of secondData"
                  :key="data2.id"
                >
                  <input
                  @change="selectInfrastructure"
                    :data-name="data2.id"
                    type="checkbox"
                    class="flexable"
                    :id="data2.id"
                  />
                  <span
                    style="font-family: Lato, Arial, sans-serif"
                    class="text-[14px] ml-[10px] media"
                    >{{ data2.name }}</span
                  >
                </label>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div
        class="p-[10px] mt-[5px] rounded-[10px] border-primary lg:max-w-[1000px] lg:mx-[auto]"
        
      >
        <div>
          <Pictures />
        </div>
      </div>
      <div class="p-[10px] mt-[5px] bg-white lg:max-w-[1000px] lg:mx-[auto]">
        <div>
          <div>
            <div class="mt-[24px] font-bold text-[16px] leading-[16px]">
              Видео
            </div>
          </div>

          <div 
          class="mt-[15px] border rounded-1 h-[30px] overflow-hidden" style="border:1px solid black;">
            <input
            @input="getLinkVideo"
              type="text"
              v-model="announData.linkVideo"
              class="w-full h-full outline-0 px-[10px]"
              placeholder="Ссылка на youtube"
            />
          </div>
        </div>

        <div>
          <div>
            <div class="mt-[24px] font-bold text-[16px] leading-[16px]">
              Заголовок объявления
            </div>
          </div>

          <div
            :style="!announData.title ? 'border:1px solid red;' : 'border: 1px solid green;'"
           class="mt-[15px] rounded-1 h-[30px] overflow-hidden">
            <input
              @input="writeTitle"
              v-model="announData.title"
              type="text"
              :style="!announData.title ? 'color:red;' : 'color: green;'"
              class="w-full h-full border-0 outline-0 px-[10px]"
            />
          </div>
        </div>

        <div>
          <div>
            <div class="mt-[24px] font-bold text-[16px] leading-[16px]">
              Описание
            </div>
          </div>

          <div 
          class="mt-[15px] rounded-1 h-[200px] overflow-hidden"
          :style="!announData.description || announData.description.length < 15 ? 'border:1px solid red;' : 'border: 1px solid green;'"
          >
          
            <textarea
              @input="writeDescription"
              v-model="announData.description"
              style="resize: none"
              type="text"
              :style="!announData.description || announData.description.length < 15 ? 'color: red;' : 'color:green;'"
              class="w-full h-full border-0 outline-0 px-[10px]"
            ></textarea>
          </div>
        </div>
      </div>

      <div class="p-[10px] mt-[5px] bg-white lg:max-w-[1000px] lg:mx-[auto]">
        <div>
          <div class="mt-[4px] font-bold text-[16px] leading-[16px]">Цена</div>
        </div>
        <div>
          <div>
            <div
              class="mb-[8px] shrink-[0] pt-[6px] w=[180px] text-[14px] leading-[16px]"
            >
              арендная плата
            </div>
          </div>

          <div
            :style="!announData.price || announData.price <100 ? 'border:1px solid red;color:red;' : 'border: 1px solid silver;color:green;'"
            class="flex rounded-1 h-[30px] items-center px-[4px] w-[120px]"
          >
            <input
              @input="getPrice"
              v-model="announData.price"
              class="border-0 outline-0 w-[100%] text-[14px] font-[450] text-[gray]"
              v-maska
              :style="!announData.price || announData.price <100 ? 'color:red;' : 'color:green;'"
              data-maska="####"
              type="text"
            />
            <span>с</span>
          </div>
        </div>

        <div class="mt-[10px] items-center flex flex-col md:flex-row-reverse justify-start">
          <div class="flex w-full">
            <next-btn :disabled="!images.length" @click="post" class="btn btn-primary w-full font-bold"
              >Разместить</next-btn
            >
          </div>
          <div class="md:flex mt-[5px] w-full md:mt-[0] mx-[5px]">
            <next-btn class="btn btn-light w-full text-primary"
              >Сохранить черновик</next-btn
            >
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup>
// ymaps.ready(init);
// function init(){
//    new ymaps.Map("yandex", {
//       center: [38.561433, 69.016602],
//       zoom: 13
//   });
       
// }
const CommercialAnnoun = ref({});
const data = fetch("http://127.0.0.1:8000/api/infrastructure");
const dataFetch = await data;
const d = await  dataFetch.json();

const firstData = ref(d.filter((item) => item.id < 14));
const secondData = ref(d.filter((item) => item.id > 14));
const infrastructure = ref([]);
const getInfo = fetch("http://127.0.0.1:8000/api/city");
const getCity = await getInfo;
const city = await getCity.json();
const {announData, images} = getData()
  announData.value = JSON.parse(localStorage.getItem('announ'))||{}

    function chooseCity(event){
      announData.value.city = event.target.textContent;
      localStorage.setItem('announ', JSON.stringify(announData.value))
    }


    function getTotalArea(){
      if (announData.value.generalArea) {
        announData.value.generalArea = parseInt(announData.value.generalArea) 
      }
      localStorage.setItem('announ', JSON.stringify(announData.value))
    }

    function getFloor(){
      if (announData.value.floor) {
        announData.value.floor = parseInt(announData.value.floor)
      }
      localStorage.setItem('announ', JSON.stringify(announData.value))
    
    }

    function getFloorFrom(){
      if (announData.value.floorFrom) {
        announData.value.floorFrom = parseInt(announData.value.floorFrom)
      }
      localStorage.setItem('announ', JSON.stringify(announData.value))
    }

    function getCeilingHeight(){
      if (announData.value.CeilingHeight) {
        announData.value.CeilingHeight = parseInt(announData.value.CeilingHeight);
      }
      localStorage.setItem('announ', JSON.stringify(announData.value))

    } 

    function getLinkVideo(){
      if (announData.value.linkVideo) {
        announData.value.linkVideo = announData.value.linkVideo
      }
      localStorage.setItem('announ', JSON.stringify(announData.value))

}
    function selectLayout(event){
      for (const item of event.target.children) {
        if(item.selected){
          announData.value.layout= item.value
        }
      localStorage.setItem('announ', JSON.stringify(announData.value))

      }
    }

    function selectState(event){
      for (const item of event.target.children) {
        if(item.selected){
          announData.value.State = item.value
          localStorage.setItem('announ',JSON.stringify(announData.value))
        }
      }
    }

    function selectFurniture(event){
      for (const item of event.target.children) {
        if(item.selected){
          announData.value.Furniture = item.value
          localStorage.setItem('announ',JSON.stringify(announData.value))
        }
      }
    }
    function selectElevator(event){
      for (const item of event.target.children) {
        if(item.selected){
          announData.value.elevator = item.value
          localStorage.setItem('announ',JSON.stringify(announData.value))
        }
      }
    }

    function selectHeating(event){
      for (const item of event.target.children) {
        if(item.selected){
          announData.value.heating = item.value
          localStorage.setItem('announ',JSON.stringify(announData.value))
        }
      }
    }
    function selectFireSystem(event){
      for (const item of event.target.children) {
        if(item.selected){
          announData.value.FireSystem = item.value
          localStorage.setItem('announ',JSON.stringify(announData.value))
        }
      }
    }
    function selectParking(event){
      for (const item of event.target.children) {
        if(item.selected){
          announData.value.parking = item.value
          
        }
      localStorage.setItem('announ', JSON.stringify(announData.value))
      }
    }

    function getQuantitiySeats(){
      if (announData.value.numberSeats) {
        announData.value.numberSeats = parseInt(announData.value.numberSeats)
      }
      localStorage.setItem('announ', JSON.stringify(announData.value))

    }

    function getYearConstruction(){
      if (announData.value.yearConstruction) {
        
        announData.value.yearConstruction = parseInt(announData.value.yearConstruction)
      }
      localStorage.setItem('announ', JSON.stringify(announData.value))
    }

    function getBuildingArea(){
      if (announData.value.buildingArea) {
        announData.value.buildingArea = parseInt(announData.value.buildingArea)
      }
      localStorage.setItem('announ', JSON.stringify(announData.value))
    }
    function getLandArea(){
      if (announData.value.landArea) {
        announData.value.landArea = parseInt(announData.value.landArea)
      }
    localStorage.setItem('announ', JSON.stringify(announData.value))
    }

    function writeTitle(){
      if (announData.value.title) {
        announData.value.title = announData.value.title 
      }
      localStorage.setItem('announ', JSON.stringify(announData.value))
    }

    function writeDescription(){
      if (announData.value.description) {
        announData.value.description = announData.value.description
      }
      localStorage.setItem('announ', JSON.stringify(announData.value))
    }

    function getPrice(){
      if (announData.value.price) {
        announData.value.price = parseInt(announData.value.price)
      }
      localStorage.setItem('announ', JSON.stringify(announData.value))
  }

    
const formData = new FormData()
async function post(event){
  event.target.disabled = true
  for (const item of images.value) {
    formData.append("images[]", item.file);
  }
  formData.append('announs',JSON.stringify(announData.value))
  formData.append('dataImage',JSON.stringify(images.value))

  await fetch('http://127.0.0.1:8000/api/create/announ',{
    method:'post',
    headers:{
      Authorization:'Bearer '+JSON.parse(localStorage.getItem('token'))
    },
    body:formData
  })
  .then(responce=>{
    if (responce.ok) {
      navigateTo('/personal_area/my_announ')
    }
    return responce.json()
    })
  .then(res=>{
    console.log(res);
  })
  event.target.disabled = false
}

  onMounted(()=>{
     setTimeout(()=>{
   const elems = document.querySelectorAll('.flexable');
        
    if (announData.value.infrastructure && announData.value.infrastructure.length) {
      infrastructure.value = announData.value.infrastructure
      elems.forEach(elem => {
            announData.value.infrastructure.forEach(val =>{
                if (parseInt(elem.dataset.name) == val) {
                    elem.checked = true
                }
            })
        })
    }
  })
  })

  function selectInfrastructure(event){
    announData.value.infrastructure = []
    if (event.target.checked) {
        if (Array.isArray(infrastructure.value)) {
            infrastructure.value.push(parseInt(event.target.dataset.name))
        }
    }

    infrastructure.value.forEach(item => {
        if (!event.target.checked && parseInt(event.target.dataset.name) === parseInt(item)) {
            infrastructure.value.splice(infrastructure.value.indexOf(item),1)
        }
    announData.value.infrastructure = infrastructure.value
    localStorage.setItem('announ',JSON.stringify(announData.value))       
    });
    const elems = document.querySelectorAll('.flexable');
    elems.forEach(elem => {
        infrastructure.value.forEach(val =>{
            if (parseInt(elem.dataset.name) == val) {
                elem.checked = true
            }
        })
    }) 

  }
</script>
<style scoped>
* {
  font-family: Lato, Arial, sans-serif;
}

@media screen and (min-width: 640px) {
  .media {
    overflow: hidden;
    width: 200px;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
}
@media screen and (min-width: 768px) {
  .media {
    min-width: 200px;
    text-overflow: none;
    white-space: wrap;
    overflow: auto;
  }
}

@media screen and (min-width: 320px) {
  .media {
    overflow: hidden;
    width: 100px;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
}
</style>
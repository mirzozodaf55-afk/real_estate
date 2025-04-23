<template>
  <div class="mt-[26px]">
    <div class="px-[8px]">
      <div class="dropup  border" style="width: 100%">
        <button
          class="btn w-100 select focus:border-0"
          type="button"
          id="dropdownMenuButton1"
          data-bs-toggle="dropdown"
          aria-expanded="false"
        >
        <div class="flex">
          <span>
            {{choose}}
          </span>
          <span v-if="!choose" class="block  w-full text-center">Выберите тип дома</span>
        </div>
        </button>
        <ul
          class="dropdown-menu mt-[10px] w-100 rounded-0"
          aria-labelledby="dropdownMenuButton1"
        >
          <li
            v-for="(item,index) of items"
            :key="index"
            class="dropdown-item"
            style="display: flex; justify-content: space-between;"
            >
            <label :for="`s${index + 1}`" style="width: 100%">{{item.label}}</label>
            <input
              v-model="item.checked"
              type='radio'
              :checked="item.checked"
              name="check"
              :data-name="item.label"
              :id="`s${index + 1}`"
              @change="check"
            />
          </li>
          
          
        </ul>
      </div>
    </div>
  </div>
</template>
<script setup>
const saveText = ref('');
const items = ref([
      { name: 'check1', label: 'Квартира', checked: false },
      { name: 'check2', label: 'Комната', checked: false },
      { name: 'check3', label: 'Дом/дача', checked: false },
      { name: 'check4', label: 'Коттедж', checked: false },
      { name:'check5',label:'Участок',checked:false}
])


const apartament = ref(items.value.filter(item=>item.label == 'Квартира' || item.label == 'Комната'))
const house = ref(items.value.filter(item=>item.label == 'Дом/дача'|| item.label =='Коттедж'))
const land = ref(items.value.filter(item=>item.label == 'Участок'))
const text = ref([]);
const typeObject = JSON.parse(sessionStorage.getItem('filter')).typeObject
    const selectedItems = items.value
        .filter((item) => item.checked)
        .map((item) => item.label);
      text.value = selectedItems.slice();

      props.filter.typeObject = text.value;
      // sessionStorage.setItem('filter', JSON.stringify(props.filter));
      if (props.filter.typeObject.length && props.filter.typeObject.indexOf('Квартира') !== -1) {
        props.uploadQuantityRoom(true)
      }else{
        props.uploadQuantityRoom(false)
      }
const props = defineProps({
  filter:Object,
  updateData:Function,
  loader:Boolean,
  updateLoader:Function,
  uploadQuantityRoom:Function,
  updateFilters:Object
});

  const filters = ref()

    let fil = props.updateFilters
    if (fil.dealType) {
      if (fil.dealType !== 'Продажа') {
        items.value.pop()
      }
    }
    
      const choose = ref('')
      items.value.forEach(item => {
          if (item.label == typeObject) {
            item.checked = true
            choose.value = item.label
          }
            if (typeObject == 'Квартира в Новостройке') {
              choose.value = 'Квартира'
              item.label == 'Квартира'? item.checked = true:item.checked=false
            }
      })
    async function check(event) {
    if (event.target.name == "check") {
      choose.value = event.target.dataset.name
      props.filter.typeObject = choose.value;
      if (props.filter.dealType == 'Продажа'&&choose.value == 'Квартира') {
      props.filter.buildingType = ['Квартира','Квартира в Новостройке']
      }else{
        props.filter.buildingType = []
      }
      sessionStorage.setItem('filter', JSON.stringify(props.filter));
      if (props.filter.typeObject.length && props.filter.typeObject.indexOf('Квартира') !== -1) {
        props.uploadQuantityRoom(true)
      }else{
        props.uploadQuantityRoom(false)
      }
    }
    props.updateLoader(true)
  await fetch("http://127.0.0.1:8000/api/filter", {
    method: "post",
    headers: {
      "Content-type": "application/json",
    },
    body: JSON.stringify(props.filter),
  })
    .then((res) => res.json())
    .then((res) => {
        props.updateData(res)
        console.log(res);
    });
  props.updateLoader(false)
  }

</script>
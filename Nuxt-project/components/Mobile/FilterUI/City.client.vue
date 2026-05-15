<template>
  <div class="dropdown h-[60px] mx-[10px] py-[10px]">
    <button
      class="flex w-100 outline-0 items-center h-full focus:border-0"
      type="button"
      id="dropdownMenuButton1"
      data-bs-toggle="dropdown"
      aria-expanded="false"
    >
      <div
        class="rounded-[4px] flex items-center justify-center text-white bg-[#0468ff] p-[4px] w-[24px] h-[24px]"
      >
        <font-awesome-icon :icon="['fas', 'location-dot']" />
      </div>
      <div class="mx-[10px]">
        {{ filter.city ? `${filter.city}` : "Все" }}
      </div>
    </button>

    <ul class="dropdown-menu rounded-0 w-100" aria-labelledby="drop23">
      <li
        @click="selectCity"
        class="flex h-[40px] items-center p-[10px] mx-[3px] my-[3px] justify-between"
      >
        <div>Все</div>
      </li>
      <li
        @click="selectCity"
        class="flex h-[40px] items-center p-[10px] mx-[3px] my-[3px] justify-between"
        v-for="city of cities"
        :key="city.id"
      >
        <div>{{ city.name }}</div>
      </li>
    </ul>
  </div>
</template>

<script setup>
const selectedCity = ref("");
const route = useRoute()

const props = defineProps({
  cities: Array,
  filter:Object,
  updateData:Function,
  loader:Boolean,
  updateLoader:Function,
  error: Object

});



async function selectCity(event) {
  route.query.city = props.filter.city = event.target.textContent;
  selectedCity.value = props.filter.city;
  console.log();
  if (event.target.textContent == "Все") {
    route.query.city = props.filter.city = "";
    sessionStorage.setItem("filter", JSON.stringify(props.filter));
  }
    sessionStorage.setItem("filter", JSON.stringify(props.filter));
    props.updateLoader(true);
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
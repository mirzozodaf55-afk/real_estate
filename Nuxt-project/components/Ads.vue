<template>
    <div class="min-h-[100vh]">
        <!-- <div class="rounded-2xl shadow-md p-4 hover:shadow-lg transition bg-white dark:bg-gray-800">
            <h2 class="text-xl font-semibold mb-1">Ads</h2>
            <p class="text-gray-500 dark:text-gray-300"></p>
            <label for="">тип сделки</label>
            <select class="form-control" name="" id="" style="width: min-content;">
                <option v-for="r of rent" class="form-control" value="Аренда">{{ r.name }}</option>
            </select>
            <label for="">тип Аренды</label>

            <select class="form-control" name="" id="" style="width: min-content;">
                <option v-for="type of typeRent" value="длительно">{{ type.name }}</option>
            </select>
            <label for="">тип недвижимости</label>

            <select class="form-control" name="" id="" style="width: min-content;">
                <option v-for="est of estate" value="">{{ est.name }}</option>
            </select>
            <label for="">Oбъект</label>

            <select class="form-control" name="" id="" style="width: min-content;">
                <option v-for="obj of object" value="">{{ obj.name }}</option>
            </select>
            <label for="">Oбъект</label>
            <select class="form-control" name="" id="" style="width: min-content;">
                <option v-for="object of officeObject" value="">{{ object.name }}</option>
            </select>
        </div> -->
        <Announ/>
    </div>
</template>
<script setup>
const {rent,officeObject,estate,object,typeRent} = data()
</script>
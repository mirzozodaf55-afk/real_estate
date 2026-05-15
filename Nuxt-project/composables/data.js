export default function data(){
    const typeObjectLiving = [
        {name:'Квартира'},
        {name:'Комната'},
        {name:'Квартира в Новостройке'},
        {name:'Дом/Дача'},
        {name:'Коттедж'},
        {name:'Участок'},
    ]
    const typeObjectCommercial = [
        {name:'Офис'},
        {name:'Здание'},
        {name:'Склад'},
        {name:'Торговая площадь'},
        {name:'Коммерческая земля'},

    ]
    
    const typeButton = reactive[
        {name:'Купить'},
        {name:'Снять'},
        {name:'Почуточно'}
    ]
    const rent = [
        {name:'Аренда'},
        {name:'Продажа'}
    ]
    const typeRent = [
        {name:'Длительно'},
        {name:'Несколко месяцев'},
        {name:'Посуточно'}
    ]
    const estate = [
        {name:'Жилая'},
        {name:'Коммерческая'}
    ]
    const object = [
        {name:'Квартира'},
        {name:'Комната'},
        {name:'Дом/Дача'},
        {name:'Коттедж'},
        {name:'Участок'},
    ]
    const officeObject = [
        {name:'Офис'},
        {name:'Здание'},
        {name:'Торговая площадь'},
        {name:'Коммерческая земля'},
        {name:'Склад'},
    ]

    return {typeRent,officeObject,object,estate,rent,typeButton,typeObjectLiving,typeObjectCommercial}
}
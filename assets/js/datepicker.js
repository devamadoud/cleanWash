import Datepicker from 'flowbite-datepicker/Datepicker';

const dateElementFrom = document.getElementById('dateFrom');
const dateElementTo = document.getElementById('dateTo');

if(dateElementFrom){
    new Datepicker(dateElementFrom);
}

if(dateElementTo){
    new Datepicker(dateElementTo);
}

require('./bootstrap');

window.Vue = require('vue');

require('moment');
require('bootstrap-daterangepicker');
import Vuelidate from 'vuelidate';
Vue.use(Vuelidate);

Vue.component('intervals-component', require('./components/Intervals/IntervalsBlock.vue'));

export const eventBus = new Vue();

const app = new Vue({
    el: '#app'
});

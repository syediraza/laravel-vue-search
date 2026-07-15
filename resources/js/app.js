import './bootstrap';
import Vue from 'vue';
import Dashboard from './components/Dashboard.vue';

new Vue({
    render: h => h(Dashboard)
}).$mount('#app');

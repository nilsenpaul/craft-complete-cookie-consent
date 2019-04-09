import Vue from 'vue';
import axios from 'axios';
import Cookiebanner from './Cookiebanner.vue';

window.ccc = null;

Vue.use({
    install (Vue) {
        Vue.prototype.$api = axios.create({
            baseUrl: ''
        });
    }
});

new Vue({
    el: '#ccc',
    components: { Cookiebanner },
});

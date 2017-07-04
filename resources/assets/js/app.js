window.$ = window.jQuery = require('jquery');
// responsive = require('../../../node_modules/responsive-bp/build/responsive.min.js');

import Vue from 'vue'

import App from './App.vue'
import router from './router'

// require('./nav.js');

const app = new Vue({
	el: '#root',
	template: `<app></app>`,
	components: { App },
	router
});

Vue.config.debug = true;
Vue.config.devtools = true;

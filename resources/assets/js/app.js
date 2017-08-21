import Vue from 'vue'

import App from './App.vue'
import router from './router'
import Auth from './store/auth'

const app = new Vue({
	el: '#root',
	template: `<app></app>`,
	components: { App },
	router,
	data() {
		return {
            authState: Auth.state,
            ready: true,
		}
	},
    computed: {
        auth() {
            if(this.authState.api_token) {
                return true
            }
            return false
        },
        guest() {
            return !this.auth
        }
    },
});

Vue.config.debug = true;
Vue.config.devtools = true;

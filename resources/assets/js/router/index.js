import Vue from 'vue'
import VueRouter from 'vue-router'
import Login from '../views/Auth/Login.vue'
import Register from '../views/Auth/Register.vue'
import Home from '../views/Home.vue'
import RecipeIndex from '../views/Recipe/Index.vue'
import RecipeShow from '../views/Recipe/Show.vue'
import RecipeForm from '../views/Recipe/Form.vue'
import IngredientIndex from '../views/Ingredient/Index.vue'
import IngredientEditForm from '../views/Ingredient/Form.vue'
import IngredientCreateForm from '../views/Ingredient/Form.vue'
import NotFound from '../views/NotFound.vue'
import { EventBus } from '../event-bus';

Vue.use(VueRouter)

const router = new VueRouter({
	mode: 'history',
	routes: [
		{ path: '/', component: Home },

        { path: '/recipes', component: RecipeIndex},
		{ path: '/recipes/create', component: RecipeForm, meta: { mode: 'create', auth: true }},
		{ path: '/recipes/:id/edit', component: RecipeForm, meta: { mode: 'edit', auth: true }},
		{ path: '/recipes/:id', component: RecipeShow },

        { path: '/ingredients', component: IngredientIndex},
        { path: '/ingredients/create', component: IngredientCreateForm, meta: { mode: 'create', auth: true }},
        { path: '/ingredients/:id/edit', component: IngredientEditForm, meta: { mode: 'edit', auth: true }},

		{ path: '/register', component: Register },
		{ path: '/login', component: Login },
		{ path: '/not-found', component: NotFound },
		{ path: '*', component: NotFound }
	]
});

router.beforeEach((to, from, next) => {

    // Wait till next tick so root variables are ready
    Vue.nextTick(function () {

        if(typeof to.meta.auth !== 'undefined' && to.meta.auth && !router.app.$root.auth) {
            // show login form
            router.app.$root.destinaton = to.path;
            EventBus.$emit('showLoginModal', true);
        } else {
            next();
        }

    });

});

export default router

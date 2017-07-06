<template>
	<div class="wrapper">

		<header class="header">
			<div class="container">
				<router-link to="/" class="header__link">Smoothie Recipes</router-link>
				<nav role="navigation" data-navigation="" class="navigation" id="main-nav">
					<button aria-controls="main-nav" aria-expanded="false" class="navigation__button">
						Menu <i class="fa fa-bars navigation__bars" aria-hidden="true"></i>
					</button>
					<ul class="navigation__container">
						<li class="navigation__item" v-if="guest">
							<router-link to="/login" class="navigation__link">Login</router-link>
						</li>
						<li class="navigation__item" v-if="guest">
							<router-link to="/register" class="navigation__link">Register</router-link>
						</li>
						<li class="navigation__item" v-if="auth">
							<router-link to="/recipes/create" class="navigation__link">Create recipe</router-link>
						</li>
						<li class="navigation__item">
							<router-link to="/ingredients" class="navigation__link">Ingredients</router-link>
						</li>
						<li class="navigation__item" v-if="auth">
							<router-link to="/ingredients/create" class="navigation__link">Create ingredient</router-link>
						</li>
						<li class="navigation__item" v-if="auth">
							<a @click.stop="logout" class="navigation__link">Logout</a>
						</li>
					</ul>
				</nav>
			</div>
		</header>

		<div class="container">
			<div class="flash flash--error" v-if="flash.error">
				{{flash.error}}
			</div>
			<div class="flash flash--success" v-if="flash.success">
				{{flash.success}}
			</div>
			<router-view></router-view>
		</div>

	</div>
</template>
<script type="text/javascript">
	import Auth from './store/auth'
	import Flash from './helpers/flash'
	import { post, interceptors } from './helpers/api'
	export default {
		created() {

			// global error http handler
			interceptors((err) => {
				if(err.response.status === 401) {
					Auth.remove()
					this.$router.push('/login')
				}

				if(err.response.status === 500) {
					Flash.setError(err.response.statusText)
				}

				if(err.response.status === 404) {
					this.$router.push('/not-found')
				}
			})
			Auth.initialize()
		},
		data() {
			return {
				authState: Auth.state,
				flash: Flash.state
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
		methods: {
			logout() {
				post('/api/logout')
				    .then((res) => {
				        if(res.data.done) {
				            // remove token
				            Auth.remove()
				            Flash.setSuccess('You have successfully logged out.')
				            this.$router.push('/login')
				        }
				    })
			}
		}
	}
</script>

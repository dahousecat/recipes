<template>
	<div class="wrapper" :class="blurWrapper ? 'wrapper--blur' : ''">

		<div class="scrim" :class="contentLoading ? 'scrim--loading' : ''"></div>

		<header class="header">
			<div class="container header__container">
				<h1 class="header__title">
					<router-link to="/"
								 @click.native="navClick" class="header__link">Smoothie Recipes</router-link>
				</h1>
				<nav role="navigation" data-navigation="" class="navigation" id="main-nav"
					 :class="[menuExpanded ? 'navigation--active' : '', menuAnimating ? 'navigation--animating' : '']"
					 :aria-expanded="menuExpanded ? 'true' : 'false'">
					<button aria-controls="main-nav"
							class="navigation__button"
							@click="toggleMenu">
						<div class="navigation__text">Menu</div>
						<div class="navigation__icon">
							<span class="navigation__bar"></span>
							<span class="navigation__bar"></span>
							<span class="navigation__bar"></span>
							<span class="navigation__bar"></span>
						</div>
					</button>
					<ul class="navigation__list">
						<li class="navigation__item" v-if="guest">
							<router-link to="/login"
										 @click.native="navClick" class="navigation__link">Login</router-link>
						</li>
						<li class="navigation__item" v-if="guest">
							<router-link to="/register"
										 @click.native="navClick" class="navigation__link">Register</router-link>
						</li>
						<li class="navigation__item" v-if="auth">
							<router-link to="/recipes/create"
										 @click.native="navClick" class="navigation__link">Create recipe</router-link>
						</li>
						<li class="navigation__item">
							<router-link to="/ingredients"
										 @click.native="navClick" class="navigation__link">Ingredients</router-link>
						</li>
						<li class="navigation__item" v-if="auth">
							<router-link to="/ingredients/create"
										 @click.native="navClick" class="navigation__link">Create ingredient</router-link>
						</li>
						<li class="navigation__item" v-if="auth">
							<a @click.stop="logout" class="navigation__link">Logout</a>
						</li>
					</ul>
				</nav>
			</div>
		</header>

		<div class="container">

			<div class="mobile-nav-scrim" :class="menuExpanded ? 'mobile-nav-scrim--visible' : ''"></div>

			<div class="flash flash--error" v-if="flash.error">
				{{flash.error}}
			</div>
			<div class="flash flash--success" v-if="flash.success">
				{{flash.success}}
			</div>
			<router-view @finishedLoading="finishedLoading"></router-view>
		</div>

		<modal :show="showLoginModal" @close="showLoginModal = false">
			This is the login modal right here
			<login-form></login-form>
		</modal>

	</div>
</template>
<script type="text/javascript">
	import Auth from './store/auth'
	import Flash from './helpers/flash'
	import { post, interceptors } from './helpers/api'
    import LoginForm from './views/Auth/Login.vue';
    import Modal from './components/Modal.vue';
    import { EventBus } from './event-bus';

	export default {
        components: {
            Modal,
            LoginForm,
        },
		created() {

			// global error http handler
			interceptors((err) => {
				if(err.response.status === 401) {
					Auth.remove();
//					this.$router.push('/login');
					this.showLoginModal = true;
				}

				if(err.response.status === 500) {
					Flash.setError(err.response.statusText)
				}

				if(err.response.status === 404) {
					this.$router.push('/not-found')
				}
			});

            EventBus.$on('blurWrapper', value => {
                this.blurWrapper = value;
            });

            EventBus.$on('contentLoading', value => {
                this.contentLoading = value;
            });

            Auth.initialize();
		},
		data() {
			return {
				authState: Auth.state,
				flash: Flash.state,
				menuExpanded: false,
                menuAnimating: false,
				contentLoading: false,
				body: document.querySelector('body'),
				showLoginModal: false,
				blurWrapper: false,
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
            finishedLoading() {
                this.contentLoading = false;
			},
            toggleMenu() {
		        this.menuExpanded = !this.menuExpanded;
                this.body.classList.toggle('noscroll', this.menuExpanded);
                this.menuAnimating = true;
                setTimeout(() => { this.menuAnimating = false; }, 500);
			},
		    navClick() {
                this.menuExpanded = false;
//                this.contentLoading = true;
                this.body.classList.remove('noscroll');
                setTimeout(() => { this.menuAnimating = false; }, 500);
			},
			logout() {
			    this.menuExpanded = false;
                this.contentLoading = true;
                setTimeout(() => { this.menuAnimating = false; }, 500);
				post('/api/logout')
				    .then((res) => {
				        if(res.data.done) {
				            // remove token
				            Auth.remove();
				            Flash.setSuccess('You have successfully logged out.');
				            this.$router.push('/login');
				            this.finishedLoading();
				        }
				    })
			}
		}
	}
</script>

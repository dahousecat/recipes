<template>

    <div class="row row--m">
        <div class="col col--1" :class="inModal? 'col--transparent' : ''">
            <div class="panel" :class="inModal? 'panel--nopad' : ''">
                <form class="form" @submit.prevent="login">
                    <h2>Please login to continue</h2>
                    <div class="form-group">
                        <label for="email" class="form-group__label">Email</label>
                        <input type="email" class="form-group__input" v-model="form.email" id="email">
                        <small class="form-group__error" v-if="error.email">{{error.email[0]}}</small>
                    </div>
                    <div class="form-group">
                        <label for="password" class="form-group__label">Password</label>
                        <input type="password" class="form-group__input" v-model="form.password" id="password">
                        <small class="form-group__error" v-if="error.password">{{error.password[0]}}</small>
                    </div>
                    <p>Don't have an account? <a @click="showRegisterForm">Register</a>.</p>

                    <div class="form-group">
                        <button :disabled="isProcessing" class="btn">Login</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


</template>
<script type="text/javascript">
    import Flash from '../../helpers/flash'
    import Auth from '../../store/auth'
    import { post } from '../../helpers/api'
    import { EventBus } from '../../event-bus';

    export default {
        data() {
            return {
                form: {
                    email: '',
                    password: ''
                },
                error: {},
                isProcessing: false
            }
        },
        props: {
            inModal: {
                type: [Boolean],
                default: false,
            },
        },
        created() {
            EventBus.$emit('contentLoading', false);
        },
        methods: {
            login() {
                this.isProcessing = true
                this.error = {}
                post('/api/login', this.form)
                    .then((res) => {
                        if(res.data.authenticated) {
                            // set token
                            Auth.set(res.data.api_token, res.data.user_id);
                            Flash.setSuccess('You have successfully logged in.');

                            if(!this.inModal || this.$root.destinaton !== null) {
                                let destination = typeof this.$root.destinaton === 'undefined' ? '/' : this.$root.destinaton;
                                this.$router.push(destination);
                                this.$root.destinaton = null;
                            }

                            this.$emit('close');

                        }
                        this.isProcessing = false
                    })
                    .catch((err) => {
                        if(typeof err.response !== 'undefined' && err.response.status === 422) {
                            this.error = err.response.data
                        }
                        this.isProcessing = false
                    })
            },
            showRegisterForm() {
                if(this.inModal) {
                    this.$emit('showRegisterForm');
                } else {
                    this.$router.push('register');
                }
            }
        },
    }
</script>

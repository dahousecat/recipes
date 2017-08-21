<template>
    <div class="row row--m">
        <div class="col-1">
            <div class="panel">

                <form class="form" @submit.prevent="register">
                    <h2>Create an Account</h2>
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form__control" v-model="form.name" id="name">
                        <small class="error__control" v-if="error.name">{{error.name[0]}}</small>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="text" class="form__control" v-model="form.email" id="email">
                        <small class="error__control" v-if="error.email">{{error.email[0]}}</small>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form__control" v-model="form.password" id="password">
                        <small class="error__control" v-if="error.password">{{error.password[0]}}</small>
                    </div>
                    <div class="form-group">
                        <label for="confirm-password">Confirm Password</label>
                        <input type="password" class="form__control" v-model="form.password_confirmation" id="confirm-password">
                    </div>
                    <p>Already have an account? <a @click="$emit('showLoginForm')">Login</a>.</p>
                    <div class="form-group">
                        <button :disabled="isProcessing" class="btn">Register</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</template>

<script type="text/javascript">
    import Flash from '../../helpers/flash'
    import { post } from '../../helpers/api'
    export default {
        data() {
            return {
                form: {
                    name: '',
                    email: '',
                    password: '',
                    password_confirmation: ''
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
        methods: {
            register() {
                this.isProcessing = true
                this.error = {}
                post('api/register', this.form)
                    .then((res) => {
                        if(res.data.registered) {
                            Flash.setSuccess('Congratulations! You have now successfully registered.');
                            if(this.inModal) {
                                this.$emit('close');
                            } else {
                                this.$router.push('/login')
                            }
                        }
                        this.isProcessing = false
                    })
                    .catch((err) => {
                        if(err.response.status === 422) {
                            this.error = err.response.data
                        }
                        this.isProcessing = false
                    })
            },
        }
    }
</script>

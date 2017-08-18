<template>

    <div class="row--m">
        <div class="col-1">
            <div class="panel">
                <form class="form" @submit.prevent="login">
                    <h1>Welcome back!</h1>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form__control" v-model="form.email" id="email">
                        <small class="error__control" v-if="error.email">{{error.email[0]}}</small>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form__control" v-model="form.password" id="password">
                        <small class="error__control" v-if="error.password">{{error.password[0]}}</small>
                    </div>
                    <div class="form-group">
                        <button :disabled="isProcessing" class="btn btn__primary">Login</button>
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
    import { loading } from '../../helpers/misc';
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
        created() {
            EventBus.$emit('contentLoading', false);
        },
        methods: {
            login() {
                this.isProcessing = true
                this.error = {}
                post('api/login', this.form)
                    .then((res) => {
                        if(res.data.authenticated) {
                            // set token
                            Auth.set(res.data.api_token, res.data.user_id)
                            Flash.setSuccess('You have successfully logged in.')
                            this.$router.push('/')
                        }
                        this.isProcessing = false
                    })
                    .catch((err) => {
                        if(err.response.status === 422) {
                            this.error = err.response.data
                        }
                        this.isProcessing = false
                    })
            }
        }
    }
</script>

<template>
    <div class="ingredient-index">
        <button @click="$router.push('/ingredients/create')">Create Ingredient</button>

        <div class="ingredient__list">

            <table>
                <tr>
                    <th>Name</th>
                    <th>Actions</th>
                </tr>
                <tr v-for="ingredient in ingredients">
                    <td>{{ingredient.name}}</td>
                    <td>
                        <router-link class="ingredient__inner" :to="`/ingredients/${ingredient.id}/edit`">
                           Edit
                        </router-link> |
                        <a @click="deleteIngredient(ingredient.id)">Delete</a>
                    </td>
                </tr>
            </table>

        </div>
    </div>
</template>
<script type="text/javascript">
    import { get, post } from '../../helpers/api'
    import { loading } from '../../helpers/misc';

    export default {
        data() {
            return {
                ingredients: []
            }
        },
        created() {
            loading(true);
            get('/api/ingredients')
                .then((res) => {
                    this.ingredients = res.data.ingredients
                    loading(false);
                })
        },
        methods: {
            deleteIngredient(id) {
                loading(true);
                let data = {'_method': 'DELETE'};
                post('/api/ingredients/' + id + '?_method=DELETE', data)
                    .then((res) => {
                        loading(false);
                        Flash.setSuccess('Ingredient deleted.');
                    })
                    .catch((err) => {
                        loading(false);
                        if(typeof err.response !== 'undefined' && err.response.status === 422) {
                            this.error = err.response.data
                        }
                    })
            }
        }
    }
</script>

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
                        <router-link class="ingredient__inner" :to="`/ingredients/${ingredient.id}/edit`">Edit</router-link> |
                        <a @click="deleteClick(ingredient.id)">Delete</a>
                    </td>
                </tr>
            </table>

        </div>

        <!-- Confirm delete modal -->
        <modal :show="showDeleteIngredientModal" @close="hideDeleteModal()">
            <h2 slot="title">Are you sure you want to delete {{ingredientToDeleteName}}</h2>

            <button @click="deleteIngredient(ingredientToDeleteId)">Yes</button>
            <button @click="hideDeleteModal()">No</button>
        </modal>
    </div>
</template>
<script type="text/javascript">
    import { get, post } from '../../helpers/api'
    import { loading } from '../../helpers/misc';
    import Modal from '../../components/Modal.vue';

    export default {
        components: {
            Modal,
        },
        data() {
            return {
                ingredients: [],
                showDeleteIngredientModal: false,
                ingredientToDeleteName: '',
                ingredientToDeleteId: null,
            }
        },
        created() {
            loading(true);
            get('/api/ingredients')
                .then((res) => {
                    this.ingredients = res.data.ingredients
                    loading(false);
                    this.$emit('finishedLoading');
                })
        },
        methods: {
            deleteClick(id) {
                let ingredient = this.getIngredient(id);
                this.showDeleteIngredientModal = true;
                this.ingredientToDeleteName = ingredient.name;
                this.ingredientToDeleteId = id;
            },
            hideDeleteModal() {
                this.showDeleteIngredientModal = false;
                this.ingredientToDeleteName = '';
                this.ingredientToDeleteId = null;
            },
            deleteIngredient(id) {
                loading(true);
//                let data = {'_method': 'DELETE'};
                post('/api/ingredients/' + id + '?_method=DELETE')
                    .then((res) => {
                        loading(false);

                        // Remove the ingredient just deleted from the ingredient array
                        let index = this.getIngredientIndex(id);
                        this.ingredients.splice(index, 1);

                        this.hideDeleteModal();

                        Flash.setSuccess('Ingredient deleted.');
                    })
                    .catch((err) => {
                        loading(false);
                        if(typeof err.response !== 'undefined' && err.response.status === 422) {
                            this.error = err.response.data
                        }
                    })
            },
            getIngredient(id) {
                let index = this.getIngredientIndex(id);
                return this.ingredients[index];
            },
            getIngredientIndex(id) {
                for (let i = 0; i < this.ingredients.length; i++) {
                    let ingredient = this.ingredients[i];
                    if(ingredient.id === id) {
                        return i;
                    }
                }
            }
        }
    }
</script>

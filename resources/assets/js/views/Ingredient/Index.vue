<template>
    <div class="ingredient-index">

        <div class="row row--m">
            <div class="col-1">
                <div class="panel panel--header">
                    <h2>Ingredients</h2>
                    <div class="recipe__button-group" v-if="$root.auth">
                        <button @click="$router.push('/ingredients/create')" class="btn">Create Ingredient</button>
                    </div>
                </div>
            </div>
        </div>


        <div class="row row--l">
            <div class="col-1">
                <div class="panel">

                    <div class="ingredient__list">

                        <table class="minimal-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th v-if="$root.auth">Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr v-for="ingredient in ingredients">
                                    <td>{{ingredient.name}}</td>
                                    <td v-if="$root.auth">
                                        <router-link class="ingredient__inner" :to="`/ingredients/${ingredient.id}/edit`">Edit</router-link> |
                                        <a @click="deleteClick(ingredient.id)">Delete</a>
                                    </td>
                                </tr>
                            </tbody>

                        </table>

                    </div>

                </div>
            </div>
        </div>


        <!-- Confirm delete modal -->
        <modal :show="showDeleteIngredientModal" @close="hideDeleteModal()">
            <h2 slot="title">Are you sure you want to delete {{ingredientToDeleteName}}</h2>

            <button @click="deleteIngredient(ingredientToDeleteId)" class="btn">Yes</button>
            <button @click="hideDeleteModal()" class="btn">No</button>
        </modal>
    </div>
</template>
<script type="text/javascript">
    import { get, post } from '../../helpers/api'
    import { loading } from '../../helpers/misc';
    import Modal from '../../components/Modal.vue';
    import { EventBus } from '../../event-bus';

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
            EventBus.$emit('contentLoading', true);
            get('/api/ingredients')
                .then((res) => {
                    this.ingredients = res.data.ingredients
                    EventBus.$emit('contentLoading', false);
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
                EventBus.$emit('contentLoading', true);
                post('/api/ingredients/' + id + '?_method=DELETE')
                    .then((res) => {
                        EventBus.$emit('contentLoading', false);

                        // Remove the ingredient just deleted from the ingredient array
                        let index = this.getIngredientIndex(id);
                        this.ingredients.splice(index, 1);

                        this.hideDeleteModal();

                        Flash.setSuccess('Ingredient deleted.');
                    })
                    .catch((err) => {
                        EventBus.$emit('contentLoading', false);
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

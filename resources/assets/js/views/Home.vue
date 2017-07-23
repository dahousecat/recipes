<template>
    <div class="home">

        <div class="flex-row">

            <div class="recipe__panel">
                <h3 class="recipe__sub_title">All recipes</h3>

                <div class="recipe__item" v-for="recipe in recipes">
                    <router-link class="recipe__inner" :to="`/recipes/${recipe.id}`">
                        <img :src="`/images/${recipe.image}`" v-if="recipe.image">
                        <p class="recipe__name">{{recipe.name}}</p>
                    </router-link>
                </div>
            </div>



        </div>

    </div>

</template>
<script type="text/javascript">
    import { get } from '../helpers/api'
    export default {
        data() {
            return {
                recipes: []
            }
        },
        created() {
            get('/api/recipes')
                .then((res) => {
                    this.recipes = res.data.recipes
                })
        }
    }
</script>

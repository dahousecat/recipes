<template>
    <div class="ingredient__list">
        <div class="ingredient__item" v-for="ingredient in ingredients">
            <router-link class="ingredient__inner" :to="`/ingredients/${ingredient.id}/edit`">
                <p class="ingredient__name">{{ingredient.name}}</p>
            </router-link>
        </div>
    </div>
</template>
<script type="text/javascript">
    import { get } from '../../helpers/api'
    export default {
        data() {
            return {
                ingredients: []
            }
        },
        created() {
            get('/api/ingredients')
                .then((res) => {
                    this.ingredients = res.data.ingredients
                })
        }
    }
</script>

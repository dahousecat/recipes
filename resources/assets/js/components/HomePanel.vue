<template>
    <div class="home-panel">
        <h3 class="home-panel__title" v-if="title.length">
            {{title}}
        </h3>
        <h3 class="home-panel__title" v-if="!title.length">
            Recipes <span v-if="typeof attributeType !== 'undefined'">with most {{attributeType.name}}</span>
        </h3>

        <div class="home-panel__item" v-for="recipe in recipes">
            <router-link class="home-panel__link" :to="`/recipes/${recipe.id}`">
                <img :src="`/images/${recipe.image}`" v-if="recipe.image">
                <div class="home-panel__name">{{recipe.name}}</div>
                <div class="home-panel__value" v-if="typeof recipe.val !== 'undefined'">
                    {{formatNumber(recipe.val)}} {{attributeType.unit}}
                </div>
            </router-link>
        </div>
    </div>
</template>

<script type="text/javascript">
    import { loading } from '../helpers/misc';
    import { formatNumber } from '../helpers/misc';
    import { get, post } from '../helpers/api';

    export default {
        props: {
            recipes: {
                type: Array,
                default: () => [],
            },
            attributeType: {
                type: Object,
                default: () => {},
            },
            title: {
                type: String,
                default: '',
            },
        },
        methods: {
            formatNumber(number) {
                return formatNumber(number);
            }
        },
    }
</script>

<style lang="scss">
    /*@import "~sass-mq/_mq.scss";*/
    @import "../../sass/variables/breakpoints";

    .home {
        padding-top: 1rem;
    }

    .home-panel {
        background-color: lightgray;
        padding: 1rem;
        margin: 0 1rem 1rem 1rem;

        @include mq($from: s) {
            flex: 0 0 50%;
        }
    }

    .home-panel__item {

    }

    .home-panel__link {
        display: flex;
        justify-content: space-between;
    }

</style>
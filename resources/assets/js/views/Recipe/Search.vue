<template>
    <div class="search">

        <div class="search__header">
            <label for="search">Add ingredients to filter by</label>

            <div class="search__field-wrapper">
                <input class="search__input" id="search" v-model="searchTerm">
                <ul class="search__auto-complete-results">
                    <li class="search__auto-complete-result"
                        v-for="(ingredient, index) in searchResults"
                        @click="addFilter(ingredient)">
                        {{ingredient.name}}
                    </li>
                </ul>
            </div>

            <div class="search__filter-item"
                 v-for="(ingredient, index) in filters"
                 @click="removeFilter(ingredient)">
                {{ingredient.name}} <i class="fa fa-times" aria-hidden="true"></i>
            </div>

        </div>

        <ul class="search__recipes">
            <li v-for="(recipe, index) in recipes">
                <router-link :to="`/recipes/${recipe.id}`">
                    {{recipe.name}}
                </router-link>

            </li>
        </ul>

    </div>
</template>

<script type="text/javascript">
    import Vue from 'vue';
    import { get } from '../../helpers/api';

    export default {
        data() {
            return {
                initializeURL: 'api/recipes',
                recipes: [],
                searchTerm: '',
                searchResults: [],
                filters: [],
            }
        },
        created() {
            get(this.initializeURL)
                .then((res) => {
                    Vue.set(this.$data, 'recipes', res.data.recipes);
                });

        },
        watch: {
            // For ingredient filter
            searchTerm: function(str){
                if(str.length) {
                    let url = '/api/ingredients/search/' + str;
                    get(url)
                        .then((res) => {
                            Vue.set(this.$data, 'searchResults', res.data.ingredients);
                        });
                } else {
                    this.searchResults = [];
                }

            },
        },
        methods: {
            addFilter(ingredient) {
                if(!this.findFilterIndex(ingredient.id)) {
                    this.filters.push(ingredient);
                    this.searchResults = [];
                    this.searchTerm = '';
                    this.updateRecipes();
                }
            },
            removeFilter(ingredient) {
                let index = this.findFilterIndex(ingredient.id);
                if(typeof index === 'number') {
                    this.filters.splice(index, 1);
                    this.updateRecipes();
                }
            },
            findFilterIndex(ingredient_id) {
                for(let i = 0; i < this.filters.length; i++) {
                    let ingredient = this.filters[i];
                    if(ingredient.id === ingredient_id) {
                        return i;
                    }
                }
            },
            updateRecipes() {
                let url = '/api/recipes?';

                for(let i = 0; i < this.filters.length; i++) {
                    let ingredient = this.filters[i];
                    url = url + 'contains[]=' + ingredient.id + '&';
                }

                get(url)
                    .then((res) => {
                        Vue.set(this.$data, 'recipes', res.data.recipes);
                    });
            }
        }
    }
</script>

<style lang="scss">
    .search__field-wrapper {
        position: relative;
        width: 22rem;
    }
    .search__input {
        width: 100%;
    }
    .search__auto-complete-results {
        position: absolute;
        border: 1px solid lightgrey;
        background-color: white;
        list-style: none;
        padding: 0;
        top: 1.7rem;
        width: 100%;

    }
    .search__auto-complete-result {
        padding: 1rem;
        border-bottom: 1px solid lightgrey;
        cursor: pointer;
        transition: background-color 200ms;

        &:hover {
            background-color: darkcyan;
            color: white;
        }

        &:last-child {
            border-bottom: none;
        }
    }
    .search__filter-item {
        display: inline-block;
        padding: 0.4rem 1.2rem;
        background-color: darkcyan;
        color: white;
        border-radius: 1rem;
        margin: 0 0.6rem 0.6rem 0;
        cursor: pointer;
        transition: background-color 200ms;

        &:hover {
            background-color: darken(darkcyan, 10);
        }
    }
</style>
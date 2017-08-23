<template>
	<div class="search">

		<div class="row row--m">
			<div class="col col--1">
				<div class="panel panel--header">
					<h2>Recipes</h2>
					<div class="recipe__button-group">
						<button @click="$router.push('/recipes/create')" class="btn">Create Recipe</button>
					</div>
				</div>
			</div>
		</div>

		<div class="row row--m">
			<div class="col col--1">
				<div class="panel">

					<label for="search">Add ingredients to filter by</label>

					<div class="search__field-wrapper">
						<input class="search__input" id="search" v-model="searchTerm">
						<ul class="search__auto-complete-results" v-if="searchResults.length">
							<li class="search__auto-complete-result"
								v-for="(ingredient, index) in searchResults"
								@click="addFilter(ingredient)">
								{{ingredient.name}}
							</li>
						</ul>
					</div>

					<div class="search__filter-items">
						<div class="search__filter-item"
							 v-for="(ingredient, index) in filters"
							 @click="removeFilter(ingredient)">
							{{ingredient.name}} <i class="fa fa-times" aria-hidden="true"></i>
						</div>
					</div>

				</div>
			</div>
			<div class="col col--3">
				<div class="panel">

					<div class="table-controls">
						<label for="sort-by">Sort by</label>
						<select v-model="sortBy" @change="updateRecipes" id="sort-by">
							<option v-for="(option, index) in sortableBy" :value="index">{{option}}</option>
						</select>
					</div>

					<table class="minimal-table">
						<thead>
							<tr>
								<th>Name</th>
								<th v-if="$root.auth">Actions</th>
								<th v-if="sortByAttribute !== null">
									{{sortByAttribute.name}}
								</th>
							</tr>
						</thead>

						<tbody>
							<tr v-for="recipe in recipes">
								<td>
									<router-link class="ingredient__inner" :to="`/recipes/${recipe.id}`">{{recipe.name}}</router-link>
								</td>
								<td v-if="$root.auth">
									<router-link class="ingredient__inner" :to="`/recipes/${recipe.id}/edit`">Edit</router-link> |
									<a @click="deleteClick(recipe.id)">Delete</a>
								</td>
								<td v-if="sortByAttribute !== null">
									{{formatNumber(recipe.val)}} {{sortByAttribute.unit}}
								</td>
							</tr>
						</tbody>

					</table>

				</div>
			</div>
		</div>

	</div>
</template>

<script type="text/javascript">
    import Vue from 'vue';
    import { get } from '../../helpers/api';
    import { formatNumber } from '../../helpers/misc';
    import { capitalize } from '../../helpers/misc';

    export default {
        data() {
            return {
                recipes: [],
                searchTerm: '',
                searchResults: [],
                filters: [],
                sortableBy: {},
                sortBy: 'recipe_name',
                sortByAttribute: null,
            }
        },
        created() {
            get('api/recipes?showSortableBy=true')
                .then((res) => {
                    Vue.set(this.$data, 'recipes', res.data.recipes);
                    Vue.set(this.$data, 'sortableBy', res.data.sortableBy);
                });
        },
        watch: {
            // For ingredient filter
            searchTerm: function(str) {
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

                url = url + 'sortBy=' + this.sortBy;

                get(url)
                    .then((res) => {
                        Vue.set(this.$data, 'recipes', res.data.recipes);

                        if(typeof res.data.sortByAttribute) {
                            Vue.set(this.$data, 'sortByAttribute', res.data.sortByAttribute);
                        }

                    });
            },
            formatNumber(number) {
                return formatNumber(number);
            },
            capitalize(string) {
                return capitalize(string);
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
	.search__filter-items {
		margin-top: 1rem;
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
	.search__recipes-row {
		display: flex;
		justify-content: space-between;
	}
	.table-controls {
		float: right;
		margin-bottom: 1rem;
	}
</style>
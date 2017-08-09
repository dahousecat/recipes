<template>
	<div class="recipe">

		<div class="row--m">
			<div class="col-1">
				<div class="panel recipe__header">
					<h2>{{action}} Recipe</h2>
					<div class="recipe__button-group">
						<button class="btn" @click="save" :disabled="isProcessing">Save</button>
						<button class="btn" @click="$router.back()" :disabled="isProcessing">Cancel</button>
					</div>
				</div>
			</div>
		</div>



		<div class="row--m">

			<!-- Image -->
			<div class="col-1 col--center">
				<div class="panel recipe__image">
					<image-upload v-model="form.image"></image-upload>
					<small class="error__control" v-if="error.image">{{error.image[0]}}</small>
				</div>
			</div>

			<!-- Basic details -->
			<div class="col-1">
				<div class="panel">
					<h3>Basic details</h3>

					<div class="form-group recipe__form-group">
						<label class="form-group__label">Name</label>
						<input type="text" class="form-group__input" v-model="form.name">
						<small class="error__control" v-if="error.name">{{error.name[0]}}</small>
					</div>
					<div class="form-group recipe__form-group">
						<label class="form-group__label">Description</label>
						<textarea class="form-group__input" v-model="form.description"></textarea>
						<small class="error__control" v-if="error.description">{{error.description[0]}}</small>
					</div>
					<div class="form-group recipe__form-group">
						<label class="form-group__label">Number of portions?</label>
						<input type="text" class="form-group__input" v-model="form.portions">
						<small class="error__control" v-if="error.portions">{{error.portions[0]}}</small>
					</div>
				</div>
			</div>
		</div>

		<div class="row--m">

			<!-- Ingredients -->
			<div class="col-1">
				<div class="panel recipe__ingredients-panel">
					<h3>Ingredients</h3>

					<div class="recipe__ingredient-search-input-wrapper"
						 :class="searchingForIngredient? 'loading loading--input' : ''">
						<input class="recipe__ingredient-search-input" type="text"
							   v-model="ingredientSearchTerm" placeholder="Search for an ingredient...">
					</div>

					<ul class="recipe__ingredient-search-results" v-if="ingredientSearchResults">
						<li v-for="item in ingredientSearchResults"
							class="recipe__ingredient-search-result"
							@click="addIngredient(item)">
							{{item.ingredient.name}}
						</li>
					</ul>

					<!--<draggable v-model="form.rows"-->
							   <!--:class="{ 'drop-zone': this.isDragging, 'ingredient-row&#45;&#45;empty': !form.rows.length }"-->
							   <!--:options='{group:"recipe", handle:".ingredient-row__handle", filter: ".empty-message"}'-->
							   <!--@start="dragStart" @end="dragEnd"-->
							   <!--class="recipe__rows">-->

						<ingredient-row
								v-for="(row, index) in form.rows"
								:row="row"
								@rowUpdated="rowUpdated(row)"
								@removeIngredient="removeIngredient(index)">
						</ingredient-row>

					<!--</draggable>-->

				</div>
			</div>

			<!-- Nutrients -->
			<nutrients
					@nutritionUpdated="recalculateNutrition=false"
					:rows="form.rows"
					:units="units"
					:recalculate="recalculateNutrition"
					class="recipe__panel"></nutrients>

		</div>

		<div class="row--m">

			<!-- Directions -->
			<div class="col-1">
				<div class="panel">
					<h3>Directions</h3>
					<div v-for="(direction, index) in form.directions" class="recipe__directions-inner">
						<textarea class="recipe__direction" v-model="direction.description"
								  :class="[error[`directions.${index}.description`] ? 'error__bg' : '']"
						></textarea>
						<button @click="remove('directions', index)" class="recipe__remove-direction-btn">&times;</button>
					</div>
					<button @click="addDirection" class="btn recipe__add-direction-btn">Add Direction</button>
				</div>
			</div>
		</div>

	</div>
</template>

<script type="text/javascript">
	import Vue from 'vue';
	import Flash from '../../helpers/flash';
	import { get, post } from '../../helpers/api';
	import { convertEnergyUnit, formatNumber, getUnit } from '../../helpers/misc';
	import { toMulipartedForm, objectToFormData } from '../../helpers/form';
	import ImageUpload from '../../components/ImageUpload.vue';
	import draggable from 'vuedraggable';
	import Nutrients from '../../components/Nutrients.vue';
	import IngredientRow from '../../components/IngredientRow.vue';
    import { loading } from '../../helpers/misc';

	Vue.use(draggable);

	export default {
		components: {
			ImageUpload,
			draggable,
            Nutrients,
            IngredientRow,
		},
		data() {
			return {
				form: {
					rows: [],
					directions: []
				},
                ingredientSearchTerm: '',
				ingredients: [],
                ingredientSearchResults: [],
				nutrients: {},
				units: [],
				amountPer: 'recipe',
				amountPerOptions: [
					{'value': 'recipe', 'name': 'Recipe'},
					{'value': 1, 'name': '1 gram'},
					{'value': 10, 'name': '10 grams'},
					{'value': 100, 'name': '100 grams'},
					{'value': 1000, 'name': '1 kg'},
				],
				energyUnit: 'calorie',
				energyUnitOptions: [
					{'value': 'calorie', 'name': 'Calories'},
					{'value': 'Kj', 'name': 'Kilojoule'},
				],
				conversions: {
				    caloriesInKj: 0.239006,
				},
				totalWeight: 0,
				error: {},
				isProcessing: false,
				initializeURL: `/api/recipes/create`,
				storeURL: `/api/recipes`,
				action: 'Create',
				ingredient: '',
				isDragging: false,
				nutritionUpdating: false,
				editIngredient: [],
				showIngredientModal: false,
                attributeTypes: [],
				recalculateNutrition: false,
				searchingForIngredient: false,
			}
		},
		watch: {
            ingredientSearchTerm: function(str){

			    if(!str.length) {
			        this.ingredientSearchResults = [];
			        return;
				}

                this.searchingForIngredient = true;
			    let _this = this;

                let url = '/api/ingredients/search/' + str;
                get(url)
                    .then((res) => {
                        this.prepareIngredients(res.data, 'ingredientSearchResults');
                        _this.searchingForIngredient = false;
                    })
            },
            '$route' (to, from) {
			    console.log('route change');
                this.init();
            }
		},

		// Run on initialization
		created() {
			this.init();

		},
		methods: {
		    init() {
                if(this.$route.meta.mode === 'edit') {
                    this.initializeURL = `/api/recipes/${this.$route.params.id}/edit`;
                    this.storeURL = `/api/recipes/${this.$route.params.id}?_method=PUT`;
                    this.action = 'Update';
                } else {
                    this.initializeURL = `/api/recipes/create`;
                    this.storeURL = `/api/recipes`;
                    this.action = 'Create';
				}

                get(this.initializeURL)
                    .then((res) => {
                        Vue.set(this.$data, 'form', res.data.form);
                        Vue.set(this.$data, 'units', res.data.units);
                        Vue.set(this.$data, 'attributeTypes', res.data.attributeTypes);

                        this.prepareIngredients(res.data, 'ingredients');

						// Just set data so wait till next tick to update the recipe rows
						let _this = this;
						this.$nextTick(function () {
							for (let i = 0; i < _this.form.rows.length; i++) {
								console.log('Set row nutrition recalculate to true');
								_this.form.rows[i].recalculate = true;
							}

							// And then the tick after that to update the recipe nutrients
							Vue.nextTick(function () {
								_this.recalculateNutrition = true;
							});
						});

						this.$emit('finishedLoading');

                    });
			},
			prepareIngredients(data, saveTo) {
                // Ingredients can turn into rows when they are dragged there.
                // Because of this we need to put the ingredient properties in their own namespace.
                let ingredients = [];
                for(let i = 0; i < data.ingredients.length; i++) {
                    let ingredient = data.ingredients[i];

                    // Set an empty object here so we can fill it when needed
                    ingredient.nutrients = {};
                    ingredient.units = {};

                    // If unit_id is not set and there is only one unit available set it as a default
                    if(ingredient.default_unit_id === null && ingredient.units.length === 1) {
                        ingredient.default_unit_id = ingredient.units[0].unit_id;
                    }

                    // We need a unit_id AND unit as that is the selects model. When it's updated the unit is
                    // automatically updated.
                    ingredients.push({
                        ingredient: ingredient,
                        unit_id: ingredient.default_unit_id,
                        unit: getUnit(ingredient.default_unit_id, this.units),
                        value: 1,
                        nutrients: {},
                        recalculate: false,
                        recalculateRecipeNutrition: false,
                    });
                }
                Vue.set(this.$data, saveTo, ingredients);
			},
            addIngredient(item) {

			    // Make new row from item
                let row = item;
                this.ingredientSearchResults = [];
                this.ingredientSearchTerm = '';
                let _this = this;

                // Make sure the nutrients for this ingredient are set
                this.fetchIngredientDetails(row).then(function(){

                    _this.form.rows.push(row);

                    // Ingredient added to recipe
                    // Recalculate this row - this will trigger recalculating the recipe nutrients
                    _this.form.rows[ _this.form.rows.length - 1 ].recalculate = true;

                    // If this is set to true after the now nutrients are updated it will trigger a recipe
                    // nutrition update
                    _this.form.rows[ _this.form.rows.length - 1 ].recalculateRecipeNutrition = true;

                });

			},
			fetchIngredientDetails(row) {

                return new Promise(function(resolve, reject){

                    if(typeof row === 'undefined') {
                        console.log('Row has no ingredient. Can\'t fetch details.');
                        // This row doesn't even have an ingredient. Get outa here.
                        resolve();
                        return;
                    }
                    let ingredient = row.ingredient;

                    if(typeof ingredient.weight_one !== 'undefined') {
                        console.log('Row has weight_one. No need to fetch details.');
                        // They have already been fetched. Nothing to do.
                        resolve();
                        return;
                    }

					loading(true);

                    console.log('Fetching ' + ingredient.name + ' details');

                    get('/api/ingredients/' + ingredient.id)
                        .then((res) => {
                            ingredient.nutrients = res.data.ingredient.nutrients;
                            ingredient.units = res.data.ingredient.units;
                            ingredient.weight_one = res.data.ingredient.weight_one;
                            ingredient.weight_one_cup = res.data.ingredient.weight_one_cup;
                            ingredient.weight_one_ml = res.data.ingredient.weight_one_ml;
                            ingredient.weight_one_cm = res.data.ingredient.weight_one_cm;
                            loading(false);
                            resolve();
                        })
                        .catch(function (error) {
                            loading(false);
                            console.log(error);
                            reject();
                        });
                });
			},
			save() {

                // Clone the form so we don't alter the original
                let data = JSON.parse(JSON.stringify(this.form));

                // Remove empty directions
                for (let i = 0; i < data.directions.length; i++) {
					if(data.directions[i].description.length === 0) {
                        data.directions.splice(i, 1);
					}
                }

                // Remove empty image
                if(!data.image) {
                    delete data.image;
				}

				// Prepare rows
				let rowData = [];
                for (let i = 0; i < data.rows.length; i++) {
                    let row = data.rows[i];
                    rowData.push({
						ingredient_id: row.ingredient.id,
						delta: i,
						unit_id: row.unit.id,
						value: row.value,
						weight: row.weight,
					});
                }

                data.rows = rowData;

                console.log(data, 'save recipe data');

				post(this.storeURL, objectToFormData(data))
				    .then((res) => {
				        if(res.data.saved) {
				            Flash.setSuccess(res.data.message);
				            this.$router.push(`/recipes/${res.data.id}`)
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
			addDirection() {
				this.form.directions.push({
					description: ''
				})
			},
			remove(type, index) {
				if(this.form[type].length > 1) {
					this.form[type].splice(index, 1)
				}
			},
            removeIngredient(index) {
				this.ingredients.push(this.form.rows[index]);
                this.form.rows.splice(index, 1);
                this.recalculateNutrition = true;
			},
            rowUpdated(row) {
			    row.recalculate = false;
			    if(row.recalculateRecipeNutrition) {
                    this.recalculateNutrition = true;
				}
			},
		}
	}
</script>

<style lang="scss">
	@import "../../../sass/variables/breakpoints";

	div.recipe__header {
		padding: 0.8rem 1.2rem;

		@include mq($from: xs) {
			display: flex;
			justify-content: space-between;
			align-items: center;
		}
	}

	.recipe__button-group {
		margin-top: 1rem;

		@include mq($from: xs) {
			margin-top: 0;
		}
	}

	.recipe__image {
		display: flex;
		align-items: center;
	}

	.recipe__box {
		display: flex;
		flex-direction: column;
		align-items: stretch;
		flex: 1;
	}

	.recipe__rows {
		min-height: 10rem;
		position: relative;
		flex: 1;
	}

	.recipe__rows--empty::after {
		content: 'Drag some ingredients here to start your recipe.';
		margin: auto;
		position: absolute;
		top: 0;
		left: 0;
		bottom: 0;
		right: 0;
		height: 4rem;
		text-align: center;
	}

	.recipe__form-group {
		@include mq($from: m) {
			flex-direction: column;
		}
		@include mq($from: l) {
			flex-direction: row;
		}
	}

	.recipe__ingredients-panel {
		position: relative;
	}

	.recipe__directions-inner {
		display: flex;
		align-content: space-between;
	}

	.recipe__directions-button {
		width: 3rem;
		height: 3rem;
		padding: 0px;
		line-height: 1;
		margin: 1.3rem 1rem 0 1rem;
	}

	.recipe__ingredient-search-input-wrapper {
		margin-bottom: 1rem;
	}

	.recipe__ingredient-search-input {
		width: 100%;
		padding: 0.6rem;
	}

	.recipe__ingredient-search-results {
		position: absolute;
		list-style: none;
		margin: 0;
		padding: 0;
		min-width: 19.8rem;
		left: 0;
		right: 0;
		z-index: 20;
		border: 1px solid lightgray;
	}

	.recipe__ingredient-search-result {
		padding: 0.6rem 1rem;
		background-color: white;
		border-bottom: 1px solid lightgray;
		transition: background-color 200ms;
		cursor: pointer;

		&:hover {
			background-color: darkcyan;
			color: white;
		}
	}

	.recipe__direction {
		width: 100%;
	}

	.recipe__remove-direction-btn {
		border: none;
		background-color: grey;
		margin-left: 1rem;
		min-width: 3rem;

		&:hover {
			color: red;
		}
	}

	.recipe__add-direction-btn {
		margin-top: 1rem;
	}

</style>
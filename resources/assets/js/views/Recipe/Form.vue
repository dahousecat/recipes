<template>
	<div class="recipe">

		<div class="recipe__header">
			<h2>{{action}} Recipe</h2>
			<div>
				<button class="btn btn__primary" @click="save" :disabled="isProcessing">Save</button>
				<button class="btn" @click="$router.back()" :disabled="isProcessing">Cancel</button>
			</div>
		</div>

		<div class="flex-row">

			<!-- Image -->
			<div class="recipe__panel">
				<image-upload v-model="form.image"></image-upload>
				<small class="error__control" v-if="error.image">{{error.image[0]}}</small>
			</div>

			<!-- Basic details -->
			<div class="recipe__panel">
				<div class="form__group">
					<label>Name</label>
					<input type="text" class="form__control" v-model="form.name">
					<small class="error__control" v-if="error.name">{{error.name[0]}}</small>
				</div>
				<div class="form__group">
					<label>Description</label>
					<textarea class="form__control form__description" v-model="form.description"></textarea>
					<small class="error__control" v-if="error.description">{{error.description[0]}}</small>
				</div>
			</div>
		</div>

		<div class="flex-row">

			<!-- Ingredients -->
			<div class="recipe__panel">
				<div class="recipe__box">
					<h3 class="recipe__sub_title">Ingredients</h3>

					<draggable v-model="form.rows"
							   :class="{ 'drop-zone': this.isDragging, 'recipe__rows--empty': !form.rows.length }"
							   :options='{group:"recipe", handle:".ingredient_row__handle", filter: ".empty-message"}'
							   @start="dragStart" @end="dragEnd"
							   class="recipe__rows">

						<ingredient-row
								v-for="(row, index) in form.rows"
								:row="row"
								@rowUpdated="rowUpdated(row)"
								@removeIngredient="removeIngredient(index)">
						</ingredient-row>

					</draggable>

				</div>
			</div>

			<!-- Pantry -->
			<div class="recipe__panel">
				<div class="recipe__pantry_inner">
					<h3 class="recipe__sub_title">Pantry</h3>

					<div class="form__group">
						<label for="search">Search</label>
						<input id="search" type="text" class="form__control" v-model="ingredient">
					</div>

					<draggable v-model="ingredients" :options='{group:"recipe"}' @start="dragStart" @end="dragEnd">
						<div v-for="item in ingredients" class="recipe__ingredient grabber">
							{{item.ingredient.name}}
						</div>
					</draggable>

				</div>
			</div>

		</div>

		<div class="flex-row">

			<!-- Nutrients -->
			<nutrients
					@nutritionUpdated="recalculateNutrition=false"
					:rows="form.rows"
					:units="units"
					:recalculate="recalculateNutrition"
					class="recipe__panel"></nutrients>

			<!-- Directions -->
			<div class="recipe__panel">
				<div class="">
					<h3 class="recipe__sub_title">Directions</h3>
					<div v-for="(direction, index) in form.directions" class="recipe__directions-inner">
						<textarea class="form__control form__margin" v-model="direction.description"
								  :class="[error[`directions.${index}.description`] ? 'error__bg' : '']"
						></textarea>
						<button @click="remove('directions', index)" class="recipe__directions-button">&times;</button>
					</div>
					<button @click="addDirection" class="btn">Add Direction</button>
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
				ingredients: [],
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
			}
		},
		watch: {
			// For ingredient filter
			ingredient: function(str){
				let url = str.length > 1 ? '/api/ingredients/search/' + str : '/api/ingredients';
				get(url)
						.then((res) => {
							Vue.set(this.$data, 'ingredients', this.rowify(res.data.ingredients));
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

                        this.prepareIngredients(res.data);


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


                    });
			},
			prepareIngredients(data) {
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
                Vue.set(this.$data, 'ingredients', ingredients);
			},
			dragStart(){

				this.isDragging = true;
				// let item = this.ingredients[evt.oldIndex];

			},
			dragEnd(evt){

				this.isDragging = false;

				let row = this.editIngredient.length === 1 ? this.editIngredient[0] :  this.form.rows[evt.newIndex];
				let _this = this;

				// Make sure the nutrients for this ingredient are set
				this.fetchIngredientDetails(row).then(function(){

                    if(_this.editIngredient.length === 1) {
                        // Ingredient dropped on the edit ingredient zone
                        _this.showIngredientModal = true;

                    } else if(typeof _this.form.rows[evt.newIndex] === 'object') {

                        // Ingredient added to recipe
						// Recalculate this row - this will trigger recalculating the recipe nutrients
                        _this.form.rows[evt.newIndex].recalculate = true;

                        // If this is set to true after the now nutrients are updated it will trigger a recipe
						// nutrition update
                        _this.form.rows[evt.newIndex].recalculateRecipeNutrition = true;

                    } else {
                        // Ingredient returned to pantry
						// No need to recalculate this row - just recalculate the recipe nutrients
                        _this.recalculateNutrition = true;
                    }

				});

			},
			fetchIngredientDetails(row) {

                return new Promise(function(resolve, reject){

                    if(typeof row === 'undefined') {
                        // This row doesn't even have an ingredient. Get outa here.
                        resolve();
                        return;
                    }
                    let ingredient = row.ingredient;

                    if(typeof ingredient.weight_one !== 'undefined') {
                        // They have already been fetched. Nothing to do.
                        resolve();
                        return;
                    }

					loading(true);
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
            setIngredientNutrients(item) {
                return new Promise(function(resolve, reject){

                    if(typeof item === 'undefined') {
                        // This item doesn't even have an ingredient. Get outa here.
                        resolve();
                        return;
                    }
                    let ingredient = item.ingredient;

                    if(ingredient.nutrients.length) {
                        // They have already been fetched. Nothing to do.
                        resolve();
                        return;
                    }

                    document.getElementById('overlay').classList.add('loading');

                    get('/api/ingredient/' + ingredient.id + '/attributes')
                        .then((res) => {
                            ingredient.nutrients = res.data.attributes;
                            document.getElementById('overlay').classList.remove('loading');
                            resolve();
                        })
                        .catch(function (error) {
                            document.getElementById('overlay').classList.remove('loading');
                            console.log(error);
                            reject();
                        });
                });
			},
			rowify(ingredients) {
			    let rows = [];
                for (let i = 0; i < ingredients.length; i++) {
                    rows.push({ingredient: ingredients[i]});
                }
                return rows;
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

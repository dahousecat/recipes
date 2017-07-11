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

						<div v-for="(row, index) in form.rows" class="ingredient_row">

							<div class="grabber ingredient_row__handle"></div>

							<!--name-->
							<div class="ingredient_row__name">
								{{row.ingredient.name}}
							</div>

							<!--amount-->
							<div class="ingredient_row__amount">
								<input type="text" class="ingredient_row__control"
									   v-model="row.value"
									   @change="recalculateNutrition = true"
									   :class="[error[`rows.${index}.amount`] ? 'error__bg' : '']">
							</div>

							<!--unit-->
							<div class="ingredient_row__unit">
								<select v-model="row.unit_id" @change="recalculateNutrition=true" class="ingredient_row__control">
									<option v-for="(unit, index) in row.ingredient.units" :value="unit.id">{{unit.name}}</option>
								</select>
							</div>

							<!--remove-->
							<div class="ingredient_row__remove">
								<button @click="removeIngredient(index)" class="ingredient_row__remove_button">&times;</button>
							</div>
						</div>
					</draggable>

				</div>
			</div>

			<!-- Pantry -->
			<div class="recipe__panel">
				<div class="recipe__pantry_inner">
					<h3 class="recipe__sub_title">Pantry</h3>

					<div class="form__group">
						<label>Search</label>
						<input type="text" class="form__control" v-model="ingredient">
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
    import { loading } from '../../helpers/misc';

	Vue.use(draggable);

	export default {
		components: {
			ImageUpload,
			draggable,
            Nutrients,
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
			}
		},

		// Run on initialization
		created() {
			if(this.$route.meta.mode === 'edit') {
				this.initializeURL = `/api/recipes/${this.$route.params.id}/edit`;
				this.storeURL = `/api/recipes/${this.$route.params.id}?_method=PUT`;
				this.action = 'Update';
			}
			get(this.initializeURL)
				.then((res) => {
					Vue.set(this.$data, 'form', res.data.form);
					Vue.set(this.$data, 'units', res.data.units);
					Vue.set(this.$data, 'attributeTypes', res.data.attributeTypes);

					// Ingredients can turn into rows when they are dragged there.
					// Because of this we need to put the ingredient properties in their own namespace.
					// Whilst we are about it lets set some default values for each row
					let items = []; // I will call a row / ingredient and "item"
					for(let i = 0; i < res.data.ingredients.length; i++) {
					    let ingredient = res.data.ingredients[i];

					    // Set an empty array here so we can fill it when needed
					    ingredient.nutrients = [];

					    // If unit_id is not set and there is only one unit available set it as a default
						if(ingredient.default_unit_id === null && ingredient.units.length === 1) {
                            ingredient.default_unit_id = ingredient.units[0].unit_id;
						}

					    // We need a unit_id AND unit as that is the selects model. When it's updated the unit is
						// automatically updated.
					    items.push({
							ingredient: ingredient,
							unit_id: ingredient.default_unit_id,
							unit: getUnit(ingredient.default_unit_id, this.units),
							value: 1,
						});
					}
                    Vue.set(this.$data, 'ingredients', items);

				});

		},
		methods: {
			dragStart(evt){

				this.isDragging = true;
				// let item = this.ingredients[evt.oldIndex];

			},
			dragEnd(evt){

				this.isDragging = false;

				let item = this.editIngredient.length === 1 ? this.editIngredient[0] :  this.form.rows[evt.newIndex];
				let _this = this;

				// Make sure the nutrients for this ingredient are set
				this.fetchIngredientDetails(item).then(function(){

                    if(_this.editIngredient.length === 1) {
                        // Ingredient dropped on the edit ingredient zone
                        _this.showIngredientModal = true;

                    } else if(typeof _this.form.rows[evt.newIndex] === 'Object') {
                        // Ingredient added to recipe
                        _this.recalculateNutrition = true;
                    } else {
                        // Ingredient returned to pantry
                        _this.recalculateNutrition = true;
                    }

				});

			},
			fetchIngredientDetails(item) {

			    let _this = this;

                return new Promise(function(resolve, reject){

                    if(typeof item === 'undefined') {
                        // This item doesn't even have an ingredient. Get outa here.
                        resolve();
                        return;
                    }
                    let ingredient = item.ingredient;

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
				let data = {
				    name: this.form.name,
				    description: this.form.description,
				    image: this.form.image,
				    directions: this.form.directions,
                    rows: [],
				};

                for (let i = 0; i < this.form.rows.length; i++) {
                    let row = this.form.rows[i];
                    data.rows.push({
						id: null,
						ingredient_id: row.ingredient_id,
						delta: i,
						unit_id: row.unit_id,
						unit_value: row.amount,
					});
                }

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
		}
	}
</script>

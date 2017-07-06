<template>
	<div class="recipe__show">
		<div class="recipe__header">
			<h3>{{action}} Recipe</h3>
			<div>
				<button class="btn btn__primary" @click="save" :disabled="isProcessing">Save</button>
				<button class="btn" @click="$router.back()" :disabled="isProcessing">Cancel</button>
			</div>
		</div>
		<div class="recipe__row">
			<div class="recipe__image">
				<div class="recipe__box">
					<image-upload v-model="form.image"></image-upload>
					<small class="error__control" v-if="error.image">{{error.image[0]}}</small>
				</div>
			</div>
			<div class="recipe__details">
				<div class="recipe__details_inner">
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
		</div>
		<div class="recipe__row">
			<div class="recipe__ingredients">
				<div class="recipe__box">
					<h3 class="recipe__sub_title">Ingredients</h3>

					<draggable v-model="form.rows"
							   :class="{ 'drop-zone': this.isDragging, 'recipe__rows--empty': !form.rows.length }"
							   :options='{group:"recipe", handle:".row__handle", filter: ".empty-message"}'
							   @start="dragStart" @end="dragEnd"
							   class="recipe__rows">

						<div v-for="(row, index) in form.rows" class="recipe__ingredient row">

							<div class="grabber row__handle"></div>

							<div class="row__segment">
								{{row.ingredient.name}}
							</div>
							<div class="row__segment">
								<input type="text" class="form__control row__amount"
									   v-model="row.value"
									   @change="updateNutrition()"
									   :class="[error[`rows.${index}.amount`] ? 'error__bg' : '']">
							</div>
							<div class="row__segment">
								<select v-model="row.unit_id" @change="recalculateNutrition=true" class="form__control row__unit">
									<option v-for="(unit, index) in row.ingredient.units" :value="unit.unit_id">{{unit.name}}</option>
								</select>
							</div>

							<!--<div class="row__segment">-->
								<!--<button @click="remove('rows', index)" class="btn btn__danger">&times;</button>								-->
							<!--</div>-->
						</div>
					</draggable>

				</div>
			</div>


			<div class="recipe__pantry">
				<div class="recipe__pantry_inner">
					<h3 class="recipe__sub_title">Pantry</h3>

					<div class="form__group">
						<label>Search</label>
						<input type="text" class="form__control" v-model="ingredient">
					</div>

					<draggable v-model="ingredients" :options='{group:"recipe"}' @start="dragStart" @end="dragEnd">
						<div v-for="item in ingredients" class="recipe__pantry__ingredient grabber">
							{{item.ingredient.name}}
						</div>
					</draggable>

				</div>
			</div>

		</div>
		<div class="recipe__row">

			<nutrients
					@nutritionUpdated="recalculateNutrition=false"
					:rows="form.rows"
					:units="units"
					:recalculate="recalculateNutrition"
					class="recipe__nutrition"></nutrients>

			<div class="recipe__directions">
				<div class="recipe__directions_inner">
					<h3 class="recipe__sub_title">Directions</h3>
					<div v-for="(direction, index) in form.directions" class="recipe__form">
						<textarea class="form__control form__margin" v-model="direction.description"
								  :class="[error[`directions.${index}.description`] ? 'error__bg' : '']"
						></textarea>
						<button @click="remove('directions', index)" class="btn btn__danger">&times;</button>
					</div>
					<button @click="addDirection" class="btn">Add Direction</button>
				</div>
			</div>
		</div>

		<draggable class="edit-ingredient-bar drop-zone"
				   :options='{
					    group: {
							name: "recipe",
						  },
					  	filter: ".message"
					}'
				   :class="{ 'active': this.isDragging }"
					v-model="editIngredient">
			<div class="inner">
				<div class="message">Edit ingredient</div>
			</div>
		</draggable>

		<ingredient-form
				:ingredient="editIngredient[0]"
				:attributeTypes="attributeTypes"
				:units="this.units"
				v-if="showIngredientModal"
				@close="ingredientSaved()">
		</ingredient-form>

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
	import ingredientForm from '../Ingredient/IngredientForm.vue';
	import Nutrients from '../../components/Nutrients.vue';

	Vue.use(draggable);

	export default {
		components: {
			ImageUpload,
			draggable,
            ingredientForm,
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
				this.setIngredientNutrients(item).then(function(){

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
			setUnitsArray(rowIngredient) {
                rowIngredient.units = [];
				for (let i = 0; i < rowIngredient.ingredient.relationships.units.data.length; i++) {
					let id = rowIngredient.ingredient.relationships.units.data[i].id;
                    rowIngredient.units.push({
						id: id,
						name: this.getUnitName(id),
						unitType: this.getUnitType(id),
					});
				}
			},
			setNutrients(ingredient) {
                return new Promise(function(resolve, reject){
                    if(typeof ingredient.nutrients === 'undefined') {
                        // Load the attributes for this ingredient
                        get('/api/ingredient/' + ingredient.ingredient.id + '/attributes')
                            .then((res) => {
                                let attributes = res.data.data;
                                ingredient.nutrients = {};
                                for (let i = 0; i < attributes.length; i++) {
                                    let attribute = attributes[i];
                                    ingredient.nutrients[attribute.attributes.type_safe_name] = {
                                        id: attribute.id,
                                        value: attribute.attributes.value,
                                        type_id: attribute.attributes.type_id,
                                        type_name: attribute.attributes.type_name,
									};
                                }
								resolve();
                            })
                            .catch(function (error) {
                                console.log(error);
                                reject();
                            });
                    } else {
                        resolve();
					}
                });
			},
			fetchAttributeTypes() {
			    let _this = this;
                return new Promise(function(resolve, reject){
                    if(_this.attributeTypes.length) {
                        resolve()
                    } else {
                        // Fetch all possible attribute types
                        get('/api/attribute-types')
                            .then((res) => {
                                Vue.set(_this.$data, 'attributeTypes', res.data.data);
                                resolve();
                            })
                            .catch(function (error) {
                                console.log(error);
                                reject();
                            });
                    }
                });
			},
			getUnitName(id) {
				let unit = this.getUnit(id);
				let name = unit.attributes.name;
				return name === 'quantity' ? 'whole' : name;
			},
			getUnitType(id) {
				let unit = this.getUnit(id);
				return unit.attributes.unitType;
			},
			updateNutrition() {

				this.nutrients = {};
				this.totalWeight = 0;

				// Loop rows
				for (let i = 0; i < this.form.rows.length; i++) {

					let row = this.form.rows[i];
					this.setRowWeight(row);
					this.totalWeight += parseInt(row.weight);

					// Loop this rows ingredients attributes
                    for (var safe_name in row.nutrients) {
                        if (row.nutrients.hasOwnProperty(safe_name)) {
                            let attribute = row.nutrients[safe_name];

                            // Nutrients value is per 100g so divide by 100 before multiplying by row weight
//                            let amountInRow = (attribute.value / 100) * row.weight;

                            if(typeof this.nutrients[safe_name] === 'undefined') {
                                this.nutrients[safe_name] = {
                                    per_100_g: attribute.value,
                                    name: attribute.type_name,
                                };
                            } else {
                                this.nutrients[safe_name].per_100_g += attribute.value;
                            }
                        }
                    }
				}

				// Now we have the total of each nutrition per 100g.

				// Divide by serving size
				for (let nutritionType in this.nutrients) {
					if (this.nutrients.hasOwnProperty(nutritionType)) {
						let nutrition = this.nutrients[nutritionType];

//						let portionDivisor = this.amountPer === 'recipe' ? 1 :  parseInt(this.amountPer) / this.totalWeight;
//						nutrition.displayValue = nutrition.value * portionDivisor;

						if(this.amountPer === 'recipe') {
                            nutrition.exactValue = (nutrition.per_100_g / 100) * this.totalWeight;
						} else {
                            nutrition.exactValue = (nutrition.per_100_g / 100) * parseInt(this.amountPer);
						}

						if(nutritionType === 'energy') {
							// Convert to display unit
							let conversionFactor = this.energyUnit === 'calorie' ? 1 : this.conversions.caloriesInKj;
							nutrition.displayValue = nutrition.displayValue / conversionFactor;
						}

						nutrition.displayValue = formatNumber(nutrition.exactValue);

					}
				}

                this.nutritionUpdating = false;

			},
//            recalculateEnergy() {
//                let energy = convertEnergyUnit(this.nutrients.energy.exactValue, this.energyUnit);
//                this.nutrients.energy.exactValue = energy;
//                this.nutrients.energy.displayValue = formatNumber(energy);
//			},
			/*setRowWeight(row) {

			    let rowUnit = this.getUnit(row.unit);

			    console.log(rowUnit, 'rowUnit');

			    switch(rowUnit.attributes.unitType) {
					case 'weight':
						// Convert to grams
						let grams;
						if(rowUnit.attributes.name === 'grams') {
                            grams = row.amount;
                        } else {
                            grams = row.amount * rowUnit.attributes.gram;
                        }
                        row.weight = grams;
					    break;
					case 'length':

					    break;
					case 'volume':

					    break;
					case 'quantity':
                        if(typeof row.attributes.weight !== 'undefined') {
                            // The attribute weight is the weight of one whole ingredient

                            row.weight = row.attributes.weight * row.amount;
                        } else {
                            console.log('ERROR: No weight for ' + row.ingredient);
                            row.weight = 0;
                        }
					    break;
				}

			},*/
			save() {
//				const form = toMulipartedForm(this.form, this.$route.meta.mode);

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
            ingredientSaved() {
                this.showIngredientModal = false;

                // put ingredient back into pantry
                this.ingredients.push(this.editIngredient[0]);

                // Remove this ingredient from the edit list.
                this.editIngredient = [];
			},
		}
	}
</script>

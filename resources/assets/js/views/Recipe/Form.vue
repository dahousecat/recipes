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
								{{row.ingredient}}
							</div>
							<div class="row__segment">
								<input type="text" class="form__control row__amount"
									   v-model="row.amount"
									   @change="updateNutrition()"
									   :class="[error[`rows.${index}.amount`] ? 'error__bg' : '']">
							</div>
							<div class="row__segment">
								<select v-model="row.unit" @change="updateNutrition()" class="form__control row__unit">
									<option v-for="(unit, index) in row.units" :value="unit.id">{{unit.name}}</option>
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
						<div v-for="ingredient in ingredients" class="recipe__pantry__ingredient grabber">
							{{ingredient.attributes.name}}
						</div>
					</draggable>

				</div>
			</div>

		</div>
		<div class="recipe__row">

			<div class="recipe__nutrition" :class="[nutritionUpdating ? 'loading' : '']">
				<div class="recipe__box">
					<h3 class="recipe__sub_title">Nutrition</h3>

					<div class="recipe__nutrition__inner">
						<div class="form__group nutrition-row">
							<div class="nutrition-row__unit">
								Amount per
							</div>
							<div class="nutrition-row__value">
								<select v-model="amountPer" @change="updateNutrition()" class="form__control">
									<option v-for="(option, index) in amountPerOptions" :value="option.value">{{option.name}}</option>
								</select>
							</div>
						</div>

						<div class="form__group nutrition-row">
							<div class="nutrition-row__unit">
								<select v-model="energyUnit" @change="recalculateEnergy()" class="form__control">
									<option v-for="(option, index) in energyUnitOptions" :value="option.value">{{option.name}}</option>
								</select>
							</div>
							<div class="nutrition-row__value">
								{{ nutritions.energy.displayValue }}
							</div>
						</div>

						<div class="form__group nutrition-row">
							<div class="nutrition-row__unit">
								Protein
							</div>
							<div class="nutrition-row__value">
								{{ nutritions.protein.displayValue }}
							</div>
						</div>

						<div class="form__group nutrition-row">
							<div class="nutrition-row__unit">
								Sugar
							</div>
							<div class="nutrition-row__value">
								{{ nutritions.sugar.displayValue }}
							</div>
						</div>
					</div>

				</div>
			</div>

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
				:attribute_types="attribute_types"
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
	import { convertEnergyUnit } from '../../helpers/convert';
	import { toMulipartedForm } from '../../helpers/form';
	import ImageUpload from '../../components/ImageUpload.vue';
	import draggable from 'vuedraggable';
	import ingredientForm from '../Ingredient/IngredientForm.vue'

	Vue.use(draggable);

	export default {
		components: {
			ImageUpload,
			draggable,
            ingredientForm,
		},
		data() {
			return {
				form: {
					rows: [],
					directions: []
				},
				ingredients: [],
				nutritions: {
					energy: {
						displayValue: '0',
					},
					protein: {
						displayValue: '0',
					},
					sugar: {
						displayValue: '0',
					},
				},
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
				ingredientsURL: `/api/ingredients`,
				unitsURL: `/api/units`,
				action: 'Create',
				ingredient: '',
				isDragging: false,
				nutritionUpdating: false,
				editIngredient: [],
				showIngredientModal: false,
                attribute_types: [],
			}
		},
		watch: {
			// For ingredient filter
			ingredient: function(str){
				let url = str.length > 1 ? '/api/ingredients/search/' + str : '/api/ingredients';
				get(url)
						.then((res) => {
							Vue.set(this.$data, 'ingredients', res.data.ingredients);
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
				});

			get(this.ingredientsURL)
					.then((res) => {
				Vue.set(this.$data, 'ingredients', res.data.data);
			});

			get(this.unitsURL)
					.then((res) => {
						Vue.set(this.$data, 'units', res.data.data);
					});
		},
		methods: {
			dragStart(evt){

				this.isDragging = true;
				let ingredient = this.ingredients[evt.oldIndex];

				// Set ingredient for the row
				if(typeof ingredient.ingredient === 'undefined') {
					ingredient.ingredient = ingredient.attributes.name;
				}

				// Set default amount for the row
				if(typeof ingredient.amount === 'undefined') {
					ingredient.amount = 1;
				}

				// Set default type of unit
				if(typeof ingredient.unit === 'undefined') {
					ingredient.unit = ingredient.attributes.default_unit_id;
				}

				// Set the ingredients unit array
				if(typeof ingredient.units === 'undefined') {
					this.setUnitsArray(ingredient);
				}

			},
			dragEnd(evt){

				this.isDragging = false;

				if(this.editIngredient.length === 1) {
				    // Ingredient dropped on the edit ingredient zone

                    // Only show the modal once the ingredients attributes array has been built and we've fetched the
					// array of unit types
                    let promises = [this.setAttributesArray(this.editIngredient[0]), this.fetchAttributeTypes()];
                    let _this = this;
                    document.body.classList.add('loading');
                    Promise.all(promises).then(function(){
                        _this.showIngredientModal = true;
                        document.body.classList.remove('loading');
                    });

				} else {
					// Ingredient added to the recipe
                    let row = this.form.rows[evt.newIndex];

                    // If there is no row then the ingredient was dropped back in the pantry
                    if(typeof row !== 'undefined') {
                        // Set attributes array (ingredient attributes, not json api attributes)
                        this.nutritionUpdating = true;
                        let _this = this;
                        this.setAttributesArray(row).then(function(){
                            _this.updateNutrition();
						});
                    }
				}

			},
			setUnitsArray(ingredient) {
				ingredient.units = [];
				for (let i = 0; i < ingredient.relationships.units.data.length; i++) {
					let id = ingredient.relationships.units.data[i].id;
					ingredient.units.push({
						id: id,
						name: this.getUnitName(id),
						unitType: this.getUnitType(id),
					});
				}
			},
			setAttributesArray(ingredient) {
                let _this = this;
                return new Promise(function(resolve, reject){
                    if(typeof ingredient.ingredientAttributes === 'undefined') {
                        // Load the attributes for this ingredient
                        get('/api/ingredient/' + ingredient.id + '/attributes')
                            .then((res) => {
                                let attributes = res.data.data;
                                // Attach the name of the unit from the units array
                                for (let i = 0; i < attributes.length; i++) {
                                    let unit = _this.getUnit(attributes[i].relationships.unit.data.id);
                                    attributes[i].attributes.unit = unit.attributes.name;
                                }
                                ingredient.ingredientAttributes = attributes;
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
                    if(_this.attribute_types.length) {
                        resolve()
                    } else {
                        // Fetch all possible attribute types
                        get('/api/attribute-types')
                            .then((res) => {
                                Vue.set(_this.$data, 'attribute_types', res.data.data);
                                resolve();
                            })
                            .catch(function (error) {
                                console.log(error);
                                reject();
                            });
                    }
                });
			},
			getUnit(id) {
			    id = parseInt(id);
				for (let i = 0; i < this.units.length; i++) {
					if(parseInt(this.units[i].id) === id) {
						return this.units[i];
					}
				}
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
				// Yes, nutritions is not a word but I can't handle using the word attribute any more!
				let nutritions = {};
				let totalWeight = 0;

				for (let i = 0; i < this.form.rows.length; i++) {

					let row = this.form.rows[i];
					this.setRowWeight(row);
					totalWeight += parseInt(row.weight);

					for (let x = 0; x < row.ingredientAttributes.length; x++) {
						let attributes = row.ingredientAttributes[x].attributes;
						let nutritionType = attributes.attributeType.name;
						let nutritionUnit = attributes.attributeType.unit;
						let value = attributes.value;
						let unit_value = attributes.unit;

						if(typeof nutritions[nutritionType] === 'undefined') {
							nutritions[nutritionType] = {
								nutritionUnit: nutritionUnit,
								value: value * row.weight,
								unit_value: unit_value,
							};
						} else {
							nutritions[nutritionType].value += (value  * row.weight);
						}
					}
				}

				// Now we have the total of each nutrition for the recipe.
				this.nutritions = nutritions;

				// Divide by serving size
				for (let nutritionType in this.nutritions) {
					if (this.nutritions.hasOwnProperty(nutritionType)) {
						let nutrition = this.nutritions[nutritionType];

						let portionDivisor = this.amountPer === 'recipe' ? 1 :  parseInt(this.amountPer) / totalWeight;

						nutrition.displayValue = nutrition.value * portionDivisor;

						if(nutritionType === 'energy') {
							// Convert to display unit
							let conversionFactor = this.energyUnit === 'calorie' ? this.conversions.caloriesInKj : 1;
							nutrition.displayValue = nutrition.displayValue * conversionFactor;
						}

                        nutrition.exactValue = nutrition.displayValue;
						nutrition.displayValue = this.formatNumber(nutrition.displayValue);

					}
				}

                this.nutritionUpdating = false;

			},
            recalculateEnergy() {
                let energy = convertEnergyUnit(this.nutritions.energy.exactValue, this.energyUnit);
                this.nutritions.energy.exactValue = energy;
                this.nutritions.energy.displayValue = this.formatNumber(energy);
			},
			setRowWeight(row) {

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

			},
			formatNumber(number) {
				let dp = number < 1 ? 1 : 0;
				return number.toFixed(dp);
			},
			save() {
				const form = toMulipartedForm(this.form, this.$route.meta.mode);
				post(this.storeURL, form)
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

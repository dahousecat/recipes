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

					<draggable v-model="form.rows" v-bind:class="{ 'drop-zone': this.isDragging, 'recipe__rows--empty': !form.rows.length }"
							   :options='{group:"recipe", handle:".row__handle", filter: ".empty-message"}'
							   @start="dragStart" @end="dragEnd"
							   class="recipe__rows">

						<!--<div v-if="!form.rows.length" class="empty-message">Drag some ingredients here to start your recipe.</div>-->

						<div v-for="(row, index) in form.rows" class="recipe__form row">

							<i class="fa fa-arrows row__handle"></i>

							<div class="row__segment">
								{{row.ingredient}}
							</div>
							<div class="row__segment">
								<input type="text" class="form__control row__amount" v-model="row.quantity"
									   :class="[error[`rows.${index}.amount`] ? 'error__bg' : '']">
							</div>
							<div class="row__segment">
								<select v-model="row.unit" class="form__control row__unit">
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

					<draggable v-model="ingredients" :options='{"group":"recipe"}' @start="dragStart" @end="dragEnd">
						<div v-for="ingredient in ingredients" class="recipe__ingredient">{{ingredient.attributes.name}}</div>
					</draggable>

				</div>
			</div>

		</div>
		<div class="recipe__row">

			<div class="recipe__nutrition">
				<div class="recipe__box">
					<h3 class="recipe__sub_title">Nutrition</h3>

					<div class="form__group nutrition-row">
						<div class="nutrition-row__unit">
							Amount per
						</div>
						<div class="nutrition-row__value">
							<select v-model="amountPer" v-on:change="recalculateEnergy()" class="form__control">
								<option v-for="(option, index) in amountPerOptions" :value="option.value">{{option.name}}</option>
							</select>
						</div>
					</div>


					<div class="form__group nutrition-row">
						<div class="nutrition-row__unit">
							<select v-model="energyUnit" v-on:change="recalculateEnergy()" class="form__control">
								<option v-for="(option, index) in energyUnitOptions" :value="option.value">{{option.name}}</option>
							</select>
						</div>
						<div class="nutrition-row__value">
							{{ nutritions.energy.displayValue }}
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
	</div>
</template>
<script type="text/javascript">
	import Vue from 'vue'
	import Flash from '../../helpers/flash'
	import { get, post } from '../../helpers/api'
	import { toMulipartedForm } from '../../helpers/form'
	import ImageUpload from '../../components/ImageUpload.vue'
	import draggable from 'vuedraggable'

	Vue.use(draggable);

	export default {
		components: {
			ImageUpload,
			draggable,
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
				error: {},
				isProcessing: false,
				initializeURL: `/api/recipes/create`,
				storeURL: `/api/recipes`,
				ingredientsURL: `/api/ingredients`,
				unitsURL: `/api/units`,
				action: 'Create',
				ingredient: '',
				isDragging: false
			}
		},
		watch: {
			// For ingredient filter
			ingredient: function(str){
				var url = str.length > 1 ? '/api/ingredients/search/' + str : '/api/ingredients';
				get(url)
						.then((res) => {
							Vue.set(this.$data, 'ingredients', res.data.ingredients);
						})
			}
		},

		// Run on initialization
		created() {
			if(this.$route.meta.mode === 'edit') {
				this.initializeURL = `/api/recipes/${this.$route.params.id}/edit`
				this.storeURL = `/api/recipes/${this.$route.params.id}?_method=PUT`
				this.action = 'Update'
			}
			get(this.initializeURL)
				.then((res) => {
					Vue.set(this.$data, 'form', res.data.form);
				})

			get(this.ingredientsURL)
					.then((res) => {
				Vue.set(this.$data, 'ingredients', res.data.data);
			})

			get(this.unitsURL)
					.then((res) => {
						Vue.set(this.$data, 'units', res.data.data);
					})
		},
		methods: {
			dragStart(evt){
				this.isDragging = true;
				var ingredient = this.ingredients[evt.oldIndex];

				// Set ingredient for the row
				if(typeof ingredient.ingredient == 'undefined') {
					ingredient.ingredient = ingredient.attributes.name;
				}

				// Set default quantity for the row
				if(typeof ingredient.quantity == 'undefined') {
					ingredient.quantity = 1;
				}

				// Set default type of unit
				if(typeof ingredient.unit == 'undefined') {
					ingredient.unit = ingredient.attributes.default_unit_id;
				}

				// Set the ingredients unit array
				if(typeof ingredient.units == 'undefined') {
					this.setUnitsArray(ingredient);
				}

			},
			dragEnd(evt){

				this.isDragging = false;
				var row = this.form.rows[evt.newIndex];

				// Set attributes array (ingredient attributes, not json api attributes)
				this.setAttributesArray(row, this.updateNutrition);

			},
			setUnitsArray(ingredient) {
				ingredient.units = [];
				for (var i = 0; i < ingredient.relationships.units.data.length; i++) {
					var id = ingredient.relationships.units.data[i].id;
					ingredient.units.push({
						id: id,
						name: this.getUnitName(id),
					});
				}
			},
			setAttributesArray(ingredient, callback) {

				if(typeof ingredient.ingredientAttributes == 'undefined') {
					// Load the attributes for this ingredient
					get('/api/ingredient/' + ingredient.id + '/attributes')
							.then((res) => {
								var attributes = res.data.data;
								// Attach the name of the unit from the units array
								for (var i = 0; i < attributes.length; i++) {
									var unit = this.getUnit(attributes[i].relationships.unit.data.id);
									attributes[i].attributes.unit = unit.attributes.name;
								}
								ingredient.ingredientAttributes = attributes;

								if(typeof callback == 'function') {
									callback();
								}
							})
				} else {
					if(typeof callback == 'function') {
						callback();
					}
				}

			},
			getUnit(id) {
				for (var i = 0; i < this.units.length; i++) {
					if(this.units[i].id == id) {
						return this.units[i];
					}
				}
			},
			getUnitName(id) {
				var unit = this.getUnit(id);
				var name = unit.attributes.name;
				return name == 'quantity' ? 'whole' : name;
			},
			updateNutrition() {
				// Yes, nutritions is not a word but I can't handle using the word attribute any more!
				var nutritions = {};

				for (var i = 0; i < this.form.rows.length; i++) {
					var row = this.form.rows[i];



					for (var x = 0; x < row.ingredientAttributes.length; x++) {
						var attribute = row.ingredientAttributes[x];
						var nutritionType = attribute.attributes.attributeType.name;
						var nutritionUnit = attribute.attributes.attributeType.unit;
						var value = attribute.attributes.value;
						var unit_value = attribute.attributes.unit;

						if(typeof nutritions[nutritionType] == 'undefined') {
							nutritions[nutritionType] = {
								nutritionUnit: nutritionUnit,
								value: value,
								unit_value: unit_value,
							};
						} else {
							nutritions[nutritionType].value += value;
						}
					}
				}

				this.nutritions = nutritions;

				this.recalculateEnergy();

			},
			recalculateEnergy() {
				if(typeof this.nutritions.energy != 'undefined') {
					var energy = parseFloat(this.nutritions.energy.value);
//					var amountPer = parseFloat(this.amountPer);
					var conversionFactor = this.energyUnit == 'calorie' ? 0.239006 : 1;
					this.nutritions.energy.displayValue = this.formatNumber(energy * amountPer * conversionFactor);
				}
			},
			formatNumber(number) {
				var dp = number < 1 ? 1 : 0;
				return number.toFixed(dp);
			},
			save() {
				const form = toMulipartedForm(this.form, this.$route.meta.mode)
				post(this.storeURL, form)
				    .then((res) => {
				        if(res.data.saved) {
				            Flash.setSuccess(res.data.message)
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
			}
		}
	}
</script>
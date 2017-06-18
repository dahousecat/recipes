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
					<div v-for="(row, index) in form.rows" class="recipe__form">

						<!--<select class="form__control"></select>-->

						<input type="text" class="form__control" v-model="row.ingredient"
							:class="[error[`rows.${index}.name`] ? 'error__bg' : '']">

						<input type="text" class="form__control form__unit" v-model="row.unit"
							:class="[error[`rows.${index}.unit`] ? 'error__bg' : '']">

						<input type="text" class="form__control form__amount" v-model="row.amount"
							   :class="[error[`rows.${index}.amount`] ? 'error__bg' : '']">

						<button @click="remove('rows', index)" class="btn btn__danger">&times;</button>

					</div>
					<button @click="addRow" class="btn">Add Ingredient</button>
				</div>
			</div>


			<div class="recipe__pantry">
				<div class="recipe__pantry_inner">
					<h3 class="recipe__sub_title">Pantry</h3>

					<div class="form__group">
						<label>Search</label>
						<input type="text" class="form__control" v-model="ingredient">
					</div>

					<draggable v-model="ingredients" :options="{group:'people'}" @start="drag=true" @end="drag=false">
						<div v-for="ingredient in ingredients" class="recipe__ingredient">{{ingredient.name}}</div>
					</draggable>

					<!--<div v-for="(ingredient, index) in ingredients" class="recipe__ingredient">-->
						<!--<div>{{ ingredient.name }}</div>-->
					<!--</div>-->
				</div>
			</div>

		</div>
		<div class="recipe__row">
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
				error: {},
				isProcessing: false,
				initializeURL: `/api/recipes/create`,
				storeURL: `/api/recipes`,
				action: 'Create',
				ingredient: ''
			}
		},
		watch: {
			ingredient: function(str){
				var url = str.length > 1 ? '/api/ingredients/search/' + str : '/api/ingredients';
				get(url)
						.then((res) => {
							Vue.set(this.$data, 'ingredients', res.data.ingredients);
						})
			}
		},
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

			get('/api/ingredients')
					.then((res) => {
				Vue.set(this.$data, 'ingredients', res.data.ingredients);
			})
		},
		methods: {
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
			addRow() {
				this.form.rows.push({
					amount: '',
					ingredient: '',
					unit: '',
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

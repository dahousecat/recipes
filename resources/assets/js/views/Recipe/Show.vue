<template>
	<div class="recipe">

		<div class="row--m">
			<div class="col-1">

				<div class="panel recipe__header">
					<div class="recipe__header-group">
						<h1 class="recipe__title">{{recipe.name}}</h1>
						<div v-if="recipe.description" class="recipe__description">{{recipe.description}}</div>
						<small>Submitted by: {{recipe.user.name}}</small><br>
						<small>Likes: {{recipe.score.likes}}, dislikes: {{recipe.score.dislikes}}</small>

					</div>

					<div class="recipe__header-group">
						<div v-if="authState.api_token" class="recipe__votes">
							Have your tried this recipe?
							<button class="btn recipe__vote-btn-like"
									:class="recipe.user_score > 0 ? 'recipe__vote-btn-like--active' : ''"
									@click="upVote">I liked it :)</button>
							<button class="btn recipe__vote-btn-dislike"
									:class="recipe.user_score < 0 ? 'recipe__vote-btn-dislike--active' : ''"
									@click="downVote">I didn't like it :(</button>
						</div>

						<div v-if="authState.api_token && authState.user_id === recipe.user_id" class="recipe__actions">
							<router-link :to="`/recipes/${recipe.id}/edit`" class="btn btn-primary">
								Edit
							</router-link>
							<button class="btn btn__danger" @click="remove" :disabled="isRemoving">Delete</button>
						</div>
					</div>

				</div>

			</div>
		</div>

		<div class="row--m">
			<div class="col-1">
				<div class="panel">
					<h3 class="recipe__sub_title">Directions</h3>
					<ul class="recipe__display-list">
						<li v-for="(direction, i) in recipe.directions">
							<p>
								<strong>{{i + 1}}: </strong>
								{{direction.description}}
							</p>
						</li>
					</ul>
				</div>
			</div>
		</div>

		<div class="row--m">

			<div class="col-1">
				<div class="panel">
					<h3 class="recipe__sub_title">Ingredients</h3>
					<ingredient-row
							v-for="(row, index) in recipe.rows"
							:row="row"
							:draggable="false"
							@rowUpdated="rowUpdated(row)"
							@removeIngredient="removeIngredient(index)">
					</ingredient-row>
				</div>
			</div>

			<div class="col-1">
				<div class="panel">
					<!-- Nutrients -->
					<nutrients
							@nutritionUpdated="recalculateNutrition=false"
							:rows="recipe.rows"
							:units="units"
							:recalculate="recalculateNutrition"></nutrients>
				</div>
			</div>

		</div>

	</div>
</template>

<script type="text/javascript">
    import Vue from 'vue';
	import Auth from '../../store/auth'
	import Flash from '../../helpers/flash'
	import { get, del, post } from '../../helpers/api'
    import Nutrients from '../../components/Nutrients.vue';
    import IngredientRow from '../../components/IngredientRow.vue';

	export default {
        components: {
            Nutrients,
            IngredientRow,
        },
		data() {
			return {
				authState: Auth.state,
				isRemoving: false,
				recipe: {
					user: {},
					ingredients: [],
					directions: []
				},
                units: [],
                recalculateNutrition: false,
			}
		},
		created() {
			get(`/api/recipes/${this.$route.params.id}`)
				.then((res) => {

                    if(res.data.recipe.directions.length == 0) {
                        res.data.recipe.directions.push({description: 'Blend all ingredients together.'});
                    }

                    Vue.set(this.$data, 'recipe', res.data.recipe);
                    Vue.set(this.$data, 'units', res.data.units);

                    // Just set data so wait till next tick to update the recipe rows
                    let _this = this;
                    this.$nextTick(function () {
                        for (let i = 0; i < _this.recipe.rows.length; i++) {
                            _this.recipe.rows[i].recalculate = true;
                        }

                        // And then the tick after that to update the recipe nutrients
                        Vue.nextTick(function () {
                            _this.recalculateNutrition = true;
                        });
                    });

				})
		},
		methods: {
			remove() {
				this.isRemoving = false
				del(`/api/recipes/${this.$route.params.id}`)
					.then((res) => {
						if(res.data.deleted) {
							Flash.setSuccess('You have successfully deleted recipe!')
							this.$router.push('/')
						}
					})
			},
            removeIngredient(index) {
                this.recipe.rows.splice(index, 1);
                this.recalculateNutrition = true;
            },
            rowUpdated(row) {
                row.recalculate = false;
                if(row.recalculateRecipeNutrition) {
                    this.recalculateNutrition = true;
                }
            },
            upVote() {
                this.vote(1);
			},
			downVote() {
				this.vote(-1);
			},
			vote(score) {
                post('/api/recipes/' + this.recipe.id + '/vote', {score: score})
                    .then((res) => {
                    	this.recipe.score = res.data.score;
                        this.recipe.user_score = score;
                    })
                    .catch((err) => {

                    })
			}
		}
	}
</script>

<style lang="scss">
	@import "../../../sass/variables/colours";

	.recipe__display-list {
		list-style-type: none;
		padding: 0;
		margin: 0;
	}
	.recipe__display-list-row {
		background-color: darken($color__alto, 10%);
		margin: 0 0 0.6rem 0;
		padding: 0.4rem 1rem;
		border-radius: 0.6rem;
		display: flex;
		max-width: 30rem;
	}
	.recipe__display-list-name {
		flex: 1;
	}
	.recipe__display-list-value {
		margin-right: 1rem;
	}
	.recipe__actions {
		text-align: right;
	}
	.recipe__votes {
		font-size: 1.2rem;
		margin-bottom: 2rem;
	}
	.recipe__vote-btn-like {

	}
	.recipe__vote-btn-like--active {
		background-color: darkgreen;
		color: white;

		&:hover {
			background-color: darken(darkgreen, 10);
		}
	}
	.recipe__vote-btn-dislike {

	}
	.recipe__vote-btn-dislike--active {
		background-color: darkred;
		color: white;

		&:hover {
			background-color: darken(darkred, 10);
		}
	}
</style>

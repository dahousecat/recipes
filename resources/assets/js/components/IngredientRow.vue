<template>
    <div class="ingredient-row">

        <div class="ingredient-row__controls">
            <div class="grabber ingredient-row__handle"></div>

            <!--name-->
            <div class="ingredient-row__name">
                {{row.ingredient.name}}
            </div>

            <!--amount-->
            <div class="ingredient-row__amount">
                <input type="text" class="ingredient-row__control"
                       v-model="row.value"
                       @change="calculateNutrition()">
            </div>

            <!--unit-->
            <div class="ingredient-row__unit">
                <select v-model="row.unit_id" @change="calculateNutrition()" class="ingredient-row__control">
                    <option v-for="(unit, index) in row.ingredient.units" :value="unit.id">{{unit.name}}</option>
                </select>
            </div>

            <!--remove-->
            <div class="ingredient-row__remove">
                <button @click="$emit('removeIngredient')" class="ingredient-row__remove_button">&times;</button>
            </div>
        </div>

        <div class="ingredient-row__nutrients">
            <div class="nutrition-row"
                 v-for="(value, name) in row.nutrients">
                <div class="nutrition-row__unit">
                    {{ name }}
                </div>
                <div class="nutrition-row__value">
                    {{ value }}
                </div>
            </div>
        </div>

    </div>
</template>

<script type="text/javascript">
    export default {
        props: {
            row: {
                type: [Object],
                default: {},
            },
            recalculate: {
                type: [Boolean],
                default: false,
            },
        },
        created() {
//            console.log(JSON.parse(JSON.stringify(this.row.ingredient)), 'row on creation');
        },
        watch: {
            // TODO: Make this bit work
            recalculate: function() {
                this.calculateNutrition();
            }
        },
        methods: {
            calculateNutrition() {

                console.log('calculateNutrition');

                let row = this.row;
                for (let nutrientName in row.ingredient.nutrients) {
                    if (row.ingredient.nutrients.hasOwnProperty(nutrientName)) {

                        let ingredientNutrient = row.ingredient.nutrients[nutrientName];

                        // we know how this row is being measured, so we can work out how much this row weights
                        switch(row.unit.type) {
                            case 'weight':
                                // If not grams then convert to grams
                                row.weight = row.unit.name === 'grams' ? row.value : row.value * row.unit.gram;
                                break;
                            case 'length':
                                row.weight = row.ingredient.weight_one_cm * row.value;
                                break;
                            case 'volume':
                                row.weight = row.ingredient.weight_one_cup * row.value;
                                break;
                            case 'quantity':
                                row.weight = row.ingredient.weight_one * row.value;
                                break;
                        }

                        // Nutrients are stored per 100g so divide by 100 first
                        row.nutrients[nutrientName] = (ingredientNutrient.value / 100) * row.weight;

                    }
                }

                this.$emit('recalculateNutrition');
            },
        }
    }
</script>
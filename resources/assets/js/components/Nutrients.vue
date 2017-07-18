<template>
    <div class="nutrients" :class="[updating ? 'loading' : '']">

        <h3 class="recipe__sub_title">Nutrition</h3>

        <div class="recipe__nutrition__inner">
            <div class="form__group nutrition-row">
                <div class="nutrition-row__unit">
                    Amount per
                </div>
                <div class="nutrition-row__value">
                    <select v-model="amountPer" @change="updatNutrition()" class="form__control">
                        <option v-for="(option, index) in amountPerOptions" :value="option.value">{{option.name}}</option>
                    </select>
                </div>
            </div>

            <div class="nutrition-row" v-if="Object.keys(nutrients).length == 0">
                Add some ingredients to see the recipe nutrients
            </div>

            <div class="nutrition-row" v-if="typeof displayNutrients.energy != 'undefined'">
                <div class="nutrition-row__unit nutrition-row__unit--wide">
                    <select v-model="energyUnit" @change="recalculateEnergy()" class="form__control">
                        <option v-for="(option, index) in energyUnitOptions" :value="option.value">{{option.name}}</option>
                    </select>
                </div>
                <div class="nutrition-row__value">
                    {{ displayNutrients.energy.value }} {{ displayNutrients.energy.unit }}
                </div>
            </div>

            <div class="nutrition-row"
                 v-for="(nutrient, name) in displayNutrients"
                 v-if="name!='energy'">
                <div class="nutrition-row__unit">
                    {{ name }}
                </div>
                <div class="nutrition-row__value">
                    {{ nutrient.value }} {{ nutrient.unit }}
                </div>
            </div>
        </div>

    </div>
</template>
<script type="text/javascript">
    import { convertEnergyUnit, formatNumber, getUnit } from '../helpers/misc';

    export default {
        props: {
            rows: {
                type: [Array],
                default: [],
            },
            units: {
                type: [Array],
                default: [],
            },
            recalculate: {
                type: [Boolean],
                default: false,
            },
        },
        watch: {
            recalculate: function() {
                if(this.recalculate) {
                    this.updatNutrition();
                    this.$emit('nutritionUpdated');
                }
            }
        },
        data() {
            return {
                nutrients: {},
                displayNutrients: {},
                amountPer: 'recipe',
                amountPerOptions: [
                    {'value': 'recipe', 'name': 'Recipe'},
                    {'value': 1, 'name': '1 gram'},
                    {'value': 10, 'name': '10 grams'},
                    {'value': 100, 'name': '100 grams'},
                    {'value': 1000, 'name': '1 kg'},
                ],
                updating: false,
                energyUnit: 'calorie',
                energyUnitOptions: [
                    {
                        'value': 'calorie',
                        'name': 'Calories'
                    },
                    {
                        'value': 'Kj',
                        'name': 'Kilojoule'
                    },
                ],
                conversions: {
                    caloriesInKj: 0.239006,
                },
                recipeWeight: 0,
            }
        },
        methods: {
            updatNutrition() {

                console.log('Calculate recipe nutrition');

                let nutrients = {};
                this.recipeWeight = 0;

                // Loop rows
                for (let i = 0; i < this.rows.length; i++) {
                    let row = this.rows[i];
                    this.recipeWeight += row.weight;

                    // Loop row nutrients
                    for (let nutrientName in row.nutrients) {
                        if (row.nutrients.hasOwnProperty(nutrientName)) {
                            let nutrient = row.nutrients[nutrientName];
                            if(typeof nutrients[nutrientName] === 'undefined') {
                                nutrients[nutrientName] = {
                                    'unit': nutrient.unit,
                                    'value': nutrient.value,
                                };
                            } else {
                                nutrients[nutrientName].value += nutrient.value;
                            }
                        }
                    }
                }

                // Now we have the total nutrients for the whole recipe.

                // Next divide by serving size
                for (let nutrientName in nutrients) {
                    if (nutrients.hasOwnProperty(nutrientName)) {
                        let nutrient = nutrients[nutrientName];

                        if(this.amountPer !== 'recipe') {
                            let nutrientsInOneGram = nutrient.value / this.recipeWeight;
                            nutrient.value = nutrientsInOneGram * parseInt(this.amountPer);
                        }

                        if(nutrientName === 'energy') {
                            // Convert to display unit
                            let conversionFactor = this.energyUnit === 'calorie' ? 1 : this.conversions.caloriesInKj;
                            nutrient.value = nutrient.value / conversionFactor;
                        }
                    }
                }

                this.nutrients = nutrients;

                this.updateDisplayNutrients();

                this.nutritionUpdating = false;

            },
            updateDisplayNutrients() {
                this.displayNutrients = {};
                for (let nutrientName in this.nutrients) {
                    if (this.nutrients.hasOwnProperty(nutrientName)) {
                        let nutrient = this.nutrients[nutrientName];
                        this.displayNutrients[nutrientName] = {
                            'value': formatNumber(nutrient.value),
                            'unit': nutrient.unit,
                        };
                    }
                }
            },
            setRowWeight(row) {

                switch(row.unit.type) {
                    case 'weight':
                        // Convert to grams
                        let grams;
                        if(row.unit.name === 'grams') {
                            grams = row.value;
                        } else {
                            grams = row.value * row.unit.gram;
                        }
                        row.weight = grams;
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

            },
            recalculateEnergy() {
                let value;
                if(this.energyUnit === 'calorie') {
                    value = this.nutrients.energy.value;
                } else {
                    const caloriesInKj = 0.239006;
                    value = this.nutrients.energy.value / caloriesInKj;
                }
                this.displayNutrients.energy.value = formatNumber(value);
            },
        }
    }
</script>

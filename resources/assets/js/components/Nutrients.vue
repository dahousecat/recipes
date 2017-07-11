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

            <div class="nutrition-row" v-if="typeof nutrients.energy != 'undefined'">
                <div class="nutrition-row__unit nutrition-row__unit--wide">
                    <select v-model="energyUnit" @change="recalculateEnergy()" class="form__control">
                        <option v-for="(option, index) in energyUnitOptions" :value="option.value">{{option.name}}</option>
                    </select>
                </div>
                <div class="nutrition-row__value">
                    {{ nutrients.energy.displayValue }}
                </div>
            </div>

            <div class="nutrition-row"
                 v-for="(nutrient, safe_name, index) in nutrients"
                 v-if="safe_name!='energy'">
                <div class="nutrition-row__unit">
                    {{ nutrient.name }}
                </div>
                <div class="nutrition-row__value">
                    {{ nutrient.displayValue }}
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
        data() {
            return {
                nutrients: {},
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
            }
        },
        watch: {
            recalculate: function() {
                if(this.recalculate) {
                    this.updatNutrition();
                    this.$emit('nutritionUpdated');
                }
            }
        },
        methods: {
            updatNutrition() {

                console.log('updatNutrition');

                let nutrients = {};
                let totalWeight = 0;

                // Loop rows
                for (let i = 0; i < this.rows.length; i++) {
                    let row = this.rows[i];

                    console.log(row, 'row');

                    if(typeof row.unit === 'undefined' || row.unit_id !== row.unit.id) {
                        // The rows unit id has changed, reload the unit.
                        row.unit = getUnit(row.unit_id, this.units);
                    }

                    this.setRowWeight(row);
                    totalWeight += parseInt(row.weight);

                    for (let nutrientName in row.ingredient.nutrients) {
                        if (row.ingredient.nutrients.hasOwnProperty(nutrientName)) {
                            let nutrient = row.ingredient.nutrients[nutrientName];

                            if(typeof nutrients[nutrientName] === 'undefined') {
                                nutrients[nutrientName] = {
                                    per_100_g: nutrient.value,
                                    name: nutrient.attribute_type.name,
                                };
                            } else {
                                nutrients[nutrientName].per_100_g += nutrient.value;
                            }
                        }
                    }
                }

                // Keep the global recipe total weight up to date.
                this.totalWeight = totalWeight;

                // Now we have the total of each nutrition per 100g for the whole recipe.

                // Next divide by serving size
                for (let nutrientName in nutrients) {
                    if (nutrients.hasOwnProperty(nutrientName)) {
                        let nutrient = nutrients[nutrientName];

                        if(this.amountPer === 'recipe') {
                            nutrient.value = (nutrient.per_100_g / 100) * totalWeight;
                        } else {
                            nutrient.value = (nutrient.per_100_g / 100) * parseInt(this.amountPer);
                        }

                        if(nutrientName === 'energy') {
                            // Convert to display unit
                            let conversionFactor = this.energyUnit === 'calorie' ? 1 : this.conversions.caloriesInKj;
                            nutrient.value = nutrient.value / conversionFactor;
                        }

                        nutrient.displayValue = formatNumber(nutrient.value);

                    }
                }

                // Update the global nutrients
                this.nutrients = nutrients;

                this.nutritionUpdating = false;

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
                let energy = convertEnergyUnit(this.nutrients.energy.value, this.energyUnit);
                this.nutrients.energy.value = energy;
                this.nutrients.energy.displayValue = formatNumber(energy);
            },
        }
    }
</script>

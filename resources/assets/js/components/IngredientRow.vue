<template>
    <div class="ingredient-row">

        <div class="ingredient-row__controls">

            <div class="ingredient-row__toggles">
                <div class="grabber ingredient-row__handle"></div>

                <div class="ingredient-row__nutrients-toggle"
                     @click="showNutrients=!showNutrients"
                     :class="showNutrients?'ingredient-row__nutrients-toggle--active':''">
                </div>
            </div>

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

        <div class="ingredient-row__nutrients" :class="showNutrients?'ingredient-row__nutrients--active':''">
            <div class="ingredient-row__nutrients-inner">
                <div class="ingredient-row__nutrient"
                     v-for="(nutrient, name) in displayNutrients">
                    <div class="ingredient-row__nutrient-unit">
                        {{ name }}
                    </div>
                    <div class="ingredient-row__nutrient-value">
                        {{ nutrient.value }} {{ nutrient.unit }}
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script type="text/javascript">
    import { formatNumber, getUnit } from '../helpers/misc';

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
        data() {
            return {
                displayNutrients: {},
                showNutrients: false,
            };
        },
        watch: {
            recalculate: function() {
                this.calculateNutrition();
            }
        },
        methods: {
            calculateNutrition() {

                let row = this.row;

                console.log('update ' + row.ingredient.name + ' nutrition');

                if(typeof row.unit === 'undefined' || row.unit_id !== row.unit.id) {
                    // The rows unit id has changed, reload the unit.
                    row.unit = getUnit(row.unit_id, row.ingredient.units);
                }

                // we know how this row is being measured, so we can work out how much this row weights
                switch(row.unit.type) {
                    case 'weight':
                        // If not grams then convert to grams
                        row.weight = row.unit.name === 'grams' ? row.value : row.value * row.unit.gram;
                        break;
                    case 'length':
                        let cm = (row.value * row.unit.mm) / 10;
                        row.weight = row.ingredient.weight_one_cm * cm;
                        break;
                    case 'volume':
                        let ml = row.value * row.unit.ml;
                        row.weight = row.ingredient.weight_one_ml * ml;
                        break;
                    case 'quantity':
                        row.weight = row.ingredient.weight_one * row.value;
                        break;
                }

                for (let nutrientName in row.ingredient.nutrients) {
                    if (row.ingredient.nutrients.hasOwnProperty(nutrientName)) {
                        let ingredientNutrient = row.ingredient.nutrients[nutrientName];
                        // Nutrients are stored per 100g so divide by 100 first
                        row.nutrients[nutrientName] = {
                            'value': (ingredientNutrient.value / 100) * row.weight,
                            'unit': ingredientNutrient.attribute_type.unit
                        };
                    }
                }

                this.updateDisplayNutrients();

                // Trigger a recalculation of the global recipe nutrients
                this.$emit('recalculateNutrition');
            },
            updateDisplayNutrients() {
                this.displayNutrients = {};
                for (let nutrientName in this.row.nutrients) {
                    if (this.row.nutrients.hasOwnProperty(nutrientName)) {
                        let nutrient = this.row.nutrients[nutrientName];
                        this.displayNutrients[nutrientName] = {
                            'value': formatNumber(nutrient.value),
                            'unit': nutrient.unit,
                        };
                    }
                }
            },
        }
    }
</script>
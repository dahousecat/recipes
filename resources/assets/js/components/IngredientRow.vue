<template>
    <div class="ingredient-row" :class="draggable?'ingredient-row--draggable':''">

        <div class="ingredient-row__controls">

            <!--grabber and dropdown toggle-->
            <div class="ingredient-row__toggles">
                <div v-if="draggable" class="grabber ingredient-row__handle"></div>

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
                <div class="ingredient-row__steppers">
                    <i class="fa fa-caret-up ingredient-row__stepper"
                       :class="canStep(row.value, 'up') ? '' : 'ingredient-row__stepper--disabled'"
                       @click="step(row, 'up')"
                       :title="getStep(row.value, 'up')">

                    </i>
                    <i class="fa fa-caret-down ingredient-row__stepper"
                       :class="canStep(row.value, 'down') ? '' : 'ingredient-row__stepper--disabled'"
                       @click="step(row, 'down')"
                       :title="getStep(row.value, 'down')">
                    </i>
                </div>
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

            <div class="ingredient-row__nutrients-header">
                <div class="ingredient-row__nutrient ingredient-row__nutrient--weight">
                    <div class="ingredient-row__nutrient-unit">Weight</div>
                    <div class="ingredient-row__nutrient-value" :title="weightDescription">
                        {{formatNumber(row.weight)}}g
                    </div>
                </div>

                <nutrient-rows :displayNutrients="displayNutrients" :category="'other'" :title="''"></nutrient-rows>
            </div>

            <div class="ingredient-row__nutrients-inner">
                <nutrient-rows :displayNutrients="displayNutrients" :category="'macronutrients'"></nutrient-rows>
                <nutrient-rows :displayNutrients="displayNutrients" :category="'minerals'"></nutrient-rows>
                <nutrient-rows :displayNutrients="displayNutrients" :category="'vitamins'"></nutrient-rows>
            </div>
        </div>

    </div>
</template>

<script type="text/javascript">
    import { formatNumber, getUnit } from '../helpers/misc';
    import NutrientRows from '../components/NutrientRows.vue';

    export default {
        components: {
            NutrientRows,
        },
        props: {
            row: {
                type: [Object],
                default: {},
            },
            draggable: {
                type: Boolean,
                default: true,
            },
        },
        data() {
            return {
                displayNutrients: {},
                showNutrients: false,
                weightDescription: '',
            };
        },
        computed: {
            // Computed recalcualte so we don't need to do a deep watch
            recalculate() {
                return this.row.recalculate;
            }
        },
        watch: {
            recalculate: function() {
                if(this.recalculate) {
                    this.calculateNutrition();
                }

            },
        },
        methods: {
            calculateNutrition() {

                let row = this.row;

                console.log('Calculate row nutrition for ' + row.ingredient.name);

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

                // Loop ingredient nutrients to set row nutrients
                for (let nutrientName in row.ingredient.nutrients) {
                    if (row.ingredient.nutrients.hasOwnProperty(nutrientName)) {
                        let ingredientNutrient = row.ingredient.nutrients[nutrientName];

                        if(typeof row.nutrients === 'undefined') {
                            row.nutrients = {};
                        }

                        // Nutrients are stored per 100g so divide by 100 first
                        row.nutrients[nutrientName] = {
                            'value': (ingredientNutrient.value / 100) * row.weight,
                            'unit': ingredientNutrient.attribute_type.unit,
                            'name': ingredientNutrient.attribute_type.name,
                            'category': ingredientNutrient.attribute_type.category,
                        };
                    }
                }

                this.updateDisplayNutrients();
                this.updateWeightDescription();

                // Send a signal that we are done updating our nutritional values
                this.$emit('rowUpdated');
            },
            updateDisplayNutrients() {
                this.displayNutrients = {};
                for (let nutrientName in this.row.nutrients) {
                    if (this.row.nutrients.hasOwnProperty(nutrientName)) {
                        let nutrient = this.row.nutrients[nutrientName];
                        this.displayNutrients[nutrientName] = {
                            'value': formatNumber(nutrient.value),
                            'unit': nutrient.unit,
                            'name': nutrient.name,
                            'category': nutrient.category,
                        };
                    }
                }
            },
            getStep(value, direction) {
                let mod;
                if(value < 1 && direction == 'up' || value <= 1 && direction == 'down') {
                    mod = 0.25;
                } else if(value < 2) {
                    mod = 0.5;
                } else {
                    mod = 1;
                }
                if(direction === 'up') {
                    return value + mod;
                } else {
                    return value - mod < 0 ? 0 : value - mod;
                }

            },
            canStep(value, direction) {
                let newValue = this.getStep(value, direction);
                return value !== newValue;
            },
            step(row, direction) {
                row.value = this.getStep(row.value, direction);
                this.calculateNutrition();
            },
            formatNumber(number) {
                return formatNumber(number);
            },
            updateWeightDescription() {

                let row = this.row;

                // If the row is defined in grams it needs no explanation.
                if(row.unit.type === 'weight') {
                    this.weightDescription = '';
                    return;
                }

                let desc = '1 ';
                let weight_this_unit;

                if(row.unit.type !== 'quantity') {
                    desc = desc + row.unit.name + ' ';
                }

                switch(row.unit.type) {
                    case 'length':
                        desc = desc + row.ingredient.name + ' weighs ' + row.ingredient.weight_one_cm + 'g per cm. ';
                        break;
                    case 'volume':

                        weight_this_unit = formatNumber(row.ingredient.weight_one_ml * row.unit.ml);

                        desc = desc + row.ingredient.name + ' weighs ' + weight_this_unit + 'g. ';
                        break;
                    case 'quantity':

                        weight_this_unit = row.ingredient.weight_one;

                        desc = desc + row.ingredient.name + ' weighs ' + weight_this_unit + 'g. ';
                        break;
                }



                desc = desc + row.value + ' x ' + weight_this_unit + 'g = ' + formatNumber(row.weight) + 'g';

                this.weightDescription = desc;
            }
        }
    }
</script>
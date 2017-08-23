<template>
    <div class="col col--1 nutrients" :class="[updating ? 'loading' : '', panelOpen ? 'nutrients--panel-open' : '']">

        <div class="panel">
            <h3 class="nutrients__title" @click="panelOpen=!panelOpen">Nutrition</h3>

            <div class="nutrients__inner">

                <div class="nutrients__empty-msg" v-if="Object.keys(nutrients).length == 0">
                    Add some ingredients to see the recipe nutrients
                </div>

                <div class="nutrients__top" v-if="Object.keys(nutrients).length > 0">

                    <div class="nutrients__row">
                        <div class="nutrients__unit">
                            Amount per
                        </div>
                        <div class="nutrients__value">
                            <select v-model="amountPer" @change="updatNutrition()" class="nutrients__select">
                                <option v-for="(option, index) in amountPerOptions" :value="option.value">{{option.name}}</option>
                            </select>
                        </div>
                    </div>

                    <!--<nutrient-rows-->
                    <div class="nutrients__row" v-if="typeof displayNutrients.energy != 'undefined'">
                        <div class="nutrients__unit">
                            <select v-model="energyUnit" @change="recalculateEnergy()" class="nutrients__select">
                                <option v-for="(option, index) in energyUnitOptions" :value="option.value">{{option.name}}</option>
                            </select>
                        </div>
                        <div class="nutrients__value">
                            {{ displayNutrients.energy.value }}
                        </div>
                    </div>

                </div>

                <div class="nutrients__columns" v-if="Object.keys(nutrients).length > 0">
                    <nutrient-rows :displayNutrients="displayNutrients" :category="'macronutrients'"
                                   :parentClass="'nutrients'"></nutrient-rows>
                    <nutrient-rows :displayNutrients="displayNutrients" :category="'minerals'"
                                   :parentClass="'nutrients'"></nutrient-rows>
                    <nutrient-rows :displayNutrients="displayNutrients" :category="'vitamins'"
                                   :parentClass="'nutrients'"></nutrient-rows>
                </div>

            </div>
        </div>

    </div>
</template>

<script type="text/javascript">
    import { convertEnergyUnit, formatNumber, getUnit } from '../helpers/misc';
    import NutrientRows from '../components/NutrientRows.vue';

    export default {
        components: {
            NutrientRows,
        },
        props: {
            rows: {
                type: [Array],
                default: () => [],
            },
            units: {
                type: [Array],
                default: [],
            },
            recalculate: {
                type: [Boolean],
                default: false,
            },
            servings: {
                type: [Number],
                default: 1,
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
                    {'value': 'serving', 'name': 'Serving'},
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
                panelOpen: true,
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
                                    'category': nutrient.category,
                                    'name': nutrient.name,
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

                        if(this.amountPer === 'serving') {
                            nutrient.value = nutrient.value / this.servings;
                        } else if(this.amountPer !== 'recipe') {
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
                            'category': nutrient.category,
                            'name': nutrient.name,
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

<style lang="scss">
    @import "../../sass/variables/breakpoints";

    .nutrients {
        font-size: 1.4rem;
    }

    .nutrients__title {
        position: relative;
        cursor: pointer;
        transition: color 200ms;

        &:hover {
            color: black;
        }

        &::after {
            content: "\f0d7";
            color: black;
            font-family: FontAwesome;
            transition: all 200ms;
            display: inline-block;
            position: absolute;
            right: 0;

            .nutrients--panel-open & {
                transform: rotate(180deg);
            }
        }
    }

    .nutrients__inner {
        display: none;

        .nutrients--panel-open & {
            display: block;
        }
    }

    .nutrients__top {
        margin: 0 auto;
    }

    .nutrients__row {
        border-bottom: 1px solid darken(lightgrey, 10);
        display: Flex;
        line-height: 2;
        justify-content: space-between;
        padding: 0rem 0 0.8rem 0;
    }

    .nutrients__select {
        display: inline-block;
        background-color: lightgrey;
        padding: 0.2rem;
        position: relative;
        top: 0.4rem;
    }

    .nutrients__columns {
        display: flex;
        justify-content: space-between;
        padding-top: 2rem;
        flex-direction: column;

        @include mq($from: s) {
            flex-direction: row;
        }

        @include mq($from: m) {
            flex-direction: column;
        }

        @include mq($from: xl) {
            flex-direction: row;
        }
    }

    .nutrients__nutrients-group {
        flex: 1;

        &:first-child {
            margin-left: 0;
        }

        &:last-child {
            margin-right: 0;
        }

        @include mq($from: s) {
            margin: 0 1rem;
        }

        @include mq($from: m) {
            margin: 0;
        }

        @include mq($from: xl) {
            margin: 0 0.5rem;
        }
    }

    .nutrients__nutrient-group-title {
        position: relative;
        transition: color 200ms;
        cursor: pointer;
        padding: 0rem 0.4rem;
        margin-left: -0.4rem;
        margin-right: -0.4rem;

        &:hover {
            color: black;
            background-color: darken(lightgrey, 10);

            @include mq($from: s) {
                background-color: transparent;
            }

            @include mq($from: m) {
                background-color: darken(lightgrey, 10);
            }

            @include mq($from: xl) {
                background-color: transparent;
            }
        }

        &::after {
            content: "\f0d7";
            color: black;
            font-family: FontAwesome;
            transition: all 200ms;
            display: inline-block;
            position: absolute;
            right: 0.4rem;

            @include mq($from: s) {
                display: none;
            }

            @include mq($from: m) {
                display: inline-block;
            }

            @include mq($from: xl) {
                display: none;
            }

            .nutrients__nutrients-group--active & {
                transform: rotate(180deg);
            }
        }
    }

    .nutrients__nutrient {
        justify-content: space-between;
        border-bottom: 1px solid grey;
        display: none;

        @include mq($from: s) {
            display: flex;
        }

        @include mq($from: m) {
            display: none;
        }

        @include mq($from: xl) {
            display: flex;
        }

        .nutrients__nutrients-group--active & {
            display: flex;
        }
    }

    .nutrients__unit {

    }

    .nutrients__value {
        text-align: right;
    }

    .nutrients__empty-msg {
        text-align: center;
    }

</style>
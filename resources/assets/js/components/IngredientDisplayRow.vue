<template>
    <div class="ingredient-row">

        <div class="ingredient-row__controls">

            <!--nutrients toggle-->
            <div class="ingredient-row__nutrients-toggle"
                 title="Show nutrients"
                 @click="showNutrients=!showNutrients"
                 :class="showNutrients?'ingredient-row__nutrients-toggle--active':''">
            </div>

            <!--name-->
            <div class="ingredient-row__name">
                {{row.ingredient.name}}
            </div>

            <!--amount-->
            <div class="ingredient-row__amount">
                {{convertedValue}}
            </div>

            <!--unit-->
            <div class="ingredient-row__unit">
                <select v-model="displayUnit" @change="changeUnit()" class="ingredient-row__control">
                    <option v-for="(unit, index) in row.ingredient.units" :value="unit.id">{{unit.abbr}}</option>
                </select>
            </div>

        </div>

        <div class="ingredient-row__nutrients" :class="showNutrients?'ingredient-row__nutrients--active':''">

            <div class="ingredient-row__nutrients-header">
                <div class="ingredient-row__nutrient ingredient-row__nutrient--weight">
                    <div class="ingredient-row__nutrient-unit">Weight</div>
                    <div class="ingredient-row__nutrient-value" :title="weightDescription"
                         v-if="typeof row.weight !== 'undefined'">
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
    import { calculateRowNutrition, updateDisplayNutrients, getWeightDescription } from '../helpers/ingredientRow';
    import NutrientRows from '../components/NutrientRows.vue';

    export default {
        components: {
            NutrientRows,
        },
        props: {
            row: {
                type: [Object],
                default: () => {},
            },
            units: {
                type: [Array],
                default: [],
            },
        },
        data() {
            return {
                displayNutrients: {},
                showNutrients: false,
                weightDescription: '',
                convertedValue: '',
                displayUnit: '',
            };
        },
        computed: {
            // Computed recalculate so we don't need to do a deep watch
            recalculate() {
                return this.row.recalculate;
            }
        },
        created() {
//            this.calculateNutrition();
            this.convertedValue = this.row.value;
            this.displayUnit = this.row.unit_id;
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
                calculateRowNutrition(this.row);
                updateDisplayNutrients(this.displayNutrients, this.row);
                this.weightDescription = getWeightDescription(this.row);
                this.$emit('rowUpdated');
            },
            changeUnit() {
                let fromUnit = this.row.unit;
                let toUnit = getUnit(this.displayUnit, this.units);

                switch(toUnit.type) {
                    case 'quantity':
                        let quantity = this.row.value;
                        this.convertedValue = quantity;
                        break;
                    case 'weight':
                        let grams = this.row.weight;
                        this.convertedValue = formatNumber(grams / toUnit.gram);
                        break;
                    case 'volume':
                        let ml = fromUnit.ml;
                        this.convertedValue = formatNumber(ml / toUnit.ml);
                        break;
                }
            },
            formatNumber(number) {
                return formatNumber(number);
            },
        }
    }
</script>

<style lang="scss">
    @import "../../sass/variables/breakpoints";

    .ingredient-row {
        background-color: darkcyan;
        padding: 1rem;
        color: white;
        margin-bottom: 1rem;
    }

    .ingredient-row__controls {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
    }

    .ingredient-row__name {
        order: 1;
        flex: 1;

        @include mq($from: xs) {
            flex: 0 0 50%;
        }

        @include mq($from: m) {
            flex: 1;
        }
    }

    .ingredient-row__amount {
        width: 5rem;
        margin: 0 1rem 0 1rem;
        padding-left: 2rem;
        position: relative;
        order: 2;

        &::before {
            content: 'x';
            position: absolute;
            left: 0;
        }
    }

    .ingredient-row__amount-input {
        width: 100%;
    }

    .ingredient-row__steppers {
        display: none;
    }

    .ingredient-row__unit {
        order: 3;
        margin-bottom: 1rem;
        flex: 0 0 28%;
        text-align: right;

        @include mq($from: l) {
            margin-bottom: 0;
        }
    }

    .ingredient-row__nutrients-toggle {
        cursor: pointer;
        order: 4;
        flex: 0 0 50%;

        @include mq($from: l) {
            flex: 0 0 auto;
            order: -1;
            padding: 0 0.6rem;
            margin-right: 1rem;
        }

        &:hover {
            @include mq($from: l) {
                background-color: darken(darkcyan, 15);
            }
        }

        &::before {
            content: 'Nutrients';
            margin-right: 0.5rem;
            font-size: 1.4rem;
            color: lightblue;
            transition: color 200ms;

            @include mq($from: l) {
                display: none;
            }
        }

        &::after {
            content: "\f0d7";
            color: lightblue;
            font-family: FontAwesome;
            transition: all 200ms;
            display: inline-block;
        }

        &:hover::before,
        &:hover::after {
            color: lighten(lightblue, 5);
        }
    }

    .ingredient-row__remove {
        order: 5;
        flex: 0 0 50%;
        text-align: right;

        @include mq($from: l) {
            flex: 0 0 auto;
            margin-left: 1rem;
        }
    }

    .ingredient-row__remove_button {
        background-color: darken(darkcyan, 5);
        color: lightblue;
        border: none;
        cursor: pointer;
        transition: background-color 200ms;

        &:hover {
            background-color: darken(darkcyan, 15);
        }
    }

    .ingredient-row__nutrients-toggle--active::after {
        transform: rotate(180deg);
    }

    .ingredient-row__nutrients {
        display: none;
        background-color: darken(darkcyan, 3);
        font-size: 1.2rem;
        padding: 0.8rem 1rem;

        @include mq($from: s) {
            padding: 0;
            background-color: transparent;
            margin: 1rem 0 0;
        }

        @include mq($from: m) {
            padding: 0.8rem 1rem;
            background-color: darken(darkcyan, 3);
            /*margin: 0;*/
        }

        @include mq($from: xl) {
            padding: 0;
            background-color: transparent;
            /*margin: 1rem 0 0;*/
        }
    }

    .ingredient-row__nutrients--active {
        display: block;
    }

    .ingredient-row__nutrients-inner {
        display: flex;
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

    .ingredient-row__nutrients-group {
        flex: 1;

        @include mq($from: s) {
            margin: 0 1rem;
        }

        @include mq($from: m) {
            margin: 0;
        }

        @include mq($from: xl) {
            margin: 0 1rem;
        }

        &:first-child {
            margin-left: 0;
        }

        &:last-child {
            margin-right: 0;
        }

        .ingredient-row__nutrients-header & {
            margin: 0;
        }
    }

    .ingredient-row__nutrient-group-title {
        position: relative;
        cursor: pointer;

        &::after {
            content: "\f0d7";
            color: lightblue;
            font-family: FontAwesome;
            transition: all 200ms;
            display: inline-block;
            position: absolute;
            right: 0;

            @include mq($from: s) {
                display: none;
            }

            @include mq($from: m) {
                display: inline-block;
            }

            @include mq($from: xl) {
                display: none;
            }

            .ingredient-row__nutrients-group--active & {
                transform: rotate(180deg);
            }
        }
    }

    .ingredient-row__nutrient {
        justify-content: space-between;
        border-bottom: 1px solid darken(darkcyan, 1);
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

        .ingredient-row__nutrients-header &,
        .ingredient-row__nutrients-group--active & {
            display: flex;
        }
    }
</style>
<template>
    <div class="ingredient-row">

        <div class="ingredient-row__controls">

            <!--grabber and dropdown toggle-->
            <!--<div class="ingredient-row__toggles">-->
                <!--<div v-if="draggable" class="grabber ingredient-row__handle"></div>-->

                <!--<div class="ingredient-row__nutrients-toggle"-->
                     <!--@click="showNutrients=!showNutrients"-->
                     <!--:class="showNutrients?'ingredient-row__nutrients-toggle&#45;&#45;active':''">-->
                <!--</div>-->
            <!--</div>-->

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
            <div class="ingredient-row__amount" v-if="editable">
                <input type="text" class="ingredient-row__amount-input"
                       v-model="displayValue"
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
            <div class="ingredient-row__amount" v-else>
                {{row.value}}
            </div>

            <!--unit-->
            <div class="ingredient-row__unit">
                <select v-model="row.unit_id" @change="calculateNutrition()" class="ingredient-row__control">
                    <option v-for="(unit, index) in row.ingredient.units" :value="unit.id">{{unit.abbr}}</option>
                </select>
            </div>

            <!--remove-->
            <div class="ingredient-row__remove" v-if="editable">
                <button class="ingredient-row__remove_button" :title="'Remove ' + row.ingredient.name"
                        @click="$emit('removeIngredient')">&times;</button>
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
    import { formatNumber, getUnit, isNumeric } from '../helpers/misc';
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
            draggable: {
                type: Boolean,
                default: true,
            },
            editable: {
                type: Boolean,
                default: true,
            },
        },
        data() {
            return {
                displayValue: '',
                displayNutrients: {},
                showNutrients: false,
                weightDescription: '',
            };
        },
        computed: {
            // Computed recalculate so we don't need to do a deep watch
            recalculate() {
                return this.row.recalculate;
            }
        },
        created() {
            this.displayValue = this.row.value;
            if(this.recalculate) {
                this.calculateNutrition();
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
                this.setRowValue();
                calculateRowNutrition(this.row);
                updateDisplayNutrients(this.displayNutrients, this.row);
                this.weightDescription = getWeightDescription(this.row);
                this.$emit('rowUpdated');
            },
            setRowValue() {
                let row = this.row;

                this.displayValue = String(this.displayValue);

                if(this.displayValue.indexOf('/') !== -1) {

                    let parts = this.displayValue.split('/');
                    this.displayValue = this.getFraction(parts[0], parts[1]);

                    if(isNumeric(parts[0]) && isNumeric(parts[1])) {
                        row.value =  parseInt(parts[0]) / parseInt(parts[1]);
                    }
                } else {
                    row.value =  this.displayValue;
                }

            },
            getFraction(numerator, denominator) {
                numerator = parseInt(numerator);
                denominator = parseInt(denominator);
                if(numerator === 1 && denominator === 2) {
                    return '½';
                } else if(numerator === 1 && denominator === 3) {
                    return '⅓';
                } else if(numerator === 2 && denominator === 3) {
                    return '⅔';
                } else if(numerator === 1 && denominator === 4) {
                    return '¼';
                } else if(numerator === 3 && denominator === 4) {
                    return '¾';
                } else {
                    return numerator + '/' + denominator;
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
        width: 6rem;
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
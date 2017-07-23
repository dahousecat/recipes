<template>
    <div :class="parentClass + '__nutrients-group ingredient-row__nutrients-group--' + category">
        <h3 v-if="getTitle()" :class="parentClass + '__nutrient-group-title'">{{getTitle()}}</h3>

        <div :class="parentClass + '__nutrient' + ' ' + parentClass + '__nutrient--' + name"
             v-for="(nutrient, name) in displayNutrients"
             v-if="category === null || category === nutrient.category">
            <div :class="parentClass + '__nutrient-unit'">
                {{ nutrient.name }}
            </div>
            <div :class="parentClass + 'ingredient-row__nutrient-value'">
                {{ nutrient.value }} {{ nutrient.unit }}
            </div>
        </div>
    </div>
</template>

<script type="text/javascript">
    import { capitalize } from '../helpers/misc';

    export default {
        props: {
            displayNutrients: {
                type: [Object],
                default: {},
            },
            category: {
                type: [String],
                default: null,
            },
            title: {
                type: [String],
                default: null,
            },
            parentClass: {
                type: [String],
                default: 'ingredient-row',
            },
        },
        methods: {
            getTitle() {
                return this.title !== null ? this.title : capitalize(this.category)
            }
        }
    }
</script>
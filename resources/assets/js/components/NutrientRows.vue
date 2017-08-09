<template>
    <div :class="getClass()">
        <h3 v-if="getTitle()"
            :class="this.parentClass + '__nutrient-group-title'"
            @click="active=!active">
            {{getTitle()}}
        </h3>

        <div :class="parentClass + '__nutrient' + ' ' + parentClass + '__nutrient--' + name"
             v-for="(nutrient, name) in displayNutrients"
             v-if="category === null || category === nutrient.category">
            <div :class="parentClass + '__nutrient-unit'">
                {{ nutrient.name }}
            </div>
            <div :class="parentClass + '__nutrient-value'">
                {{ nutrient.value }} {{ nutrient.unit }}
            </div>
        </div>
    </div>
</template>

<script type="text/javascript">
    import { capitalize } from '../helpers/misc';

    export default {
        data() {
            return {
                active: false
            }
        },
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
            },
            getClass() {
                let classes = [];
                classes.push(this.parentClass + '__nutrients-group');
                classes.push(this.parentClass + '__nutrients-group--' + this.category);

                if(this.active) {
                    classes.push(this.parentClass + '__nutrients-group--active');
                }

                return classes;
            }
        }
    }
</script>
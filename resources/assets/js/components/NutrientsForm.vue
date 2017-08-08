<template>
    <div>
        <div class="row" v-for="(type, index) in attributeTypes" v-if="category === null || category === type.category">
            <div class="col-s-6">
                <label>
                    {{capitalize(type.name)}} ({{type.unit}})
                    <a :href="getSearchUrl(type)" target="_blank">
                        <i class="fa fa-question-circle" aria-hidden="true" v-if="form.name"></i>
                    </a>
                </label>
            </div>
            <div class="col-s-6">
                <input type="text" class="form__control"
                       v-model="form.nutrients[type.safe_name].value">
            </div>
        </div>
    </div>
</template>

<script type="text/javascript">
    import { capitalize } from '../helpers/misc';

    export default {
        props: {
            attributeTypes: {
                type: [Array],
                default: {},
            },
            form: {
                type: [Object],
                default: false,
            },
            category: {
                type: [String],
                default: null,
            },
        },
        methods: {
            getSearchUrl(type) {
                let attribute = type.name === 'energy' ? this.energyUnit : type.name;
                return 'https://www.google.com.au/search?q=how+many+' +
                    attribute +
                    '+are+there+in+100g+of+' + this.form.name;
            },
            capitalize(string) {
                return capitalize(string);
            }
        }
    }
</script>
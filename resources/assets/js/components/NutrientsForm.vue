<template>
    <div>
        <div class="form-group nutrients__group" v-for="(type, index) in attributeTypes" v-if="category === null || category === type.category">

            <label class="form-group__label nutrients__label" :for="'nutrient-' + type.safe_name">
                {{capitalize(type.name)}} ({{type.unit}})
                <a :href="getSearchUrl(type)" :title="getSearchTitle(type)" target="_blank">
                    <i class="fa fa-question-circle" aria-hidden="true" v-if="form.name"></i>
                </a>
            </label>

            <input type="text" class="form-group__input nutrients__input" :id="'nutrient-' + type.safe_name"
                   v-model="form.nutrients[type.safe_name].value">

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
                let title = this.getSearchTitle(type);
                let url = 'https://www.google.com.au/search?q=' +
                        title.replace(/ /g, '+');
                return url;
            },
            getSearchTitle(type) {
                let text = 'How ';
                if(type.name.toLowerCase() === 'energy') {
                    text = text + ' many calories are';
                } else {
                    text = text + ' much ' + type.name.toLowerCase() + ' is';
                }
                text = text + ' there in 100g of ' + this.form.name.toLowerCase();
                return text;
            },
            capitalize(string) {
                return capitalize(string);
            }
        }
    }
</script>

<style lang="scss">
    @import "../../sass/variables/breakpoints";

    .nutrients__group {
        flex-direction: row;
    }

    .nutrients__label {
        flex: 0 0 70%;
        margin-right: 0;
        font-size: 1.4rem;
    }

    .nutrients__input {
        flex: 0 0 30%;
        width: 30%;
    }

</style>


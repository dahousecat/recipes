<template>
    <form action="#">

        <h1 class="form__title">Ingredient form</h1>

        <fieldset>

            <div class="col-s-4">
                <div class="form__group">
                    <label>Name</label>
                    <input type="text" class="form__control" v-model="form.name">
                    <small class="error__control" v-if="error.name">{{error.name[0]}}</small>
                </div>
                <div class="form__group">
                    <label>
                        Weight (grams)

                        <a :href="getWeightUrl()" target="_blank" v-if="form.name">
                            <i class="fa fa-question-circle" aria-hidden="true"></i>
                        </a>
                    </label>
                    <input type="text" class="form__control" v-model="form.weight">
                    <small class="error__control" v-if="error.weight">{{error.weight[0]}}</small>
                </div>
            </div>

            <div class="col-s-4">

                <p class="form__title">Would you measure {{form.name}} using...</p>

                <div class="row" v-for="(unitType, unitTypeName) in unitTypes">
                    <div class="col-s-1">
                        <input :id="unitTypeName" type="checkbox" class="form__control" v-model="unitType.checked" @change="updateUnits()">
                    </div>
                    <div class="col-s-11">
                        <label :for="unitTypeName">{{ unitType.label }}</label>
                    </div>
                </div>

                <div class="row">
                    <div class="col-s-6">
                        <label>Default unit id</label>
                    </div>
                    <div class="col-s-6">
                        <select type="text" class="form__control" v-model="form.default_unit_id">
                            <option v-for="(unit, index) in default_unit_options" :value="unit.id">{{unit.name}}</option>
                        </select>
                    </div>
                </div>

            </div>

            <div class="col-s-4">

                <p class="form__title">Nutritional information (per {{nutritionPer}}g)</p>

                <button @click="searchNdb()">Autofill</button>

                <div class="row" v-for="(type, index) in attributeTypes">
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
                               v-model="nutrients[type.safe_name].value">
                    </div>
                </div>

            </div>

            <button>Save</button>

        </fieldset>
    </form>
</template>

<script type="text/javascript">
    import Vue from 'vue';
    import Flash from '../../helpers/flash';
    import { get, post } from '../../helpers/api';
    import { convertEnergyUnit, formatNumber, getUnit } from '../../helpers/convert';
    import { toMulipartedForm, objectToFormData } from '../../helpers/form';

    export default {
        data() {
            return {
                form: {
                    name: '',
                    weight: '',
                    units: [],
                    attributes: [],
                },
                error: {},
                isProcessing: false,
                initializeURL: `/api/ingredients/create`,
                storeURL: `/api/ingredients`,
                action: 'Create',
                units: [],
                unitTypes: {
                    volume: {
                        label: 'Volume (Litres or cups)',
                        checked: false,
                    },
                    weight: {
                        label: 'Weight (Kilograms or pounds)',
                        checked: false,
                    },
                    length: {
                        label: 'Length (Centimeters or inches)',
                        checked: false,
                    },
                    quantity: {
                        label: 'Quantity (Count how many)',
                        checked: false,
                    },
                },
                attributeTypes: [],
                default_unit_options: [],
                ndb: {
                    'name': '',
                    'groups': {},
                },
                energyUnit: 'calorie',
                nutritionPer: 100, // grams
                nutrients: {}
            }
        },
        created() {
            if(this.$route.meta.mode === 'edit') {
                this.initializeURL = `/api/ingredients/${this.$route.params.id}/edit`;
                this.storeURL = `/api/ingredients/${this.$route.params.id}?_method=PUT`;
                this.action = 'Update';
            }
            get(this.initializeURL)
                .then((res) => {
                    Vue.set(this.$data, 'form', res.data.form);
                    Vue.set(this.$data, 'units', res.data.units);
                    Vue.set(this.$data, 'attributeTypes', res.data.attributeTypes);

                    // Set the default values for the attribute types
                    for (let i = 0; i < this.attributeTypes.length; i++) {
                        let attributeType = this.attributeTypes[i];
                        this.nutrients[attributeType.safe_name] = {value: ''};
                    }
                });
        },
        methods: {
            getWeightUrl() {
                return 'https://www.google.com.au/search?q=how+much+does+a+' +
                    this.form.name + '+weight?';
            },
            updateUnits() {
                this.default_unit_options = [];

                for (let i = 0; i < this.units.length; i++) {
                    let unit = this.units[i];
                    if(this.unitTypes[unit.type].checked) {
                        this.default_unit_options.push(unit);
                    }
                }
            },
            searchNdb() {
                get('/api/ndb/search/' + this.form.name)
                    .then((res) => {
                        this.modalLoading = false;
                        this.ndb.groups = res.data.groups;
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            },
            getSearchUrl(type) {
                let attribute = type.name === 'energy' ? this.energyUnit : type.name;
                return 'https://www.google.com.au/search?q=how+many+' +
                    attribute +
                    '+are+there+in+100g+of+' + this.form.name;
            },
            capitalize(string) {
                return string.charAt(0).toUpperCase() + string.slice(1);
            },
        }
    }
</script>
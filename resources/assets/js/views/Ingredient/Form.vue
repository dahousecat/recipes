<template>
    <div class="form">

        <h1 class="form__title">Ingredient form</h1>

        <div class="form__actions">
            <button class="btn btn__primary" @click="save()">Save</button>
            <button class="btn" @click="$router.back()">Cancel</button>
        </div>

        <fieldset>

            <div class="row equal-height">

                <!-- Basic Details -->
                <div class="col-s-4 form__panel">
                    <div class="">
                        <div class="form__group">
                            <label>Name</label>
                            <input type="text" class="form__control" v-model="form.name">
                            <small class="error__control" v-if="error.name">{{error.name[0]}}</small>
                        </div>
                        <div class="form__group" v-if="typeof form.units.quantity !== 'undefined'">
                            <label>
                                How much does one {{form.name}} weight? (grams)
                                <a :href="getWeightUrl()" target="_blank" v-if="form.name">
                                    <i class="fa fa-question-circle" aria-hidden="true"></i>
                                </a>
                            </label>
                            <input type="text" class="form__control" v-model="form.weight">
                            <small class="error__control" v-if="error.weight">{{error.weight[0]}}</small>
                        </div>
                    </div>
                </div>

                <!-- Units -->
                <div class="col-s-4 form__panel">
                    <div class="">
                        <div class="form__title">Would you measure {{form.name}} using...</div>

                        <div class="row" v-for="(unitType, unitTypeName) in unitTypes">
                            <div class="col-s-1">
                                <input :id="unitTypeName" type="checkbox" class="form__control"
                                       v-model="unitType.checked" @change="updateUnits()">
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
                                    <option v-for="(unit, index) in form.units" :value="unit.id">{{unit.name}}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Nutrients -->
                <div class="col-s-4 form__panel" id="nutrients-panel">

                    <div class="">

                        <div class="form__title">Nutritional information (per {{nutritionPer}}g)</div>

                        <button @click="searchNdb()" :disabled="form.name.length ? false : true">Autofill</button>

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
                                       v-model="form.nutrients[type.safe_name].value">
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </fieldset>

        <modal :show="!!Object.keys(ndb.groups).length" @close="ndb.groups = {}">
            <h2 slot="title">Select the closest match</h2>

            <ul class="ingredient-list">
                <li v-for="(group, key, index) in ndb.groups">

                    <span class="ingredient-list__heading">{{key}}</span>

                    <ul class="ingredient-list__child-list">
                        <li v-for="(item, index) in group" class="ingredient-list__row">
                            <a @click="selectNdbIngredient(item)">{{item.name}}</a>
                        </li>
                    </ul>

                </li>
            </ul>
        </modal>

    </div>
</template>

<script type="text/javascript">
    import Vue from 'vue';
    import Flash from '../../helpers/flash';
    import { get, post } from '../../helpers/api';
    import { convertEnergyUnit, formatNumber, getUnit, loading } from '../../helpers/misc';
    import { toMulipartedForm, objectToFormData } from '../../helpers/form';
    import Modal from '../../components/Modal.vue';

    export default {
        components: {
            Modal,
        },
        data() {
            return {
                form: {
                    name: '',
                    weight: '',
                    units: {},
                    nutrients: {},
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
                ndb: {
                    'name': '',
                    'groups': {},
                },
                energyUnit: 'calorie',
                nutritionPer: 100, // grams
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
                });
        },
        methods: {
            getWeightUrl() {
                return 'https://www.google.com.au/search?q=how+much+does+one+' +
                    this.form.name + '+weight?';
            },
            updateUnits() {
                this.form.units = {};

                for (var name in this.units) {
                    if (this.units.hasOwnProperty(name)) {
                        let unit = this.units[name];
                        if(this.unitTypes[unit.type].checked) {
                            this.form.units[name] = unit;
                        }
                    }
                }
            },
            searchNdb() {
                loading(true);
                get('/api/ndb/search/' + this.form.name)
                    .then((res) => {
                        loading(false);
                        this.ndb.groups = res.data.groups;
                    })
                    .catch(function (error) {
                        loading(false);
                        console.log(error);
                    });
            },
            selectNdbIngredient(item) {
                this.ndb.groups = {};
                loading(true, 'nutrients-panel');
                get('/api/ndb/view/' + item.id)
                    .then((res) => {
                        loading(false, 'nutrients-panel');
                        for (let i = 0; i < res.data.length; i++) {
                            let row = res.data[i];
                            this.form.nutrients[row.attribute_safe_name].value = row.value;
                        }
                    })
                    .catch(function (error) {
                        loading(false);
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
            save() {
                loading(true);
                post(this.storeURL, this.form)
                    .then((res) => {
                        loading(false);
                        Flash.setSuccess('Ingredient saved.')
                    })
                    .catch((err) => {
                        loading(false);
                        if(typeof err.response !== 'undefined' && err.response.status === 422) {
                            this.error = err.response.data
                        }
                    })
            }
        }
    }
</script>
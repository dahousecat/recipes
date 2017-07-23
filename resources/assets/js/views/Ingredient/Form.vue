<template>
    <div class="form ingredient-form">

        <h1 class="form__title">Ingredient form</h1>

        <div class="form__actions">
            <button class="btn btn__primary" @click="save()">Save</button>
            <button class="btn" @click="$router.back()">Cancel</button>
        </div>

        <fieldset>

            <div class="row equal-height">

                <div class="col-s-4 form__panel">

                    <!-- Basic Details -->
                    <div class="">
                        <div class="form__group">
                            <label for="name">Name</label>
                            <input id="name" type="text" class="form__control" v-model="form.name">
                            <small class="error-msg" v-if="error.name">{{error.name[0]}}</small>
                        </div>
                    </div>

                    <!-- Units -->
                    <div class="">
                        <div class="form__title">Would you measure {{form.name}} using...</div>

                        <small class="error-msg" v-if="error.units">{{error.units[0]}}</small>

                        <!--loop unit types-->
                        <div class="unit-types__group" v-for="(unitType, unitTypeName) in unitTypes">
                            <div class="row unit-types__row">
                                <div class="col-s-1">
                                    <input :id="unitTypeName" type="checkbox" class="form__control"
                                           v-model="unitType.checked" @change="setUnitsFromUnitTypes()">
                                </div>
                                <div class="col-s-11">
                                    <label :for="unitTypeName">{{ unitType.label }}</label>
                                </div>
                            </div>
                            <div class="row unit-types__row" v-if="unitType.term && unitType.checked">
                                <div class="col-s-8">
                                    <label :for="unitTypeName + '_weight'" class="unit-types__label">
                                        How much does {{unitType.term}} weight?
                                        <a :href="getWeightUrl(unitType)" :title="getWeightTitle(unitType)" target="_blank" v-if="form.name">
                                            <i class="fa fa-question-circle" aria-hidden="true"></i>
                                        </a>
                                    </label>
                                </div>
                                <div class="col-s-4 flex">
                                    <input :id="unitTypeName + '_weight'" type="input" class="unit-types__weight"
                                           v-model="form[unitType.key]" @change="setUnitsFromUnitTypes()">
                                    <span class="unit-types__unit" title="Grams">g</span>
                                </div>
                            </div>
                            <div class="row unit-types__error-row" v-if="unitType.term && unitType.checked">
                                <div class="col-s-12">
                                    <small class="error-msg" v-if="error[unitType.key]">{{error[unitType.key][0]}}</small>
                                </div>
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
                        <div class="row unit-types__error-row" v-if="error.default_unit_id">
                            <div class="col-s-12">
                                <small class="error-msg">{{error.default_unit_id[0]}}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Nutrients 1 -->
                <div class="col-s-4 form__panel ingredient-form__unit-types unit-types">

                    <div class="">

                        <div class="form__title">Nutritional information (per {{nutritionPer}}g)</div>

                        <button @click="searchNdb()" :disabled="form.name.length ? false : true">Autofill</button>

                        <h3>Energy</h3>
                        <nutrients-form
                                :attributeTypes="attributeTypes"
                                :form="form"
                                :category="'other'">
                        </nutrients-form>

                        <h3>Macronutrients</h3>
                        <nutrients-form
                            :attributeTypes="attributeTypes"
                            :form="form"
                            :category="'macronutrients'">
                        </nutrients-form>

                        <h3>Minerals</h3>
                        <nutrients-form
                                :attributeTypes="attributeTypes"
                                :form="form"
                                :category="'minerals'">
                        </nutrients-form>

                    </div>

                </div>

                <!-- Nutrients 2 -->
                <div class="col-s-4 form__panel" id="nutrients-panel">

                    <h3>Vitamins</h3>
                    <nutrients-form
                            :attributeTypes="attributeTypes"
                            :form="form"
                            :category="'vitamins'">
                    </nutrients-form>

                </div>
            </div>

        </fieldset>

        <!-- Select NDB modal -->
        <modal :show="!!Object.keys(ndb.groups).length" @close="ndb.groups = {}">

            <ndb-ingredients :ndb="ndb"
                             :ingredient="form.name"
                             @close="ndb.groups = {}"
                             @updateNutrients="updateNutrients">
            </ndb-ingredients>

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
    import NutrientsForm from '../../components/NutrientsForm.vue';
    import NdbIngredients from '../../components/NdbIngredients.vue';

    export default {
        components: {
            Modal,
            NutrientsForm,
            NdbIngredients,
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
                        label: 'Volume (Litres, cups, teaspoons etc)',
                        checked: false,
                        term: 'one cup',
                        key: 'weight_one_cup',
                        suffix: ' of ',
                    },
                    weight: {
                        label: 'Weight (Kilograms or pounds)',
                        checked: false,
                        term: false,
                    },
                    length: {
                        label: 'Length (Centimeters or inches)',
                        checked: false,
                        term: 'one cm',
                        key: 'weight_one_cm',
                        suffix: ' of ',
                    },
                    quantity: {
                        label: 'Quantity (Count how many)',
                        checked: false,
                        term: 'one',
                        key: 'weight_one',
                        suffix: '',
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
            this.init();
        },
        watch: {
            '$route' (to, from) {
                console.log('route change');
                this.init();
            }
        },
        methods: {
            init() {
                loading(true);
                if(this.$route.meta.mode === 'edit') {
                    this.initializeURL = `/api/ingredients/${this.$route.params.id}/edit`;
                    this.storeURL = `/api/ingredients/${this.$route.params.id}?_method=PUT`;
                    this.action = 'Update';
                } else {
                    this.initializeURL = `/api/ingredients/create`;
                    this.storeURL = `/api/ingredients`;
                    this.action = 'Create';
                }
                get(this.initializeURL)
                    .then((res) => {
                        Vue.set(this.$data, 'form', res.data.form);
                        Vue.set(this.$data, 'units', res.data.units);
                        Vue.set(this.$data, 'attributeTypes', res.data.attributeTypes);

                        this.setUnitTypesFromUnits();
                        loading(false);
                    });
            },
            getWeightUrl(unitType) {
                let url = 'https://www.google.com.au/search?q=';
                let query = 'how+much+does+' + unitType.term + '+' + unitType.suffix + this.form.name.toLowerCase() +  '+weight+in+grams?';
                return url + query;
            },
            getWeightTitle(unitType) {
                return 'How much does ' + unitType.term + unitType.suffix + ' ' + this.form.name.toLowerCase() + ' weigh in grams?';
            },
            setUnitsFromUnitTypes() {
                let form = this.form;
                form.units = {};

                for (var name in this.units) {
                    if (this.units.hasOwnProperty(name)) {
                        let unit = this.units[name];
                        if(this.unitTypes[unit.type].checked) {
                            form.units[name] = unit;
                        }
                    }
                }

                // If there is only 1 unit type available set it as the default
                if(Object.keys(form.units).length === 1) {
                    let key = Object.keys(form.units)[0];
                    form.default_unit_id = form.units[key].id;
                }

            },
            setUnitTypesFromUnits() {

                // Loop unitTypes to uncheck all
                for (var name in this.unitTypes) {
                    if (this.unitTypes.hasOwnProperty(name)) {
                        this.unitTypes[name].checked = false;
                    }
                }

                // Loop form units to check active types
                for (var name in this.form.units) {
                    if (this.form.units.hasOwnProperty(name)) {
                        let unit = this.form.units[name];
                        this.unitTypes[unit.type].checked = true;
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
            updateNutrients(data) {
                for (let i = 0; i < data.length; i++) {
                    let row = data[i];
                    if(typeof this.form.nutrients[row.attribute_safe_name] === 'undefined') {
                        this.form.nutrients[row.attribute_safe_name] = {};
                    }
                    this.form.nutrients[row.attribute_safe_name].value = row.value;
                }
            },
            save() {
                loading(true);

                // Clone the form so we don't alter the original
                let data = JSON.parse(JSON.stringify(this.form));

                // Units just needs to be an array of ids
                data.units = this.getUnitsIds();

                // If volume is not a unit type in use clear the cup weight value
                if(!this.unitTypes.volume.checked) {
                    data.weight_one_cup = '';
                }

                // If length is not a unit type in use clear the cm weight value
                if(!this.unitTypes.length.checked) {
                    data.weight_one_cm = '';
                }

                // If volume is not a unit type in use clear the cup weight value
                if(!this.unitTypes.quantity.checked) {
                    data.weight_one = '';
                }

                post(this.storeURL, data)
                    .then((res) => {
                        loading(false);
                        Flash.setSuccess('Ingredient saved.');
                        this.$router.push('/ingredients');
                    })
                    .catch((err) => {
                        loading(false);
                        if(typeof err.response !== 'undefined' && err.response.status === 422) {
                            this.error = err.response.data
                        }
                    })
            },
            getUnitsIds() {
                let ids = [];
                for (var name in this.form.units) {
                    if (this.form.units.hasOwnProperty(name)) {
                        let unit = this.form.units[name];
                        ids.push(unit.id);
                    }
                }
                return ids;
            }
        },
    }
</script>
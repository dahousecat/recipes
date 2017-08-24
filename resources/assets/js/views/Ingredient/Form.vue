<template>
    <div class="ingredient-form">

        <div class="row row--m" v-if="showHeader">
            <div class="col col--1">
                <div class="panel panel--header">
                    <h2>{{action}} Ingredient</h2>
                    <div class="recipe__button-group">
                        <button class="btn" @click="save" :disabled="isProcessing">Save</button>
                        <button class="btn" @click="cancelClick" :disabled="isProcessing">Cancel</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row row--l">

            <div class="col col--1">

                <div class="panel">

                    <!-- Basic Details -->
                    <div class="form-section">
                        <h3>Basic details</h3>
                        <div class="form-group">
                            <label for="name" class="form-group__label ingredient__label">Name</label>
                            <input id="name" type="text" class="form-group__input" v-model="form.name" ref="name">
                        </div>

                        <small class="error-msg" v-if="error.name">{{error.name[0]}}</small>
                    </div>


                    <!-- Units -->
                    <div class="form-section">
                        <h3>Would you measure {{form.name}} using...</h3>

                        <small class="error-msg" v-if="error.units">{{error.units[0]}}</small>

                        <!--loop unit types-->
                        <div class="ingredient__units" v-for="(unitType, unitTypeName) in unitTypes">

                            <div class="ingredient__unit">
                                <input :id="unitTypeName" type="checkbox" class="checkbox"
                                       v-model="unitType.checked" @change="setUnitsFromUnitTypes()">
                                <label :for="unitTypeName" class="ingredient__label">{{ unitType.label }}</label>
                            </div>

                            <div class="vert-form-group" v-if="unitType.term && unitType.checked">

                                    <label :for="unitTypeName + '_weight'" class="ingredient__label vert-form-group__label">
                                        How much does {{unitType.term}} weight?
                                        <a :href="getWeightUrl(unitType)" :title="getWeightTitle(unitType)" target="_blank" v-if="form.name">
                                            <i class="fa fa-question-circle" aria-hidden="true"></i>
                                        </a>
                                    </label>

                                    <input :id="unitTypeName + '_weight'" type="input" class="ingredient__weight"
                                           v-model="form[unitType.key]" @change="setUnitsFromUnitTypes()">
                                    <span class="unit-types__unit" title="Grams">g</span>

                            </div>

                            <div class="row unit-types__error-row" v-if="unitType.term && unitType.checked">
                                <small class="error-msg" v-if="error[unitType.key]">{{error[unitType.key][0]}}</small>
                            </div>

                        </div>
                    </div>

                    <div class="form-section" v-if="Object.keys(form.units).length">

                        <div class="form-group">
                            <label class="form-group__label form-group__label--big ingredient__label" for="default-unit-id">Default unit id</label>
                            <select type="text" class="form-group__select form-group__select--small" v-model="form.default_unit_id" id="default-unit-id">
                                <option v-for="(unit, index) in form.units" :value="unit.id">{{unit.name.replace('_', ' ')}}</option>
                            </select>
                        </div>


                    </div>
                    <div class="row unit-types__error-row" v-if="error.default_unit_id">
                        <small class="error-msg">{{error.default_unit_id[0]}}</small>
                    </div>
                </div>
            </div>

            <!-- Nutrients 1 -->
            <div class="col col--1">

                <div class="panel">

                    <h3>Nutritional information (per {{nutritionPer}}g)</h3>

                    <button @click="searchNdb()" :disabled="form.name.length ? false : true" class="btn">Autofill</button>

                    <h4>Energy</h4>
                    <nutrients-form
                            :attributeTypes="attributeTypes"
                            :form="form"
                            :category="'other'">
                    </nutrients-form>

                    <h4>Macronutrients</h4>
                    <nutrients-form
                        :attributeTypes="attributeTypes"
                        :form="form"
                        :category="'macronutrients'">
                    </nutrients-form>

                    <h4>Minerals</h4>
                    <nutrients-form
                            :attributeTypes="attributeTypes"
                            :form="form"
                            :category="'minerals'">
                    </nutrients-form>

                </div>

            </div>

            <!-- Nutrients 2 -->
            <div class="col col--1">

                <div class="panel">
                    <h3>Vitamins</h3>
                    <nutrients-form
                            :attributeTypes="attributeTypes"
                            :form="form"
                            :category="'vitamins'">
                    </nutrients-form>
                </div>

            </div>
        </div>



        <!-- Select NDB modal -->
        <modal :show="ndb.showPopup" @close="ndb.showPopup = false" :activeProp="ndb.popupActive">

            <h1 class="modal-title" slot="title">Pick ingredient to load nutrients info</h1>

            <ndb-ingredients :ndb="ndb"
                             :ingredient="form.name"
                             @close="ndb.showPopup = false"
                             @updateNutrients="updateNutrients">
            </ndb-ingredients>

        </modal>

    </div>
</template>

<script type="text/javascript">
    import Vue from 'vue';
    import Flash from '../../helpers/flash';
    import { get, post } from '../../helpers/api';
    import { convertEnergyUnit, formatNumber, getUnit } from '../../helpers/misc';
    import { toMulipartedForm, objectToFormData } from '../../helpers/form';
    import Modal from '../../components/Modal.vue';
    import NutrientsForm from '../../components/NutrientsForm.vue';
    import NdbIngredients from '../../components/NdbIngredients.vue';
    import { EventBus } from '../../event-bus';

    export default {
        components: {
            Modal,
            NutrientsForm,
            NdbIngredients,
        },
        props: {
            inModal: {
                type: [Boolean],
                default: false,
            },
            initialName: {
                type: [String],
                default: '',
            },
            showHeader: {
                type: [Boolean],
                default: true,
            },
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
                    name: '',
                    groups: {},
                    showPopup: false,
                    popupActive: false,
                },
                energyUnit: 'calorie',
                nutritionPer: 100, // grams
            }
        },
        created() {
            this.init();

            EventBus.$on('saveIngredient', value => {
                this.save();
            });
        },
        mounted() {
            this.$refs.name.focus();
        },
        watch: {
            '$route' (to, from) {
                console.log('route change');
                this.init();
            }
        },
        methods: {
            init() {

                EventBus.$emit('contentLoading', true);

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

                        if(this.form.name.length === 0 && this.initialName.length > 0) {
                            this.form.name = this.initialName;

                        }

                        this.setUnitTypesFromUnits();
                        EventBus.$emit('contentLoading', false);
                        this.$emit('finishedLoading');
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
                let loadingTarget = this.inModal ? 'modalLoading' : 'contentLoading';
                EventBus.$emit(loadingTarget, true);
                get('/api/ndb/search/' + this.form.name)
                    .then((res) => {
                        EventBus.$emit(loadingTarget, false);
                        this.ndb.groups = res.data.groups;
                        this.ndb.showPopup = true;
                        // Tell other modals to become inactive
                        EventBus.$emit('modalActive', false);

                        this.ndb.popupActive = true;

                        console.log('emit modalInactive');
                    })
                    .catch(function (error) {
                        EventBus.$emit(loadingTarget, false);
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
                EventBus.$emit('contentLoading', true);

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
                        EventBus.$emit('contentLoading', false);
                        Flash.setSuccess('Ingredient saved.');

                        if(this.inModal) {
                            let item = {
                                id: res.data.id,
                                name: data.name,
                                default_unit_id: data.default_unit_id,
                            };
                            EventBus.$emit('ingredientSaved', item);
                            this.$emit('close');
                        } else {
                            this.$router.push('/ingredients');
                        }
                    })
                    .catch((err) => {
                        EventBus.$emit('contentLoading', false);
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
            },
            cancelClick() {
                if(this.inModal) {
                    this.$emit('close');
                } else {
                    this.$router.back();
                }
            }
        },
    }
</script>

<style lang="scss">
    @import "../../../sass/variables/breakpoints";

    .ingredient__weight {
        width: 6rem;
    }

    .ingredient__label {
        font-size: 1.4rem;

        @include mq($from: xl) {

        }
    }

    .ingredient__units {
        border-bottom: 1px solid darken(lightgrey, 10);
        margin-bottom: 1rem;
    }

    .ingredient__unit {
        display: flex;
        margin-bottom: 1rem;
    }

</style>
<template>
    <transition name="modal">
        <div class="modal-mask">
            <div class="modal-wrapper">
                <div class="modal-container" :class="modalLoading ? 'loading' : ''">

                    <div class="modal-header">
                        Edit {{ingredient.attributes.name}}
                    </div>

                    <div class="modal-body ingredient-form">

                        <div class="form__container">
                            <div class="form__group">
                                <label>Name</label>
                                <input type="text" class="form__control" v-model="ingredient.attributes.name">
                                <small class="error__control" v-if="error.name">{{error.name[0]}}</small>
                            </div>
                            <div class="form__group">
                                <label>Weight (grams)</label>
                                <input type="text" class="form__control" v-model="ingredient.attributes.weight">
                                <small class="error__control" v-if="error.weight">{{error.weight[0]}}</small>
                            </div>
                            <div class="form__group">
                                <label>Matching NDB item</label>
                                <input type="text" class="form__control" disabled v-model="ndb.name">
                                <input type="button" value="Search" @click="searchNdb">
                            </div>

                            <!--<div class="form__group">-->
                                <!--<label>Description</label>-->
                                <!--<textarea class="form__control form__description" v-model="ingredient.attributes.description"></textarea>-->
                                <!--<small class="error__control" v-if="error.description">{{error.description[0]}}</small>-->
                            <!--</div>-->
                        </div>

                        <div class="form__container form__container--units">

                            <p class="form__title">Would you measure {{ingredient.attributes.name}} using...</p>

                            <div class="form__group form__group--inline">
                                <label>Volume (Litres or cups)</label>
                                <input type="checkbox" class="form__control" v-model="ingredient.volume" @change="updateDefaultUnitOptions()">
                            </div>
                            <div class="form__group form__group--inline">
                                <label>Weight (Kilograms or pounds)</label>
                                <input type="checkbox" class="form__control" v-model="ingredient.weight" @change="updateDefaultUnitOptions()">
                            </div>
                            <div class="form__group form__group--inline">
                                <label>Distance (Centimeters or inches)</label>
                                <input type="checkbox" class="form__control" v-model="ingredient.distance" @change="updateDefaultUnitOptions()">
                            </div>
                            <div class="form__group form__group--inline">
                                <label>Quantity (Count how many)</label>
                                <input type="checkbox" class="form__control" v-model="ingredient.quantity" @change="updateDefaultUnitOptions()">
                            </div>
                            <div class="form__group form__group--inline form__group--select">
                                <label>Default unit id</label>
                                <select type="text" class="form__control" v-model="ingredient.attributes.default_unit_id">
                                    <option v-for="(unit, index) in default_unit_options" :value="unit.id">{{unit.name}}</option>
                                </select>
                            </div>
                        </div>

                        <div class="form__container form__container--nutrition" :class="nutritionUpdating ? 'loading' : ''">
                            <p class="form__title">Nutritional information (per {{nutritionPer}}g)</p>

                            <button @click="searchNdb()">Autofill</button>

                            <div v-for="(type, index) in attribute_types" class="form__group">
                                <div class="form__group form__group--inline">

                                    <label v-if="type.attributes.name==='energy'">
                                        <!--<select v-model="energyUnit" @change="convertIngredientFormEnergyUnit()">-->
                                            <!--<option value="calorie">Calories</option>-->
                                            <!--<option value="kj">Kj</option>-->
                                        <!--</select>-->
                                        Energy
                                        <a :href="getSearchUrl(type)" target="_blank">
                                            <i class="fa fa-question-circle" aria-hidden="true"></i>
                                        </a>
                                    </label>
                                    <label v-else>
                                        {{capitalize(type.attributes.name)}} ({{type.attributes.unit}})
                                        <a :href="getSearchUrl(type)" target="_blank">
                                            <i class="fa fa-question-circle" aria-hidden="true"></i>
                                        </a>
                                    </label>

                                    <input type="text" class="form__control"
                                           v-model="ingredient.ingredientAttributes[type.attributes.safe_name].value">
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <slot name="footer">
                            <button class="modal-default-button" @click="save()">
                                Save
                            </button>
                        </slot>
                    </div>

                    <div class="modal-overlay" v-if="Object.keys(ndb.groups).length">
                        <div class="modal-header">
                            Possible NDB matches
                        </div>
                        <div class="modal-body">
                            <ul>
                                <li v-for="(group, key, index) in ndb.groups">

                                    <span class="group">{{key}}</span>

                                    <ul>
                                        <li v-for="(item, index) in group">
                                            <a @click="selectNdbIngredient(item)">{{item.name}}</a>
                                        </li>
                                    </ul>


                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </transition>
</template>
<script type="text/javascript">
    import { convertEnergyUnit } from '../../helpers/convert';
    import { get, post, getExernal } from '../../helpers/api';

    export default {
        components: {
        },
        props: {
            ingredient: {
                name: '',
                description: '',
                default_unit_id: '',
                units: [],
                attributes: [],
                volume: false,
                weight: false,
                distance: false,
                quantity: false,
            },
            attribute_types: Array,
            units: Array
        },
        data() {
            return {
                error: {},
//                attributes: {},
                nutritionPer: 100, // grams
                energyUnit: 'calorie',
                storeURL: '/api/ingredients/%',
                energyExactValue: 0,
                default_unit_options: [],
                nutrientsApiKey: 'Om7a9m8XAvN0NzN8TpcuMXXcHZCvQlg4MvRL3BJJ',
                ndb: {
                    'name': '',
                    'groups': {},
                },
                modalLoading: false,
                nutritionUpdating: false,
            }
        },
        created() {

            this.ingredient.volume = false;
            this.ingredient.weight = false;
            this.ingredient.distance = false;
            this.ingredient.quantity = false;

            this.storeURL = '/api/ingredients/' + this.ingredient.id;

            // Set unit type default values
            for (let i = 0; i < this.ingredient.units.length; i++) {
                let unit = this.ingredient.units[i];
                switch(unit.unitType) {
                    case 'volume':   this.ingredient.volume = true;   break;
                    case 'weight':   this.ingredient.weight = true;   break;
                    case 'distance': this.ingredient.distance = true; break;
                    case 'quantity': this.ingredient.quantity = true; break;
                }
            }

            // Set attributes default values
//            for (let i = 0; i < this.ingredient.ingredientAttributes.length; i++) {
//                let attribute = this.ingredient.ingredientAttributes[i];
//
//                let attributeType = attribute.attributes.attributeType;
//
//                if(attributeType.safe_name == 'energy') {
//                    // default energy unit it calorie so convert from Kj
//                    let value = attribute.attributes.value * this.nutritionPer;
//                    this.energyExactValue = convertEnergyUnit(value, 'calorie');
//                    this.attributes[attributeType.safe_name] = this.energyExactValue.toFixed(0);
//                } else {
//                    this.attributes[attributeType.safe_name] = Math.round(attribute.attributes.value * this.nutritionPer);
//                }
//            }


            // Set empty strings for any attributes that do not have a value yet
            for (let i = 0; i < this.attribute_types.length; i++) {
                let attribute_type = this.attribute_types[i];
                if(typeof this.ingredient.ingredientAttributes[attribute_type.attributes.safe_name] === 'undefined') {
                    this.ingredient.ingredientAttributes[attribute_type.attributes.safe_name] = {
                        id: null,
                        value: '',
                        type_id: attribute_type.id,
                        type_name: attribute_type.attributes.name,
                    };
                }
            }


            this.updateDefaultUnitOptions();

        },
        methods: {
            capitalize(string) {
                return string.charAt(0).toUpperCase() + string.slice(1);
            },
            convertIngredientFormEnergyUnit() {
                this.energyExactValue  = convertEnergyUnit(this.energyExactValue, this.energyUnit);
                this.attributes['energy'] = this.energyExactValue.toFixed(0);
            },
            save() {

                let data = {
                    id: this.ingredient.id,
                    name: this.ingredient.attributes.name,
                    description: this.ingredient.attributes.description,
                    weight: this.ingredient.attributes.weight,
                    default_unit_id: this.ingredient.attributes.default_unit_id,
                    units: [],
                    ingredientAttributes: [],
                    _method: 'PUT',
                };

                // Set units
                for (let x = 0; x < this.ingredient.units.length; x++) {
                    let ingredientUnitType = this.ingredient.units[x].unitType;
                    for (let i = 0; i < this.units.length; i++) {
                        let unit = this.units[i];
                        if(unit.attributes.unitType == ingredientUnitType) {
                            data.units.push(unit.id);
                        }
                    }
                }

                // Set attributes
                for (var safe_name in this.ingredient.ingredientAttributes) {
                    if (this.ingredient.ingredientAttributes.hasOwnProperty(safe_name)) {
                        let attribute = this.ingredient.ingredientAttributes[safe_name];

                        data.ingredientAttributes.push({
                            id: attribute.id,
                            value: attribute.value,
                            attribute_type_id: attribute.type_id,
                        });
                    }
                }

                post(this.storeURL, data)
                    .then((res) => {

                        if(res.data.saved) {
                            console.log('emit close');
                            this.$emit('close');
                        }
                        this.isProcessing = false
                    })
                    .catch((err) => {
                        if(typeof err.response !== 'undefined' && err.response.status === 422) {
                            this.error = err.response.data
                        }
                        this.isProcessing = false
                    })

            },
            updateDefaultUnitOptions() {
                this.default_unit_options = [];
                for (let i = 0; i < this.ingredient.units.length; i++) {
                    let unit = this.ingredient.units[i];
                    if(this.ingredient[unit.unitType]) {
                        let name = unit.name == 'whole' ? 'quantity' : unit.name;
                        this.default_unit_options.push({id: unit.id, name: name});
                    }
                }
            },
//            updateIngredientAttribute(safe_name) {
//                let foundAttribute = false;
//                let newValue = this.attributes[safe_name] / this.nutritionPer;
//
//                // Loop ingredient attributes to find the matching safe_name
//                for (let i = 0; i < this.ingredient.ingredientAttributes.length; i++) {
//                    let ingredientAttribute = this.ingredient.ingredientAttributes[i];
//                    if(ingredientAttribute.attributes.attributeType.safe_name == safe_name) {
//                        foundAttribute = true;
//                        ingredientAttribute.attributes.value = newValue;
//
//                        // Update the exact value
//                        if(safe_name == 'energy') {
//                            this.energyExactValue = newValue;
//                        }
//                    }
//                }
//
//                if(!foundAttribute) {
//
//                    let attributeType = this.getAttributeType(safe_name);
//
//                    let unitGramID = 6; // do we really need to pass this around if it's always gram?
//
//                    this.ingredient.ingredientAttributes.push({
//                        attributes: {
//                            attributeType: {
//                                id: attributeType.id,
//                                name: attributeType.attributes.name,
//                                safe_name: attributeType.attributes.safe_name,
//                                unit: attributeType.attributes.unit,
//                            },
//                            unit: 'gram',
//                            value: newValue,
//                        },
//                        relationships: {
//                            ingredient: {
//                                data: {id: this.ingredient.id, type: 'ingredient'},
//                            },
//                            unit: {
//                                data: {id: unitGramID, type: 'unit'},
//                            },
//                        },
//                    });
//                }
//            },
            getAttributeType(safe_name) {
                for (let i = 0; i < this.attribute_types.length; i++) {
                    let attributeType = this.attribute_types[i];
                    if(attributeType.attributes.safe_name === safe_name) {
                        return attributeType;
                    }
                }
            },
            getSearchUrl(type) {
                let attribute = type.attributes.name === 'energy' ? this.energyUnit : type.attributes.name;
                return 'https://www.google.com.au/search?q=how+many+' +
                    attribute +
                    '+are+there+in+100g+of+' + this.ingredient.attributes.name;
            },
            hintModal(type) {
                //this.hintModalUrl = this.getSearchUrl(type);
                // this.doSearch();
            },
            searchNdb() {
                this.modalLoading = true;
                get('/api/ndb/search/' + this.ingredient.attributes.name)
                    .then((res) => {
                        this.modalLoading = false;
                        this.ndb.groups = res.data.groups;
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            },
            selectNdbIngredient(item) {
                this.nutritionUpdating = true;

                this.ndb.groups = {};
                get('/api/ndb/view/' + item.id)
                    .then((res) => {

                        this.nutritionUpdating = false;
                        this.energyUnit = 'calorie';

                        for (let i = 0; i < res.data.length; i++) {
                            let row = res.data[i];
                            this.ingredient.ingredientAttributes[row.attribute_safe_name].value = row.value;
                        }

                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            }
        }
    }
</script>

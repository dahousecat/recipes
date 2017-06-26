<template>
    <transition name="modal">
        <div class="modal-mask">
            <div class="modal-wrapper">
                <div class="modal-container">

                    <div class="modal-header">
                        Edit {{ingredient.attributes.name}}
                    </div>

                    <div class="modal-body">

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

                            <!--<div class="form__group">-->
                                <!--<label>Description</label>-->
                                <!--<textarea class="form__control form__description" v-model="ingredient.attributes.description"></textarea>-->
                                <!--<small class="error__control" v-if="error.description">{{error.description[0]}}</small>-->
                            <!--</div>-->
                        </div>

                        <div class="form__container">
                            <p>Would you measure {{ingredient.attributes.name}} using...</p>

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

                            <div class="form__group">
                                <label>Default unit id</label>
                                <select type="text" class="form__control" v-model="ingredient.attributes.default_unit_id">
                                    <option v-for="(unit, index) in default_unit_options" :value="unit.id">{{unit.name}}</option>
                                </select>
                            </div>
                        </div>

                        <div class="form__container">
                            <p>Nutritional information (per {{nutritionPer}}g)</p>
                            <div v-for="(type, index) in attribute_types" class="form__group">
                                <div class="form__group form__group--inline">

                                    <label v-if="type.attributes.name==='energy'">
                                        <select v-model="energyUnit" @change="convertIngredientFormEnergyUnit()">
                                            <option value="calorie">Calories</option>
                                            <option value="kj">Kj</option>
                                        </select>
                                    </label>
                                    <label v-else>{{capitalize(type.attributes.name)}} ({{type.attributes.unit}})</label>

                                    <input type="text" class="form__control"
                                           v-model="attributes[type.attributes.safe_name]"
                                           @change="updateIngredientAttribute(type.attributes.safe_name)">
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
                </div>
            </div>
        </div>
    </transition>
</template>
<script type="text/javascript">
    import { convertEnergyUnit } from '../../helpers/convert';
    import { get, post } from '../../helpers/api';

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
                attributes: {},
                nutritionPer: 100, // grams
                energyUnit: 'calorie',
                storeURL: '/api/ingredients/%',
                energyExactValue: 0,
                default_unit_options: [],
            }
        },
        created() {

            console.log(this.ingredient, 'this.ingredient created');

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
            for (let i = 0; i < this.ingredient.ingredientAttributes.length; i++) {
                let attribute = this.ingredient.ingredientAttributes[i];

                let attributeType = attribute.attributes.attributeType;

                if(attributeType.safe_name == 'energy') {
                    // default energy unit it calorie so convert from Kj
                    let value = attribute.attributes.value * this.nutritionPer;
                    this.energyExactValue = convertEnergyUnit(value, 'calorie');
                    this.attributes[attributeType.safe_name] = this.energyExactValue.toFixed(0);
                } else {
                    this.attributes[attributeType.safe_name] = Math.round(attribute.attributes.value * this.nutritionPer);
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
                for (let x = 0; x < this.ingredient.ingredientAttributes.length; x++) {
                    let attribute = this.ingredient.ingredientAttributes[x];
                    let attributeType = attribute.attributes.attributeType;
                    let value;

                    if(attributeType.name === 'energy') {
                        value = this.energyExactValue;
                        if(this.energyUnit === 'calorie') {
                            value = convertEnergyUnit(value, 'kj');
                        }
                    } else {
                        value = attribute.attributes.value;
                    }

                    data.ingredientAttributes.push({
                        id: attribute.id,
                        unit_id: attribute.relationships.unit.data.id,
                        value: value,
                        attribute_type_id: attribute.attributes.attributeType.id,
                    });
                }

                console.log(data, 'data');

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
            updateIngredientAttribute(safe_name) {
                let foundAttribute = false;
                let newValue = this.attributes[safe_name] / this.nutritionPer;

                // Loop ingredient attributes to find the matching safe_name
                for (let i = 0; i < this.ingredient.ingredientAttributes.length; i++) {
                    let ingredientAttribute = this.ingredient.ingredientAttributes[i];
                    if(ingredientAttribute.attributes.attributeType.safe_name == safe_name) {
                        foundAttribute = true;
                        ingredientAttribute.attributes.value = newValue;

                        // Update the exact value
                        if(safe_name == 'energy') {
                            this.energyExactValue = newValue;
                        }
                    }
                }

                if(!foundAttribute) {

                    let attributeType = this.getAttributeType(safe_name);

                    let unitGramID = 6; // do we really need to pass this around if it's always gram?

                    this.ingredient.ingredientAttributes.push({
                        attributes: {
                            attributeType: {
                                id: attributeType.id,
                                name: attributeType.attributes.name,
                                safe_name: attributeType.attributes.safe_name,
                                unit: attributeType.attributes.unit,
                            },
                            unit: 'gram',
                            value: newValue,
                        },
                        relationships: {
                            ingredient: {
                                data: {id: this.ingredient.id, type: 'ingredient'},
                            },
                            unit: {
                                data: {id: unitGramID, type: 'unit'},
                            },
                        },
                    });
                }
            },
            getAttributeType(safe_name) {
                for (let i = 0; i < this.attribute_types.length; i++) {
                    let attributeType = this.attribute_types[i];
                    if(attributeType.attributes.safe_name === safe_name) {
                        return attributeType;
                    }
                }
            }
        }
    }
</script>

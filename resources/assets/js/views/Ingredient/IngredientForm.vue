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
                                <label>Description</label>
                                <textarea class="form__control form__description" v-model="ingredient.attributes.description"></textarea>
                                <small class="error__control" v-if="error.description">{{error.description[0]}}</small>
                            </div>
                        </div>

                        <div class="form__container">
                            <p>Would you measure {{ingredient.attributes.name}} using...</p>

                            <div class="form__group form__group--inline">
                                <label>Volume (Litres or cups)</label>
                                <input type="checkbox" class="form__control" v-model="ingredient.volume">
                            </div>
                            <div class="form__group form__group--inline">
                                <label>Weight (Kilograms or pounds)</label>
                                <input type="checkbox" class="form__control" v-model="ingredient.weight">
                            </div>
                            <div class="form__group form__group--inline">
                                <label>Distance (Centimeters or inches)</label>
                                <input type="checkbox" class="form__control" v-model="ingredient.distance">
                            </div>
                            <div class="form__group form__group--inline">
                                <label>Quantity (Count how many)</label>
                                <input type="checkbox" class="form__control" v-model="ingredient.quantity">
                            </div>
                        </div>

                        <div class="form__container">
                            <p>Nutritional information (per {{nutritionPer}}g)</p>
                            <div v-for="(type, index) in attribute_types" class="form__group">
                                <div class="form__group form__group--inline">
                                    <label>{{capitalize(type.attributes.name)}} ({{type.attributes.unit}})</label>
                                    <input type="text" class="form__control" v-model="attributes[type.id]">
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <slot name="footer">
                            <button class="modal-default-button" @click="$emit('close')">
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
        },
        data() {
            return {
                error: {},
                attributes: {},
                nutritionPer: 100, // grams
            }
        },
        created() {

            console.log(this.ingredient, 'this.ingredient created');

            this.ingredient.volume = false;
            this.ingredient.weight = false;
            this.ingredient.distance = false;
            this.ingredient.quantity = false;

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
                this.attributes[attribute.id] = attribute.attributes.value * this.nutritionPer;
            }

        },
        methods: {
            capitalize(string) {
                return string.charAt(0).toUpperCase() + string.slice(1);
            }
        }
    }
</script>

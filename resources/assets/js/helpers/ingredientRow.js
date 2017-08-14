import { getUnit } from '../helpers/misc';

export function calculateRowNutrition(row) {

    console.log('Calculate row nutrition for ' + row.ingredient.name);

    if(typeof row.unit === 'undefined' || row.unit_id !== row.unit.id) {
        // The rows unit id has changed, reload the unit.
        row.unit = getUnit(row.unit_id, row.ingredient.units);
    }

    // we know how this row is being measured, so we can work out how much this row weights
    switch(row.unit.type) {
        case 'weight':
            // If not grams then convert to grams
            row.weight = row.unit.name === 'grams' ? row.value : row.value * row.unit.gram;
            break;
        case 'length':
            let cm = (row.value * row.unit.mm) / 10;
            row.weight = row.ingredient.weight_one_cm * cm;
            break;
        case 'volume':
            let ml = row.value * row.unit.ml;
            row.weight = row.ingredient.weight_one_ml * ml;
            break;
        case 'quantity':
            row.weight = row.ingredient.weight_one * row.value;
            break;
    }

    // Loop ingredient nutrients to set row nutrients
    for (let nutrientName in row.ingredient.nutrients) {
        if (row.ingredient.nutrients.hasOwnProperty(nutrientName)) {
            let ingredientNutrient = row.ingredient.nutrients[nutrientName];

            if(typeof row.nutrients === 'undefined') {
                row.nutrients = {};
            }

            // Nutrients are stored per 100g so divide by 100 first
            row.nutrients[nutrientName] = {
                'value': (ingredientNutrient.value / 100) * row.weight,
                'unit': ingredientNutrient.attribute_type.unit,
                'name': ingredientNutrient.attribute_type.name,
                'category': ingredientNutrient.attribute_type.category,
            };
        }
    }

}
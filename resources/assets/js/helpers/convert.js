export function convertEnergyUnit(old_value, new_unit) {
    let new_value;
    let caloriesInKj = 0.239006;
    if(new_unit === 'calorie') {
        // convert from Kj to calorie
        new_value = old_value * caloriesInKj;
    } else {
        // convert from calorie to Kj
        new_value = old_value / caloriesInKj;
    }
    return new_value;
}

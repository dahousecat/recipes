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

export function formatNumber(number) {
    let dp = number < 1 ? 1 : 0;
    return number.toFixed(dp);
}

export function getUnit(id, units) {
    id = parseInt(id);
    for (let i = 0; i < units.length; i++) {
        if(parseInt(units[i].id) === id) {
            return units[i];
        }
    }
}
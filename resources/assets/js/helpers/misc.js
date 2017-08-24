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
    let dp = number < 1 ? 2 : 1;

    // the plus here is converting back to a float so any additional zeros are stripped off the end
    // https://stackoverflow.com/a/12830454/967168
    return +number.toFixed(dp);
}

export function getUnit(id, units) {
    id = parseInt(id);
    if(typeof units === 'object') {
        for (let unitName in units) {
            if (units.hasOwnProperty(unitName)) {
                let unit = units[unitName];
                if(parseInt(unit.id) === id) {
                    return unit;
                }
            }
        }
    } else {
        for (let i = 0; i < units.length; i++) {
            if(parseInt(units[i].id) === id) {
                return units[i];
            }
        }
    }
}

export function capitalize(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

export function isNumeric(n) {
    return !isNaN(parseFloat(n)) && isFinite(n);
}

export function isURL(str) {
    var pattern = new RegExp('^(https?:\\/\\/)?'+ // protocol
        '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.?)+[a-z]{2,}|'+ // domain name
        '((\\d{1,3}\\.){3}\\d{1,3}))'+ // OR ip (v4) address
        '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*'+ // port and path
        '(\\?[;&a-z\\d%_.~+=-]*)?'+ // query string
        '(\\#[-a-z\\d_]*)?$','i'); // fragment locator
    return pattern.test(str);
}
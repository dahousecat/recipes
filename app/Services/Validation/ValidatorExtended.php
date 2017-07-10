<?php
namespace App\Services\Validation;

use Illuminate\Validation\Validator as IlluminateValidator;
use App\Models\Unit;

/**
 * Provide additional validation rules.
 *
 * Class Validation
 * @package app\Services
 */
class ValidatorExtended extends IlluminateValidator
{

    private $_custom_messages = array(
        'required_if_volume_unit' => 'Please enter how much one cup weighs',
        'required_if_quantity_unit' => 'Please enter how much one weighs',
        'required_if_length_unit' => 'Please enter how much one cm weighs',
        'positive' => ':attribute must be a number greater than 0',
        'units.required' => 'Please choose at least one way to measure this ingredient.',
    );

    public function __construct($translator, $data, $rules, $messages = array(), $customAttributes = array()) {
        parent::__construct($translator, $data, $rules, $messages, $customAttributes);

        $this->setCustomMessages($this->_custom_messages);
    }

    /**
     * If any unit has type volume make sure we have weight per cup
     */
    public function validateRequiredIfVolumeUnit($attribute, $value, $parameters)
    {
        return $this->validateRequiredIfTypeUnit('volume', $value);
    }

    /**
     * If any unit has type quantity make sure we have how much one weights
     */
    public function validateRequiredIfQuantityUnit($attribute, $value, $parameters)
    {
        return $this->validateRequiredIfTypeUnit('quantity', $value);
    }

    /**
     * If any unit has type length make sure we know much much one centimeter weights
     */
    public function validateRequiredIfLengthUnit($attribute, $value, $parameters)
    {
        return $this->validateRequiredIfTypeUnit('length', $value);
    }

    /**
     * If any unit has type length make sure we know much much one centimeter weights
     */
    public function validateNullableNumeric($attribute, $value, $parameters)
    {
        // Empty values are allowed
        if(empty($value)) {
            return true;
        }

        // Numeric not empty values are allowed
        if(is_numeric($value)) {
            return true;
        }

        // Everything else fails. Error!
        return false;
    }

    public function validatePositive($attribute, $value, $parameters) {

        // Empty values are allowed
        if(empty($value)) {
            return true;
        }

        // Must be numeric
        if(!is_numeric($value)) {
            return false;
        }

        // Value cannot be less than 0
        if($value < 0) {
            return false;
        }

        // Must be a positive number so all good
        return true;
    }

    /**
     * Check if there is a unit of type for the type provided.
     *
     * @param $type
     * @param $value
     * @return bool
     */
    private function validateRequiredIfTypeUnit($type, $value) {
        // Collect the type of the units for this ingredient
        $types = Unit::getTypes($this->data['units']);
        if(in_array($type, $types) && empty($value)) {
            // This data is not valid
            return false;
        }
        // There is no error
        return true;
    }

}

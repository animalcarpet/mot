<?php
namespace DvsaCommon\Messages\Vehicle;

/**
 * Errors for vehicle creation
 */
class CreateVehicleErrors
{
    const MISSING_PARAM = "Missing param: '%s' ";

    //
    const COUNTRY_EMPTY = 'you must choose a country of registration';

    const DATE_EMPTY = 'you must enter a date';
    const DATE_INVALID = 'must be a valid date. For example, 31 01 2014.';
    const DATE_MIN = 'must not be before %d';
    const DATE_MAX = 'must not be in the future';

    const MAKE_EMPTY = 'you must choose a manufacturer';
    const MAKE_OTHER_EMPTY = 'you must enter a manufacturer';
    const MAKE_OTHER_MAX = 'must be less than %d characters long';
    const MAKE_OTHER = 'only enter a new manufacturer if you choose \'other\' from the list of manufacturers';
    const TRANSMISSION_EMPTY = 'you must choose a transmission';

    //
    const BOTH_REG_AND_VIN_EMPTY  = ' you must enter a registration mark or a VIN';

    const REG_EMPTY = 'you must enter a registration mark or choose a reason for not supplying one';
    const REG_INVALID = 'must only contain letters and numbers';
    const REG_TOO_LONG = 'must be %d characters or less for vehicles not registered in the UK';
    const REG_TOO_LONG_FOR_UK = 'must be %d characters or less for vehicles registered in the UK';
    const REG_TOO_LONG_NO_COUNTRY = 'must be %d characters or less';
    const EMPTY_REG_REASON_REQUIRED   = 'you must choose a reason';
    const EMPTY_REG_REASON_NOT_PERMITTED = 'you have provided a registration mark and chosen not to supply a registration mark. To continue, either, remove the reason or remove the registration mark.';

    const VIN_EMPTY = 'you must enter a full VIN or chassis number or choose a reason for not supplying one';
    const VIN_INVALID = 'must be a valid full VIN or chassis number';
    const VIN_TOO_LONG = 'must be %d characters or less';
    const VIN_LENGTH  = 'must be 20 characters or less';
    const EMPTY_VIN_REASON_REQUIRED = 'you must choose a reason';
    const EMPTY_VIN_REASON_NOT_PERMITTED = 'you have provided a VIN and chosen not to supply a VIN. To continue, either, remove the reason or remove the VIN.';

    //
    const CC_EMPTY = 'you must enter a cylinder capacity';
    const CC_INVALID = 'must be a valid capacity. For example, 1400.';
    const CC_NOT_BETWEEN = 'must be less than %d';

    const CLASS_EMPTY = 'you must choose a class';
    const CLASS_INVALID = "'%s' is invalid value for vehicle class";
    const CLASS_PERSON = 'you are not eligible to test class %d vehicles';
    const CLASS_VTS = 'this VTS is not eligible to test class %d vehicles';

    const COLOUR_EMPTY = 'you must choose a primary colour';

    const FUEL_TYPE_EMPTY = 'you must choose a fuel type';

    const MODEL_EMPTY = 'you must choose a model';
    const MODEL_OTHER_EMPTY = 'you must enter a model';
    const MODEL_OTHER = 'only enter a new model if you choose \'other\' from the list of models';
    const MODEL_MAX = 'must be less than %d characters long';
}

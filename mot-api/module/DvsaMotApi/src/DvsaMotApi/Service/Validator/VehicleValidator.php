<?php

namespace DvsaMotApi\Service\Validator;

use DvsaCommon\Date\DateUtils;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Messages\Vehicle\CreateVehicleErrors as Errors;
use DvsaCommon\Model\FuelTypeAndCylinderCapacity;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApi\Service\Exception\RequiredFieldException;
use DvsaCommonApi\Service\Validator\AbstractValidator;

/**
 * Class VehicleValidator.
 */
class VehicleValidator extends AbstractValidator
{
    const LIMIT_CC_MIN = 0.1;
    const LIMIT_CC_MAX = 10000;
    const MODEL_OTHER = 'OTHER';
    const MAKE_OTHER = 'OTHER';

    const LIMIT_REG_MAX = 13;
    const LIMIT_VIN_MAX = 20;

    const MIN_DATE = '1800-01-01';

    private static $requiredFields
        = [
            'make',
            'model',
            'colour',
            'secondaryColour',
            'dateOfFirstUse',
            'fuelTypeCode',
            'testClass',
            'countryOfRegistration',
            'transmissionType',
            'vtsId',
        ];

    public static function getRequiredFields()
    {
        return self::$requiredFields;
    }

    public function validate($data)
    {
        $requiredFields = self::$requiredFields;

        $isCylinderCapacityCompulsory =
            FuelTypeAndCylinderCapacity::isCylinderCapacityCompulsoryForFuelTypeCode($data['fuelTypeCode']);

        if ($isCylinderCapacityCompulsory) {
            $requiredFields[] = 'cylinderCapacity';
        }

        RequiredFieldException::CheckIfRequiredFieldsNotEmpty($requiredFields, $data);

        $this->validateDateOfFirstUse($data['dateOfFirstUse']);

        if ($isCylinderCapacityCompulsory) {
            $this->validateCylinderCapacity($data['cylinderCapacity']);
        }

        $vrmAndVinValidator = new VehicleVrmVinValidator();
        $vrmAndVinValidator->validate($data, $this->errors);

        if (VehicleClassCode::exists((string) $data['testClass']) === false) {
            $this->errors->add(sprintf(Errors::CLASS_INVALID, $data['testClass']), 'testClass');
        }

        if (!array_key_exists('makeOther', $data)) {
            $this->errors->add(sprintf(Errors::MISSING_PARAM, 'makeOther'), 'makeOther');
        }

        if (!array_key_exists('modelOther', $data)) {
            $this->errors->add(sprintf(Errors::MISSING_PARAM, 'modelOther'), 'modelOther');
        }

        $makeOther = ArrayUtils::tryGet($data, 'makeOther');
        $make = $data['make'];
        $this->validateMake($make, $makeOther);

        $modelOther = ArrayUtils::tryGet($data, 'modelOther');
        $this->validateModel($data['model'], $modelOther, $make);

        $this->errors->throwIfAny();
    }

    public function validateDateOfFirstUse($dateOfFirstUse)
    {
        try {
            $date = DateUtils::toDate($dateOfFirstUse);
            if (DateUtils::isDateInFuture($date)) {
                $this->errors->add(Errors::DATE_MAX);
            } elseif ($date < date_create(self::MIN_DATE)) {
                $this->errors->add(sprintf(Errors::DATE_MIN, self::MIN_DATE));
            }
        } catch (\Exception $e) {
            $this->errors->add(Errors::DATE_INVALID, 'dateOfFirstUse');
        }
    }

    public function validateCylinderCapacity($cylinderCapacity)
    {
        if ($cylinderCapacity !== '0' && !preg_match('/^[1-9]+[0-9]*$/', $cylinderCapacity)) {
            $this->errors->add(Errors::CC_INVALID, 'cylinderCapacity');
        } else {
            if ($cylinderCapacity < self::LIMIT_CC_MIN || $cylinderCapacity > self::LIMIT_CC_MAX) {
                $this->errors->add(sprintf(Errors::CC_NOT_BETWEEN, self::LIMIT_CC_MAX), 'cylinderCapacity');
            }
        }
    }

    private function validateMake($make, $makeOther)
    {
        if (!$make) {
            $this->errors->add('You must choose a manufacturer');
        } elseif ($make === self::MAKE_OTHER && !$makeOther) {
            $this->errors->add('You must enter a manufacturer', 'makeOther');
        } elseif ($make !== self::MAKE_OTHER && $makeOther) {
            $this->errors->add("You can only enter a new manufacturer if you choose 'other' from the list of manufacturers");
        }
    }

    private function validateModel($model, $modelOther, $make)
    {
        if (!$model) {
            $this->errors->add('You must choose a model');
        } elseif ($make === self::MAKE_OTHER && $model !== self::MODEL_OTHER) {
            $this->errors->add('Model must be related to a manufacturer', 'model');
        } elseif ($model === self::MODEL_OTHER && !$modelOther) {
            $this->errors->add('You must enter a model', 'modelOther');
        } elseif ($model !== self::MODEL_OTHER && $modelOther) {
            $this->errors->add("You can only enter a new model if you choose 'other' from the list of models");
        }
    }
}

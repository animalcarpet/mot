<?php

namespace DvsaMotApi\Service\Validator;

use DvsaCommonApi\Service\Exception\RequiredFieldException;
use DvsaCommonApi\Service\Validator\AbstractValidator;
use DvsaCommon\Model\VehicleClassGroup;
use Zend\Validator\Digits;

class DemoTestAssessmentValidator extends AbstractValidator
{
    const FIELD_TESTER_ID = 'testerId';
    const FIELD_VEHICLE_CLASS_GROUP = 'vehicleClassGroup';

    const ERROR_WRONG_NUMBER_OF_AUTHORISATION_RECORDS = "The amount of rows in database in table 'auth_for_testing_mot' for person with id '%s' for vehicle class group '%s' is invalid. The amount is '%s' while required is '%s'.";
    const ERROR_INVALID_FIELD_TYPE = 'Field %s must be of integer type';

    private static $requiredFields
        = [
            self::FIELD_TESTER_ID,
            self::FIELD_VEHICLE_CLASS_GROUP,
        ];

    private $digitValidator;

    public function __construct()
    {
        parent::__construct();

        $this->digitValidator = new Digits();
    }

    /**
     * @param array $data
     *
     * @throws RequiredFieldException
     * @throws \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function validate(array $data)
    {
        RequiredFieldException::CheckIfRequiredFieldsNotEmpty(self::$requiredFields, $data);

        if (!$this->digitValidator->isValid($data[self::FIELD_TESTER_ID])) {
            $msg = sprintf(self::ERROR_INVALID_FIELD_TYPE, self::FIELD_TESTER_ID);
            $this->errors->add($msg, self::FIELD_TESTER_ID);
        }

        try {
            VehicleClassGroup::getClassesForGroup($data[self::FIELD_VEHICLE_CLASS_GROUP]);
        } catch (\InvalidArgumentException $e) {
            $this->errors->add("Unknown group '".$data[self::FIELD_VEHICLE_CLASS_GROUP]."'");
        }

        $this->errors->throwIfAny();
    }
}

<?php

namespace PersonApi\Service\Validator;

use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use DvsaCommonApi\Service\Validator\AbstractValidator;
use PersonApi\Service\PersonalAuthorisationForMotTestingService;

/**
 * Class PersonalAuthorisationForMotTestingValidator.
 */
class PersonalAuthorisationForMotTestingValidator extends AbstractValidator
{
    const INVALID_VEHICLE_GROUP = 'Invalid group of vehicle classes';
    const INVALID_STATUS = 'Invalid authorised status';

    private $requiredFields
        = [
            'result',
            'group',
        ];

    public function validate($data)
    {
        $this->checkRequiredFields($this->requiredFields, $data);

        $this->validateGroupOfVehicle($data);
        $this->validateResult($data);

        $this->errors->throwIfAny();
    }

    /**
     * @param $data
     */
    private function validateGroupOfVehicle($data)
    {
        if (false === in_array(
                $data['group'],
                [
                    PersonalAuthorisationForMotTestingService::GROUP_A_VEHICLE,
                    PersonalAuthorisationForMotTestingService::GROUP_B_VEHICLE,
                ]
            )
        ) {
            $this->errors->add(self::INVALID_VEHICLE_GROUP, 'group');
        }
    }

    /**
     * @param $data
     */
    private function validateResult($data)
    {
        if (false === in_array(
                $data['result'],
                [
                    AuthorisationForTestingMotStatusCode::UNKNOWN,
                    AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED,
                    AuthorisationForTestingMotStatusCode::DEMO_TEST_NEEDED,
                    AuthorisationForTestingMotStatusCode::QUALIFIED,
                    AuthorisationForTestingMotStatusCode::SUSPENDED,
                ]
            )
        ) {
            $this->errors->add(self::INVALID_STATUS, 'group');
        }
    }
}

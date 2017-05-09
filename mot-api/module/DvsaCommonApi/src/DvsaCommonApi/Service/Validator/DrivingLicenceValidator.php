<?php

namespace DvsaCommonApi\Service\Validator;

use DvsaCommonApi\Service\Exception\RequiredFieldException;

/**
 * Class DrivingLicenceValidator.
 */
class DrivingLicenceValidator extends AbstractValidator
{
    const TYPE_DRIVING_LICENCE_UK = 'UK';

    const MESSAGE_DRIVING_LICENCE_INCORRECT = 'Driving licence format is incorrect';

    private $requiredFields
        = [
            'drivingLicenceRegion',
            'drivingLicenceNumber',
        ];

    public function validate($data)
    {
        RequiredFieldException::CheckIfRequiredFieldsNotEmpty($this->requiredFields, $data);
        $this->validateWithoutThrowing($data);

        $this->errors->throwIfAny();
    }

    public function validateWithoutThrowing($data)
    {
        if (!$this->isDrivingLicenceValid($data['drivingLicenceNumber'], $data['drivingLicenceRegion'])) {
            $this->errors->add(self::MESSAGE_DRIVING_LICENCE_INCORRECT, 'passwordConfirmation');
        }
    }

    private function isDrivingLicenceValid(
        $drivingLicenceNumber,
        $drivingLicenceRegion = self::TYPE_DRIVING_LICENCE_UK
    ) {
        switch ($drivingLicenceRegion) {
            case self::TYPE_DRIVING_LICENCE_UK:
                if (
                    preg_match(
                        '/^([A-Z]{2}[9]{3}|[A-Z]{3}[9]{2}|[A-Z]{4}[9]{1}|[A-Z]{5})[0-9]{6}([A-Z]{1}[9]{1}|[A-Z]{2})[0-9]{1}[A-Z0-9]{2}[0-9]{2}$/',
                        $drivingLicenceNumber
                    )
                    || preg_match(
                        '/^[0-9]{8}$/',
                        $drivingLicenceNumber
                    )
                ) {
                    return true;
                }

                break;
            default:
                return true;
        }

        return false;
    }
}

<?php

namespace DvsaMotApi\Service\Validator;

use DvsaCommon\Messages\Vehicle\CreateVehicleErrors as Errors;
use DvsaCommon\Model\CountryOfRegistration;
use DvsaCommonApi\Service\Validator\ErrorSchema;

class VehicleVrmVinValidator
{
    const REG_MAX_LENGTH = 13;
    const REG_MAX_LENGTH_FOR_UK = 7;

    const VIN_MIN_LENGTH = 1;
    const VIN_MAX_LENGTH = 20;

    public function validate($data = null, $errors = null)
    {
        $appendOnly = !is_null($errors);
        if (!$appendOnly) {
            $errors = new ErrorSchema();
        }
        $cor = $data['countryOfRegistration'];
        $vrm = $data['registrationNumber'];
        $vin = $data['vin'];
        $emptyVrmReason = isset($data['emptyVrmReason']) ? $data['emptyVrmReason'] : null;
        $emptyVinReason = isset($data['emptyVinReason']) ? $data['emptyVinReason'] : null;

        if (is_null($vin) && !is_null($emptyVinReason)
            && is_null($vrm) && !is_null($emptyVrmReason)
        ) {
            $errors->add(Errors::BOTH_REG_AND_VIN_EMPTY, '');
        }

        $this->validateVrm($errors, $vrm, $emptyVrmReason, $cor);
        $this->validateVin($errors, $vin, $emptyVinReason);

        if (!$appendOnly) {
            $errors->throwIfAny();
        }
    }

    private function validateVrm(ErrorSchema $errors, $vrm, $emptyVrmReason, $cor)
    {
        if (is_null($vrm)) {
            if (is_null($emptyVrmReason)) {
                $errors->add(Errors::EMPTY_REG_REASON_REQUIRED);
            }

            return;
        }

        $isUkCountry = CountryOfRegistration::isUkCountry((int) $cor);
        $maxVRMLength = $isUkCountry ? self::REG_MAX_LENGTH_FOR_UK : self::REG_MAX_LENGTH;

        if (strlen($vrm) > $maxVRMLength) {
            $errors->add(sprintf(Errors::REG_TOO_LONG, $maxVRMLength), 'registrationNumber');
        }

        if (!is_null($emptyVrmReason)) {
            $errors->add(Errors::EMPTY_REG_REASON_NOT_PERMITTED, 'emptyVrmReason');
        }
    }

    private function validateVin(ErrorSchema $errors, $vin, $emptyVinReason)
    {
        if (is_null($vin)) {
            if (is_null($emptyVinReason)) {
                $errors->add(Errors::EMPTY_VIN_REASON_REQUIRED);
            }

            return;
        }

        $vinLength = strlen($vin);

        if ($vinLength < self::VIN_MIN_LENGTH || $vinLength > self::VIN_MAX_LENGTH) {
            $errors->add(sprintf(Errors::VIN_LENGTH, self::VIN_MIN_LENGTH, self::VIN_MAX_LENGTH), 'vin');
        }

        if (!is_null($emptyVinReason)) {
            $errors->add(Errors::EMPTY_VIN_REASON_NOT_PERMITTED, 'emptyVinReason');
        }
    }
}

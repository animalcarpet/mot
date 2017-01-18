<?php

namespace DvsaMotApi\Service\Validator;

use Api\Check\CheckMessage as CM;
use Api\Check\CheckResult;
use CensorApi\Service\CensorService;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Date\Exception\DateException;
use DvsaMotApi\Dto\ReplacementCertificateDraftChangeDTO;
use DvsaMotApi\Service\Validator\Odometer\OdometerReadingValidator;

/**
 * Class ReplacementCertificateDraftValidator
 *
 * @package DvsaMotApi\Service\Validator
 */
class ReplacementCertificateDraftChangeValidator
{

    /**
     * @var CensorService $censorService
     */
    private $censorService;

    /**
     * @param CensorService $censorService
     */
    public function __construct(CensorService $censorService)
    {
        $this->censorService = $censorService;
    }

    /**
     * @param ReplacementCertificateDraftChangeDTO $dto
     *
     * @return CheckResult
     */
    public function validate(ReplacementCertificateDraftChangeDTO $dto)
    {
        $checkResult = CheckResult::ok();

        if (!$dto->isDataChanged()) {
            $checkResult->add(CM::withText("No data changed"));
            return $checkResult;
        }

        if ($dto->isCustomMakeSet()) {
            $make = $dto->getCustomMake();

            if (self::isStringEmpty($make)) {
                $checkResult->add(CM::withText("Custom Make cannot be Empty")->field("customMake"));
            } else {
                if ($this->censorService->containsProfanity($make)) {
                    $checkResult->add(CM::withText("Custom Make contains a censored word")->field("customMake"));
                }
            }
        }

        if ($dto->isCustomModelSet()) {
            $model = $dto->getCustomModel();

            if (self::isStringEmpty($model)) {
                $checkResult->add(CM::withText("Custom Model cannot be Empty")->field("customModel"));
            } else {
                if ($this->censorService->containsProfanity($model)) {
                    $checkResult->add(CM::withText("Custom Model contains a censored word")->field("customModel"));
                }
            }
        }

        if ($dto->isOdometerReadingSet()) {
            $checkResult = (new OdometerReadingValidator())->validate(
                $dto->getOdometerValue(),
                $dto->getOdometerUnit(),
                $dto->getOdometerResultType(),
                'odometerReading'
            );
        }
        if ($dto->isVinSet() && self::isStringEmpty($dto->getVin())) {
            $checkResult->add(CM::withText("Invalid VIN")->field("vin"));
        }
        if ($dto->isVrmSet() && self::isStringEmpty(($dto->getVrm()))) {
            $checkResult->add(CM::withText("Invalid VRM")->field("vrm"));
        }

        if ($dto->isCountryOfRegistrationSet() && !is_int($dto->getCountryOfRegistration())) {
            $checkResult->add(CM::withText("Invalid country of registration")->field("countryOfRegistration"));
        }
        if ($dto->isVtsSiteNumberSet() && self::isStringEmpty($dto->getVtsSiteNumber())) {
            $checkResult->add(CM::withText("Invalid testing location")->field("vtsSiteNumber"));
        }
        if ($dto->isReasonForReplacementSet() && self::isStringEmpty($dto->getReasonForReplacement())) {
            $checkResult->add(CM::withText("Reason for replacement cannot be empty")->field("reasonForReplacement"));
        }
        if ($dto->isReasonForDifferentTesterSet() && self::isStringEmpty($dto->getReasonForDifferentTester())) {
            $checkResult->add(
                CM::withText("Reason for different tester cannot be empty")->field("reasonForDifferentTester")
            );
        }

        if ($dto->isMakeSet()) {
            if (self::isStringEmpty($dto->getCustomModel()) && self::isStringEmpty($dto->getModel())) {
                $checkResult->add(
                    CM::withText('Please specify a model')
                );
            }
        }
        if ($dto->isExpiryDateSet()) {
            try {
                $expiryDate = DateUtils::toDate($dto->getExpiryDate());

                if ($expiryDate < new \DateTime('now')) {
                    throw new DateException("Date needs to be in the future.");
                }
            } catch (DateException $e) {
                $checkResult->add(CM::withText("Invalid expiry date")->field("expiryDate"));
            }
        }

        return $checkResult;
    }

    /**
     * @param $str string
     *
     * @return bool
     */
    private static function isStringEmpty($str)
    {
        $str = trim($str);
        return empty($str);
    }
}

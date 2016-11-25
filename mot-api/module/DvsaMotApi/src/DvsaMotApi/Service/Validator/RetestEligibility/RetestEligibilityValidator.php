<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot
 */

namespace DvsaMotApi\Service\Validator\RetestEligibility;

use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Date\DateUtils as DU;
use DvsaCommon\Dto\MotTesting\ContingencyTestDto;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaCommonApi\Service\Exception\ForbiddenException;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Repository\MotTestRepository;
use DvsaMotApi\Helper\MysteryShopperHelper;
use NonWorkingDaysApi\NonWorkingDaysHelper;

/**
 * Contains services related to retest eligibility feature
 */
class RetestEligibilityValidator
{
    const RETEST_WORKING_DAYS_PERIOD = 10;

    /** @var NonWorkingDaysHelper */
    private $nonWorkingDaysHelper;
    /** @var MotTestRepository $motTestRepository */
    private $motTestRepository;
    /** @var  DateTimeHolder */
    private $dateTime;

    /**
     * @var MysteryShopperHelper
     */
    private $mysteryShopperHelper;

    /**
     * @param NonWorkingDaysHelper $nonWorkingDaysHelper
     * @param MotTestRepository    $motTestRepository
     * @param MysteryShopperHelper $mysteryShopperHelper
     */
    public function __construct(
        NonWorkingDaysHelper $nonWorkingDaysHelper,
        MotTestRepository $motTestRepository,
        MysteryShopperHelper $mysteryShopperHelper
    ) {
        $this->nonWorkingDaysHelper = $nonWorkingDaysHelper;
        $this->motTestRepository = $motTestRepository;
        $this->mysteryShopperHelper = $mysteryShopperHelper;

        $this->dateTime = new DateTimeHolder();
    }

    /**
     * Method checks if a vehicle is eligible for a retest
     *
     * @param int $vehicleId database identifier of vehicle
     * @param int $vtsId database identifier of Vehicle Testing Station
     * @param ContingencyTestDto $contingencyDto
     *
     * @return int  Retest granted code, or throw exception
     * @throws BadRequestException
     */
    public function checkEligibilityForRetest($vehicleId, $vtsId, $contingencyDto = null)
    {
        $checkResult = $this->validateVehicleForRetest($vehicleId, $vtsId, $contingencyDto);

        $isEligibleForRetest = (count($checkResult) === 0);
        if (!$isEligibleForRetest) {
            $exception = BadRequestException::create("Vehicle is not eligible for a retest");

            foreach ($checkResult as $errorCode) {
                $errorMsg = RetestEligibilityErrorCodeTranslator::toText($errorCode);
                $exception->addError($errorMsg, $errorCode, $errorMsg);
            }

            throw $exception;
        }

        return $isEligibleForRetest;
    }

    /**
     * @param DateTimeHolder $dateTimeHolder
     *
     * @return $this
     */
    public function setDateTimeHolder(DateTimeHolder $dateTimeHolder)
    {
        $this->dateTime = $dateTimeHolder;

        return $this;
    }

    /**
     * @param $vehicleId
     * @param $vtsId
     * @param $contingencyDto
     *
     * @return array
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    private function validateVehicleForRetest($vehicleId, $vtsId, $contingencyDto = null)
    {
        $resultCodes = [];
        $lastTest = $this->motTestRepository->findLastNormalTest($vehicleId, $contingencyDto);

        if (!($lastTest instanceof MotTest)) {
            return [RetestEligibilityCheckCode::RETEST_REJECTED_ORIGINAL_NEVER_PERFORMED];
        }

        $isDifferentVts = intval($lastTest->getVehicleTestingStation()->getId()) !== intval($vtsId);

        if ($lastTest->isCancelled() && $isDifferentVts) {
            $lastTest = $this->motTestRepository->findLastNormalTest($vehicleId, $contingencyDto, $vtsId);
            if ($lastTest == null || !$lastTest->isFailed()) {
                return [RetestEligibilityCheckCode::RETEST_REJECTED_ORIGINAL_WAS_NOT_FAILED];
            }

            $count = $this->motTestRepository->countNotCancelledTests($vehicleId, $lastTest->getCompletedDate(),
                $contingencyDto);

            if ($count > 0) {
                return [RetestEligibilityCheckCode::RETEST_REJECTED_ORIGINAL_PERFORMED_AT_A_DIFFERENT_VTS];
            }
        } elseif ($lastTest->isCancelled()) {
            return [RetestEligibilityCheckCode::RETEST_REJECTED_ORIGINAL_CANCELLED];
        } elseif (!$lastTest->isFailed()) {
            return [RetestEligibilityCheckCode::RETEST_REJECTED_ORIGINAL_WAS_NOT_FAILED];
        } elseif ($isDifferentVts) {
            $resultCodes[] = RetestEligibilityCheckCode::RETEST_REJECTED_ORIGINAL_PERFORMED_AT_A_DIFFERENT_VTS;
        }

        if (false === $this->isWithinTenWorkingDays($lastTest, $contingencyDto)) {
            $resultCodes[] = RetestEligibilityCheckCode::RETEST_REJECTED_ORIGINAL_PERFORMED_MORE_THAN_10_WORKING_DAYS;
        }

        $reTest = $this->motTestRepository->findRetestForMotTest($lastTest->getNumber());
        if ($reTest instanceof MotTest) {
            $resultCodes [] = RetestEligibilityCheckCode::RETEST_REJECTED_ALREADY_REGISTERED;
        }
        return $resultCodes;
    }

    /**
     * @param MotTest $test
     * @param $contingencyDto
     *
     * @throws NotFoundException
     *
     * @return bool
     */
    private function isWithinTenWorkingDays(MotTest $test, $contingencyDto = null)
    {
        $country = $test->getVehicleTestingStation()->getNonWorkingDayCountry();
        if (is_null($country)) {
            throw new NotFoundException("Vts country required");
        }

        $lastTestCompletedDate = DU::cropTime($test->getCompletedDate());
        $queryDate = ($contingencyDto instanceof ContingencyTestDto)
            ? DU::cropTime($contingencyDto->getPerformedAt())
            : $this->dateTime->getCurrentDate();

        $countryCode = $country->getCountry()->getCode();
        $nthWorkingDayDate = $this->nonWorkingDaysHelper->calculateNthWorkingDayAfter(
            $lastTestCompletedDate,
            self::RETEST_WORKING_DAYS_PERIOD,
            $countryCode
        );

        $dayDiff = intval(DU::getDaysDifference($nthWorkingDayDate, $queryDate));

        return $dayDiff <= 0;
    }
}

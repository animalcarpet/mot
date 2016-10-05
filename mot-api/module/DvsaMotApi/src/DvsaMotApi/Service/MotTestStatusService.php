<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotApi\Service;

use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\ReasonForRejection;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaEntities\Entity\MotTest;

class MotTestStatusService
{
    /**
     * @var array
     */
    private static $brakePerformanceNotTestedRfrs = [
        ReasonForRejection::CLASS_12_BRAKE_PERFORMANCE_NOT_TESTED_RFR_ID,
        ReasonForRejection::CLASS_3457_BRAKE_PERFORMANCE_NOT_TESTED_RFR_ID,
    ];

    /**
     * @var MotAuthorisationServiceInterface
     */
    private $authorisationService;

    /**
     * MotTestStatusService constructor.
     *
     * @param MotAuthorisationServiceInterface $authorisationService
     */
    public function __construct(MotAuthorisationServiceInterface $authorisationService)
    {
        $this->authorisationService = $authorisationService;
    }

    /**
     * @param MotTest $motTest
     *
     * @return bool
     */
    public function isIncomplete(MotTest $motTest)
    {
        $hasOdometerReading = $motTest->getOdometerReading() != null;
        $hasBrakeTestResult = $motTest->hasBrakeTestResults();
        $canTestWithoutBrakeTests = $this->authorisationService->isGranted(PermissionInSystem::TEST_WITHOUT_BRAKE_TESTS);
        $hasUnrepairedBrakePerformanceNotTestedRfr = $this->hasUnrepairedBrakePerformanceNotTestedRfr($motTest);

        $hasOriginalBrakeTestPassing = $motTest->getMotTestType()->getCode() === MotTestTypeCode::RE_TEST
            && $motTest->getMotTestIdOriginal()->getBrakeTestGeneralPass();

        $isBrakeTestOk = $hasBrakeTestResult || $hasUnrepairedBrakePerformanceNotTestedRfr || $canTestWithoutBrakeTests
            || $hasOriginalBrakeTestPassing;

        return !$hasOdometerReading || !$isBrakeTestOk;
    }

    /**
     * @param MotTest $motTest
     *
     * @return bool
     */
    public function hasUnrepairedBrakePerformanceNotTestedRfr(MotTest $motTest)
    {
        $motRfrs = $motTest->getMotTestReasonForRejections();
        foreach ($motRfrs as $rfr) {
            if ($rfr->getReasonForRejection() === null) {
                continue; // TODO solve 'Manual Advisory' Test RFRs which doesn't have linked RFR
            }
            if (in_array($rfr->getReasonForRejection()->getRfrId(), self::$brakePerformanceNotTestedRfrs) && !$rfr->isMarkedAsRepaired()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param MotTest $motTest
     *
     * @return string
     */
    public function getMotTestPendingStatus(MotTest $motTest)
    {
        if ($this->isIncomplete($motTest)) {
            return MotTestService::PENDING_INCOMPLETE_STATUS;
        }

        return $motTest->hasFailures() ? MotTestStatusName::FAILED : MotTestStatusName::PASSED;
    }
}

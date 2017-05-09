<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotApi\Service;

use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\OdometerReadingResultType;
use DvsaCommon\Constants\ReasonForRejection;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaEntities\Entity\MotTest;

class MotTestStatusService
{
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
        $hasBrakeTestResult = $motTest->hasBrakeTestResults();
        $canTestWithoutBrakeTests = $this->authorisationService->isGranted(PermissionInSystem::TEST_WITHOUT_BRAKE_TESTS);
        $hasUnrepairedBrakePerformanceNotTestedRfr = $this->hasUnrepairedBrakePerformanceNotTestedRfr($motTest);

        $hasOriginalBrakeTestPassing = $motTest->getMotTestType()->getCode() === MotTestTypeCode::RE_TEST
            && $motTest->getMotTestIdOriginal()->getBrakeTestGeneralPass();

        $isBrakeTestOk = $hasBrakeTestResult || $hasUnrepairedBrakePerformanceNotTestedRfr || $canTestWithoutBrakeTests
            || $hasOriginalBrakeTestPassing;

        return !$this->hasOdometer($motTest) || !$isBrakeTestOk;
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
            $rfrId = $rfr->getReasonForRejection()->getRfrId();
            if (in_array($rfrId, ReasonForRejection::BRAKE_PERFORMANCE_NOT_TESTED_RFR_IDS) && !$rfr->isMarkedAsRepaired()) {
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

    private function hasOdometer(MotTest $motTest)
    {
        $hasOdometerValue = !is_null($motTest->getOdometerValue());
        $hasOdometerUnit = !is_null($motTest->getOdometerUnit());
        $hasOdometerResultType = !is_null($motTest->getOdometerResultType()) &&
            OdometerReadingResultType::OK != $motTest->getOdometerResultType();

        return ($hasOdometerValue && $hasOdometerUnit) xor $hasOdometerResultType;
    }
}

<?php

namespace DvsaMotApi\Service;

use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Repository\MotTestRepository;

/**
 * Class MotTestDateHelperService.
 */
class MotTestDateHelperService
{
    /**
     * @var DateTimeHolder
     */
    private $dateTimeHolder;
    /**
     * @var MotTestRepository
     */
    private $motTestRepository;
    /**
     * @var MotTestStatusService
     */
    private $motTestStatusService;

    /**
     * @param DateTimeHolder       $dateTimeHolder
     * @param MotTestRepository    $motTestRepository
     * @param MotTestStatusService $motTestStatusService
     */
    public function __construct(
        DateTimeHolder $dateTimeHolder,
        MotTestRepository $motTestRepository,
        MotTestStatusService $motTestStatusService
    ) {
        $this->dateTimeHolder = $dateTimeHolder;
        $this->motTestRepository = $motTestRepository;
        $this->motTestStatusService = $motTestStatusService;
    }

    /**
     * @param MotTest $motTest
     * @param null    $issuedDate
     * @param null    $pendingStatus
     *
     * @return \DateTime|null
     */
    public function getIssuedDate(MotTest $motTest, $issuedDate = null, $pendingStatus = null)
    {
        //  --  define issued date  --
        if ($issuedDate === null) {
            if ($motTest->getEmergencyLog() === null) {
                // Use *today* as the issue date
                $issuedDate = $this->dateTimeHolder->getCurrent();
            } else {
                // Use the contingency test started date as the issued date
                $issuedDate = $motTest->getStartedDate();
            }
        }

        //  --  set or not issued date to mot test in depend from test type and test status --
        $isTestIncomplete = ($pendingStatus == MotTestService::PENDING_INCOMPLETE_STATUS);

        return $isTestIncomplete ? null : $issuedDate;
    }

    /**
     * Answers the calculated expiry date based. Delegates all business
     * logic to MotTestDate which actually has the logic within it.
     *
     * NOTE: Null MUST be returned if no date is required as it is used
     * to update MOT rows during a status update operation and null is used
     * to remove the fact that an expiry date is available at all!
     *
     * @param MotTest $currentMotTest
     * @param null    $issuedDate
     * @param null    $pendingStatus
     *
     * @throws \Exception
     *
     * @return \DateTime|null -- null causes "N/A" to be displayed in views
     *
     * @SuppressWarnings(unused) --remove later!
     */
    public function getExpiryDate(MotTest $currentMotTest, $issuedDate = null, $pendingStatus = null)
    {
        $returnDate = null;

        if ($issuedDate || $this->motTestNeedsExpiryDate($currentMotTest, $pendingStatus)) {
            $previousMotTest = $this->motTestRepository->getLatestMotTestByVehicleIdAndResult(
                $currentMotTest->getVehicle()->getId(),
                MotTestStatusName::PASSED,
                DateUtils::toIsoString($this->dateTimeHolder->getCurrent())
            );

            $motTestDate = new MotTestDate(
                $this->dateTimeHolder->getCurrent(),
                $currentMotTest,
                $previousMotTest
            );

            $returnDate = $motTestDate->getExpiryDate();
        }

        return $returnDate;
    }

    /**
     * Answers TRUE if the test requires an expiry date to be generated. If FALSE is returned
     * then it means the test is not yet passed or it is a compliance or re-inspection test.
     *
     * @param MotTest $motTest
     * @param $pendingStatus
     *
     * @return bool
     */
    protected function motTestNeedsExpiryDate(MotTest $motTest, $pendingStatus)
    {
        $isTestPassed = $motTest->isPassed() || $pendingStatus == MotTestStatusName::PASSED;
        $motTestTypeCode = $motTest->getMotTestType()->getCode();

        $isNeedSetDate = $isTestPassed
            && !($motTestTypeCode === MotTestTypeCode::MOT_COMPLIANCE_SURVEY
                || $motTestTypeCode === MotTestTypeCode::TARGETED_REINSPECTION
            );

        if (!$isNeedSetDate) {
            return false;
        }

        return true;
    }
}

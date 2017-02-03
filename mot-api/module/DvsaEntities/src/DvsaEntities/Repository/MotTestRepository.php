<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaEntities\Repository;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Tools\Pagination\Paginator;
use DvsaCommon\Constants\SearchParamConst;
use DvsaCommon\Dto\Common\OdometerReadingDto;
use DvsaCommon\Dto\MotTesting\ContingencyTestDto;
use DvsaCommon\Enum\MotTestStatusCode;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Enum\OrganisationSiteStatusCode;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaCommonApi\Service\Exception\ServiceException;
use DvsaEntities\DqlBuilder\NativeQueryBuilder;
use DvsaEntities\DqlBuilder\SearchParam\MotTestSearchParam;
use DvsaEntities\Entity\Make;
use DvsaEntities\Entity\Model;
use DvsaEntities\Entity\ModelDetail;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestType;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\Vehicle;
use DvsaEntities\Entity\VehicleHistory;
use DvsaMotApi\Helper\MysteryShopperHelper;

/**
 * Class MotTestRepository
 */
class MotTestRepository extends AbstractMutableRepository
{
    protected $query;

    public static $testLogTestTypes = [
        'TT_NORMAL' => MotTestTypeCode::NORMAL_TEST,
        'TT_PARTIAL_RETEST_LEFT' => MotTestTypeCode::PARTIAL_RETEST_LEFT_VTS,
        'TT_PARTIAL_RETEST_REPAIRED' => MotTestTypeCode::PARTIAL_RETEST_REPAIRED_AT_VTS,
        'TT_RETEST' => MotTestTypeCode::RE_TEST,
        'TT_MYSTERY_SHOPPER' => MotTestTypeCode::MYSTERY_SHOPPER,
    ];

    public static $testLogTestStatuses = [
        'TS_ABANDONED' => MotTestStatusName::ABANDONED,
        'TS_ABORTED' => MotTestStatusName::ABORTED,
        'TS_ABORTED_VE' => MotTestStatusName::ABORTED_VE,
        'TS_FAILED' => MotTestStatusName::FAILED,
        'TS_PASSED' => MotTestStatusName::PASSED,
        'TS_REFUSED' => MotTestStatusName::REFUSED,
    ];

    /**
     * Test types used commonly in queries.
     *
     * @var array
     */
    private $testTypes
        = [
            MotTestTypeCode::RE_TEST,
            MotTestTypeCode::NORMAL_TEST,
            MotTestTypeCode::STATUTORY_APPEAL,
            MotTestTypeCode::INVERTED_APPEAL,
        ];

    /**
     * @param $vehicleId
     * @param $contingencyDto
     *
     * @return MotTest
     */
    public function findLastNormalTest($vehicleId, $contingencyDto = null, $vtsId = null)
    {
        $qb = $this->createQueryBuilder('mt')
            ->innerJoin('mt.vehicle', 'v')
            ->innerJoin('mt.motTestType', 't')
            ->where('v.id = :vehicleId')
            ->andWhere('t.code = :code')
            ->orderBy('mt.completedDate', 'DESC')
            ->setParameter('vehicleId', $vehicleId)
            ->setParameter('code', MotTestTypeCode::NORMAL_TEST)
            ->setMaxResults(1);

        if ($contingencyDto instanceof ContingencyTestDto) {
            $qb
                ->andWhere('mt.completedDate <= :contingencyDatetime')
                ->setParameter('contingencyDatetime', $contingencyDto->getPerformedAt());
        }

        if ($vtsId !== null) {
            $qb
                ->andWhere('mt.vehicleTestingStation = :vtsId')
                ->setParameter('vtsId', $vtsId);
        }

        $resultArray = $qb->getQuery()->getResult();

        return empty($resultArray) ? null : $resultArray[0];
    }

    /**
     * @param int $vehicleId
     * @param DateTime $from
     * @param ContingencyTestDto|null $contingencyDto
     * @param bool $isMysteryShopper
     *
     * @return int
     */
    public function countNotCancelledTests($vehicleId, DateTime $from, $contingencyDto = null)
    {
        $qb = $this->createQueryBuilder('mt')
            ->select('count(mt.id) AS amount')
            ->innerJoin('mt.vehicle', 'v')
            ->innerJoin('mt.motTestType', 't')
            ->innerJoin('mt.status', 's')
            ->where('v.id = :vehicleId')
            ->andWhere('t.code = :motTestTypeCode')
            ->andWhere('s.code NOT IN (:motTestStatusCode)')
            ->andWhere('mt.completedDate > :completedDate')
            ->setParameter('vehicleId', $vehicleId)
            ->setParameter('motTestTypeCode', MotTestTypeCode::NORMAL_TEST)
            ->setParameter('motTestStatusCode', [MotTestStatusCode::ABANDONED, MotTestStatusCode::ABORTED, MotTestStatusCode::ABORTED_VE])
            ->setParameter('completedDate', $from);

        if ($contingencyDto instanceof ContingencyTestDto) {
            $qb
                ->andWhere('mt.completedDate <= :contingencyDatetime')
                ->setParameter('contingencyDatetime', $contingencyDto->getPerformedAt());
        }

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $registration
     * @param $v5cReference
     *
     * @throws \DvsaCommonApi\Service\Exception\ServiceException
     */
    public function findLastPass($registration, $v5cReference)
    {
        $exception = new ServiceException(null);
        $exception->addError(
            'TODO(PT): To be implemented when a placement of v5c is known.',
            ServiceException::DEFAULT_STATUS_CODE
        );

        throw $exception;
    }

    /**
     * Returns the expiry date for the last previous certificated test.
     * Can be null if no such certificate has been issued.
     *
     * @param $vehicleId
     *
     * @return \DateTime
     */
    public function findLastCertificateExpiryDate($vehicleId)
    {
        $qb = $this->createQueryBuilder('mt')
            ->innerJoin('mt.motTestType', 't')
            ->innerJoin('mt.status', 'ts')
            ->where('mt.vehicle = :vehicleId')
            ->andWhere('t.code IN (:testTypes)')
            ->andWhere('ts.name = :status')
            ->orderBy('mt.expiryDate', 'DESC')
            ->setParameter('vehicleId', $vehicleId)
            ->setParameter('status', MotTestStatusName::PASSED)
            ->setParameter('testTypes', $this->testTypes)
            ->setMaxResults(1);

        $resultArray = $qb->getQuery()->getArrayResult();

        return empty($resultArray) ? null : $resultArray[0]['expiryDate'];
    }

    /**
     * Finds in progress MOT test number for a person.
     *
     * @param $personId
     *
     * @return string
     */
    public function findInProgressTestNumberForPerson($personId)
    {
        $motTest = $this->findInProgressTestForPerson($personId);

        return is_null($motTest) ? null : $motTest->getNumber();
    }

    /**
     * Finds in progress MOT test number for a person.
     *
     * @param $personId
     *
     * @return MotTest
     */
    public function findInProgressTestForPerson($personId)
    {
        $qb = $this->createQueryBuilder('mt')
            ->innerJoin('mt.motTestType', 't')
            ->innerJoin('mt.status', 'ts')
            ->where('mt.tester = :personId')
            ->andWhere('ts.name = :status')
            ->andWhere('t.code NOT IN (:code)')
            ->setParameter('personId', $personId)
            ->setParameter('status', MotTestStatusName::ACTIVE)
            ->setParameter(
                'code',
                [
                    MotTestTypeCode::ROUTINE_DEMONSTRATION_TEST,
                    MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING,
                    MotTestTypeCode::NON_MOT_TEST
                ]
            )
            ->setMaxResults(1);

        $resultArray = $qb->getQuery()->getResult();

        return empty($resultArray) ? null : $resultArray[0];
    }

    /**
     * @param int $personId
     *
     * @return string|null
     */
    public function findInProgressNonMotTestNumberForPerson($personId)
    {
        $motTest = $this->findInProgressTestOfTypeForPerson($personId, MotTestTypeCode::NON_MOT_TEST);

        return is_null($motTest) ? null : $motTest->getNumber();
    }

    /**
     * Return in progress DEMO test number for the given person.
     *
     * @see findInProgressDemoTestForPerson for different type of demo tests
     *
     * @param int $personId
     * @param bool $routine To set the demo test type
     *
     * @return string|null
     */
    public function findInProgressDemoTestNumberForPerson($personId, $routine = false)
    {
        $motTest = $this->findInProgressDemoTestForPerson($personId, $routine);

        return is_null($motTest) ? null : $motTest->getNumber();
    }

    /**
     * Return in progress Demo test for the given person.
     *
     * note: there are 2 type of the demo test
     *          - Demonstration Test following training (DT)
     *          - Routine Demonstration Test (DR)
     *       this method will return the "Demonstration Test following training" by default
     *
     * @param int $personId
     * @param bool $routine To set the demo test type
     *
     * @return MotTest|null
     */
    public function findInProgressDemoTestForPerson($personId, $routine = false)
    {
        $testTypeCode = $routine ? MotTestTypeCode::ROUTINE_DEMONSTRATION_TEST : MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING;

        return $this->findInProgressTestOfTypeForPerson($personId, $testTypeCode);
    }

    /**
     * @param int $personId
     * @param string $testTypeCode
     * @return MotTest|null
     */
    private function findInProgressTestOfTypeForPerson($personId, $testTypeCode)
    {
        $qb = $this->createQueryBuilder('mt')
            ->innerJoin('mt.motTestType', 't')
            ->innerJoin('mt.status', 'ts')
            ->where('mt.tester = :personId')
            ->andWhere('ts.name = :status')
            ->andWhere('t.code = :code')
            ->setParameter('personId', $personId)
            ->setParameter('status', MotTestStatusName::ACTIVE)
            ->setParameter('code', $testTypeCode)
            ->setMaxResults(1);

        $resultArray = $qb->getQuery()->getResult();

        return empty($resultArray) ? null : $resultArray[0];
    }

    /**
     * @param $vehicleId
     *
     * @return int
     */
    public function isTestInProgressForVehicle($vehicleId)
    {
        $resultArray = $this->findInProgressTestDataForVehicle($vehicleId, 'mt.id');

        return !empty($resultArray[0]['id']);
    }

    /**
     * @param $vehicleId
     *
     * @return MotTest
     */
    public function findInProgressTestForVehicle($vehicleId)
    {
        $resultArray = $this->findInProgressTestDataForVehicle($vehicleId, 'mt');

        return empty($resultArray) ? null : $resultArray[0];
    }

    /**
     * @param $vtsId
     *
     * @return MotTest[]
     */
    public function findInProgressTestsForVts($vtsId)
    {
        $qb = $this->createQueryBuilder('mt');
        $query =
            $qb->innerJoin('mt.status', 'ts')
                ->where('ts.name = :status')
                ->andWhere('mt.vehicleTestingStation = :vehicleTestingStation')
                ->setParameter('status', MotTestStatusName::ACTIVE)
                ->setParameter('vehicleTestingStation', $vtsId)
                ->getQuery();

        return $query->getResult();
    }

    /**
     * @param $vtsId
     *
     * @return int
     */
    public function countInProgressTestsForVts($vtsId)
    {
        $qb = $this->createQueryBuilder('mt')
            ->select('COUNT(mt.id) AS cnt')
            ->innerJoin('mt.status', 'ts')
            ->where('ts.code = :STATUS')
            ->andWhere('mt.vehicleTestingStation = :VTS_ID')
            ->setParameter(':STATUS', MotTestStatusCode::ACTIVE)
            ->setParameter(':VTS_ID', $vtsId);

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $motTestNumber
     *
     * @return MotTest
     */
    public function findRetestForMotTest($motTestNumber)
    {
        $qb = $this->createQueryBuilder('mt')
            ->innerJoin('mt.motTestIdOriginal', 'omt')
            ->innerJoin('mt.motTestType', 't')
            ->innerJoin('mt.status', 'ts')
            ->where('t.code = :code')
            ->andWhere('omt.number = :normalTestId')
            ->andWhere('ts.name IN (:statuses)')
            ->setParameter('normalTestId', $motTestNumber)
            ->setParameter('code', MotTestTypeCode::RE_TEST)
            ->setParameter('statuses', [MotTestStatusName::PASSED, MotTestStatusName::FAILED])
            ->setMaxResults(1);

        $resultArray = $qb->getQuery()->getResult();

        return empty($resultArray) ? null : $resultArray[0];
    }

    /**
     * @param int $siteNumber
     * @param array $optionalMotTestTypes
     *
     * @return array
     */
    public function getLatestMotTestsBySiteNumber($siteNumber, array $optionalMotTestTypes)
    {
        $mtQb = $this->createQueryBuilder('it')
            ->select('DATE(it.startedDate) AS sort_date')
            ->distinct(true)
            ->innerJoin('it.vehicleTestingStation', 'ivts')
            ->where('ivts.siteNumber = :siteNumber')
            ->andWhere("it.startedDate >= DATE_SUB(CURRENT_DATE(), 2, 'MONTH')")
            ->addOrderBy('sort_date', 'DESC')
            ->setParameter('siteNumber', $siteNumber)
            ->setMaxResults(2);

        $res = $mtQb->getQuery()->getArrayResult();
        $dates = array_map(
            function ($data) {
                return $data['sort_date'];
            }, $res
        );

        if (empty($dates)) {
            return new ArrayCollection();
        }

        $minDate = min($dates);

        $qb = $this->createQueryBuilder('t')
            ->select(['t', 'v', 'p', 'vts'])
            ->innerJoin('t.tester', 'p')
            ->innerJoin('t.vehicle', 'v')
            ->innerJoin('t.vehicleTestingStation', 'vts')
            ->innerJoin('t.motTestType', 'tt')
            ->innerJoin('t.status', 'ts')
            ->where('vts.siteNumber = :siteNumber')
            ->setParameter('siteNumber', $siteNumber)
            ->orderBy('t.startedDate', 'DESC')
            ->addOrderBy('v.id', 'DESC')
            ->andWhere('t.startedDate >= :minDate')
            ->andWhere('tt.code IN (:testTypes)')
            ->setParameter('testTypes', $this->getMotTestHistoryTestTypes($optionalMotTestTypes))
            ->setParameter('minDate', $minDate);

        return $qb->getQuery()->getResult();
    }

    /**
     * Based on MOT test certificate number returns common MOT test data.
     *
     * @param $motTestNumber
     *
     * @throws NotFoundException
     *
     * @return MotTest
     */
    public function getMotTestByNumber($motTestNumber)
    {
        $result = $this->createQueryBuilder('mt')
            ->addSelect(['rfr', 'rfrMarkedAsRepaired', 'vts', 'defaultBrakeTestClass1And2',
                         'defaultServiceBrakeTestClass3AndAbove', 'defaultParkingBrakeTestClass3AndAbove'])
            ->innerJoin('mt.motTestType', 'tt')
            ->innerJoin('mt.status', 's')
            ->leftJoin('mt.motTestReasonForRejections', 'rfr')
            ->leftJoin('rfr.markedAsRepaired', 'rfrMarkedAsRepaired')
            ->leftJoin('mt.vehicleTestingStation', 'vts')
            ->leftJoin('vts.defaultBrakeTestClass1And2', 'defaultBrakeTestClass1And2')
            ->leftJoin('vts.defaultServiceBrakeTestClass3AndAbove', 'defaultServiceBrakeTestClass3AndAbove')
            ->leftJoin('vts.defaultParkingBrakeTestClass3AndAbove', 'defaultParkingBrakeTestClass3AndAbove')
            ->andWhere('mt.number = :number')
            ->setParameters(['number' => $motTestNumber])
            ->getQuery()->getOneOrNullResult();

        if (is_null($result)) {
            throw new NotFoundException("MOT Test with number $motTestNumber");
        }

        return $result;
    }

    /**
     * Returns a test for a given registration and test number.
     *
     * @param $registration
     * @param $testNumber
     *
     * @return MotTest
     */
    public function findTestByVehicleRegistrationAndTestNumber($registration, $testNumber)
    {
        $qb = $this->createQueryBuilder('mt')
            ->innerJoin('mt.motTestType', 't')
            ->innerJoin('mt.status', 'ts')
            ->innerJoin('mt.vehicle', 'v')
            ->andWhere('ts.name IN (:statuses)')
            ->andWhere('t.code IN (:testTypes)')
            ->andWhere('v.registration = :registration')
            ->andWhere('mt.number = :number')
            ->setParameter('statuses', [MotTestStatusName::PASSED, MotTestStatusName::FAILED])
            ->setParameter('testTypes', $this->testTypes)
            ->setParameter('registration', $registration)
            ->setParameter('number', $testNumber);

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * Find recent tests for a given vehicle
     *
     * @param int $vehicleId
     * @param string|null $startDate
     * @param MysteryShopperHelper $mysteryShopperHelper
     * @param array $mysteryShopperSiteIds Optional
     * @return mixed
     */
    public function findTestsForVehicle(
        $vehicleId,
        $startDate,
        MysteryShopperHelper $mysteryShopperHelper,
        array $mysteryShopperSiteIds = []
    ) {
        $mysteryShopperToggleEnabled = $mysteryShopperHelper->isMysteryShopperToggleEnabled();
        $canViewAllMysteryShopperTests = $mysteryShopperHelper->hasPermissionToViewMysteryShopperTests();

        $testTypes = $this->testTypes;
        $testTypes[] = MotTestTypeCode::TARGETED_REINSPECTION;

        $testTypeWhereClauseFunction = null;

        if ($canViewAllMysteryShopperTests) {
            $testTypes[] = MotTestTypeCode::MYSTERY_SHOPPER;
        } else if ($mysteryShopperToggleEnabled && !empty($mysteryShopperSiteIds)) {
            $testTypeWhereClauseFunction = function($qb) use ($mysteryShopperSiteIds) {
                return $qb->andWhere('(
                        t.code IN (:testTypes)
                        OR (
                            t.code = :mysteryShopperCode 
                            AND mt.vehicleTestingStation IN (:mysteryShopperSites)
                        ))')
                    ->setParameter('mysteryShopperCode', MotTestTypeCode::MYSTERY_SHOPPER)
                    ->setParameter('mysteryShopperSites', $mysteryShopperSiteIds)
                    ;
            };
        }

        return $this->fetchTestsForVehicle($vehicleId, $startDate, $testTypes, $testTypeWhereClauseFunction);
    }

    /**
     * Returns a list of tests for a given vehicle as of a specified date.
     *
     * @param int $vehicleId
     * @param DateTime|null $startDate
     *
     * @return MotTest[]
     */
    public function findTestsExcludingNonAuthoritativeTestsForVehicle($vehicleId, $startDate)
    {
        $testTypes = $this->testTypes;
        $testTypes[] = MotTestTypeCode::TARGETED_REINSPECTION;

        return $this->fetchTestsForVehicle($vehicleId, $startDate, $testTypes);
    }

    private function fetchTestsForVehicle($vehicleId, $startDate, $testTypes, $testTypeWhereClauseFunction = null)
    {
        $statuses = [
            MotTestStatusName::PASSED,
            MotTestStatusName::FAILED,
            MotTestStatusName::ABANDONED,
        ];

        $qb = $this->createQueryBuilder('mt')
            ->innerJoin('mt.motTestType', 't')
            ->innerJoin('mt.status', 'ts')
            ->andWhere('ts.name IN (:statuses)');

        $qb = is_callable($testTypeWhereClauseFunction) ?
            $testTypeWhereClauseFunction($qb) : $qb->andWhere('t.code IN (:testTypes)');

        // note: sort ordering is now applied in-memory, see compareTests
        $qb = $qb
            ->andWhere('mt.vehicle = :vehicleId')
            ->setParameter('statuses', $statuses)
            ->setParameter('testTypes', $testTypes)
            ->setParameter('vehicleId', $vehicleId);

        if ($startDate !== null) {
            $qb
                ->andWhere('mt.issuedDate >= :startDate OR mt.issuedDate IS NULL')
                ->setParameter('startDate', $startDate);
        }

        $query = $qb->getQuery();
        $query->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, MotTestIndexSqlWalker::class);
        $query->setHint(MotTestIndexSqlWalker::HINT_USE_INDEX, $this->getVehicleIndexName());

        $result = $query->getResult();

        usort($result, [$this, 'compareTests']);

        return $result;
    }

    /**
     * Returns a list of Mystery Shopper test type mot tests for a given vehicle as of a specified date and vts.
     *
     * @param int $vehicleId
     * @param DateTime $startDate      (optional)
     * @param int $limit               (optional)
     * @param int $siteId              (optional)
     *
     * @return MotTest[]
     */
    public function findHistoricalMysteryShopperTestsForVehicle($vehicleId, DateTime $startDate = null, $limit = null, $siteId = null)
    {
        $statuses = [
            MotTestStatusName::PASSED,
            MotTestStatusName::FAILED,
            MotTestStatusName::ABANDONED,
        ];

        $testTypes = [MotTestTypeCode::MYSTERY_SHOPPER];

        $qb = $this
            ->createQueryBuilder('mt')
            ->innerJoin('mt.motTestType', 't')
            ->innerJoin('mt.status', 'ts')
            ->where('ts.name IN (:statuses)')
            ->andWhere('t.code IN (:testTypes)')
            ->andWhere('mt.vehicle = :vehicleId')
            ->setParameter('statuses', $statuses)
            ->setParameter('testTypes', $testTypes)
            ->setParameter('vehicleId', $vehicleId);

        if ($startDate !== null) {
            $qb
                ->andWhere('mt.issuedDate >= :startDate OR mt.issuedDate IS NULL')
                ->setParameter('startDate', $startDate);
        }

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        if ($siteId !== null) {
            $qb
                ->andWhere('mt.vehicleTestingStation = :vtsId')
                ->setParameter('vtsId', $siteId);
        }

        $query = $qb->getQuery();
        $query->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, MotTestIndexSqlWalker::class);
        $query->setHint(MotTestIndexSqlWalker::HINT_USE_INDEX, $this->getVehicleIndexName());

        $result = $query->getResult();

        usort($result, [$this, 'compareTests']);

        return $result;
    }

    /**
     * Checks if there is any latest Fail, Abandon, Aborted or Refused test of a vehicle issued after given test.
     *
     * @param $registration
     * @param $v5cReference
     * @param $issuedDate
     *
     * @throws ServiceException
     */
    public function findNonPassIssuedAfter($registration, $v5cReference, $issuedDate)
    {
        $exception = new ServiceException(null);
        $exception->addError(
            'TODO(PT): To be implemented when a placement of v5c is known.',
            ServiceException::DEFAULT_STATUS_CODE
        );

        throw $exception;
    }

    /**
     * Checks if there is latest test of a vehicle with a different V5C reference number and issued after given test.
     *
     * @param $registration
     * @param $v5cReference
     * @param $issuedDate
     *
     * @throws \DvsaCommonApi\Service\Exception\ServiceException
     */
    public function isAnyWithDifferentV5cReferenceIssuedAfter($registration, $v5cReference, $issuedDate)
    {
        $exception = new ServiceException(null);
        $exception->addError(
            'TODO(PT): To be implemented when a placement of v5c is known.',
            ServiceException::DEFAULT_STATUS_CODE
        );

        throw $exception;
    }

    /**
     * Get the latest MOT Test by vehicleId and Result.
     *
     * @param string $vehicleId
     * @param string $status
     * @param string $issuedDate
     *
     * @return MotTest
     */
    public function getLatestMotTestByVehicleIdAndResult(
        $vehicleId,
        $status = MotTestStatusName::PASSED,
        $issuedDate = false
    )
    {
        $testTypeCodes = \DvsaCommon\Domain\MotTestType::getExpiryDateDefiningTypes();

        $qb = $this->createQueryBuilder('t')
            ->innerJoin('t.motTestType', 'tt')
            ->innerJoin('t.status', 'ts')
            ->where('t.vehicle = :vehicleId')
            ->andWhere('t.completedDate IS NOT NULL')
            ->andWhere('tt.code IN (:codes)')
            ->andWhere('ts.name = :status')
            ->orderBy('t.completedDate', 'DESC')
            ->setParameter('vehicleId', $vehicleId)
            ->setParameter('status', $status)
            ->setParameter('codes', $testTypeCodes)
            ->setMaxResults(1);

        if ($issuedDate) {
            $qb->andWhere('t.issuedDate < :issuedDate')
                ->setParameter('issuedDate', $issuedDate);
        }

        $result = $qb->getQuery()->getResult();

        return array_shift($result);
    }

    /**
     * Get the latest MOT Test by registration number and Result.
     *
     * @param string $vrm
     * @param string $status
     * @param string $issuedDate
     * @param array $excludeCodes
     *
     * @return MotTest
     */
    public function findLatestMotTestByVrmAndResult(
        $vrm,
        $status = MotTestStatusName::PASSED,
        $issuedDate,
        $excludeCodes = [
            MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING,
            MotTestTypeCode::ROUTINE_DEMONSTRATION_TEST,
        ]
    )
    {
        $qb = $this
            ->createQueryBuilder('t')
            ->innerJoin('t.motTestType', 'tt')
            ->innerJoin('t.status', 'ts')
            ->innerJoin('t.vehicle', 'v')
            ->where('v.registration = :vrm')
            ->andWhere('t.completedDate IS NOT NULL')
            ->andWhere('tt.code NOT IN (:codes)')
            ->andWhere('ts.name = :status')
            ->andWhere('t.issuedDate < :issuedDate')
            ->orderBy('t.completedDate', 'DESC')
            ->setParameter('vrm', $vrm)
            ->setParameter('status', $status)
            ->setParameter('issuedDate', $issuedDate)
            ->setParameter('codes', $excludeCodes)
            ->setMaxResults(1);

        $result = $qb->getQuery()->getResult();

        return array_shift($result);
    }

    /**
     * Retrieve the latest MOT test number of a specific status by vehicle ID.
     *
     * @param int $vehicleId Non-obfuscated vehicle ID
     * @param string $status Status of MOT test to retrieve, default passed
     *
     * @throws NotFoundException
     *
     * @return null|string Numeric MOT test number or null if no test with $status exists for vehicle ID
     */
    public function getLatestMotTestIdByVehicleId($vehicleId, $status = MotTestStatusName::PASSED)
    {
        $qb = $this->createQueryBuilder('t')
            ->select('t.number')
            ->innerJoin('t.motTestType', 'tt')
            ->innerJoin('t.status', 'ts')
            ->where('t.vehicle = :vehicleId')
            ->andWhere('t.completedDate IS NOT NULL')
            ->andWhere('tt.code IN (:codes)')
            ->andWhere('ts.name = :status')
            ->orderBy('t.completedDate', 'DESC')
            ->setParameter('vehicleId', $vehicleId)
            ->setParameter('status', $status)
            ->setParameter(
               'codes', [
                MotTestTypeCode::NORMAL_TEST,
                MotTestTypeCode::PARTIAL_RETEST_LEFT_VTS,
                MotTestTypeCode::PARTIAL_RETEST_REPAIRED_AT_VTS,
                MotTestTypeCode::RE_TEST,
                MotTestTypeCode::INVERTED_APPEAL,
                MotTestTypeCode::STATUTORY_APPEAL,
               ])
            ->setMaxResults(1);

        if ($result = $qb->getQuery()->getResult()) {
            return $result[0]['number'];
        }

        throw new NotFoundException('MOT test with status ' . $status . ' for vehicle ' . $vehicleId);
    }

    /**
     * Get all complete and in-progress mot tests by vehicleId.
     *
     * @param int $vehicleId
     * @param int $maxResults
     *
     * @return array [DvsaEntities\Entity\MotTest]
     */
    public function getLatestMotTestsByVehicleId($vehicleId, $maxResults = 100)
    {
        $qb = $this->createQueryBuilder('mt')
            ->addSelect('v, vt, t, rfr', 'rfrMarkedAsRepaired')
            ->innerJoin('mt.vehicle', 'v')
            ->innerJoin('mt.vehicleTestingStation', 'vt')
            ->innerJoin('mt.tester', 't')
            ->innerJoin('mt.motTestType', 'tt')
            ->leftJoin('mt.motTestReasonForRejections', 'rfr')
            ->leftJoin('rfr.markedAsRepaired', 'rfrMarkedAsRepaired')
            ->where('mt.vehicle = :vehicleId')
            ->orderBy('mt.startedDate', 'DESC')
            ->setParameter('vehicleId', $vehicleId)
            ->setMaxResults($maxResults);

        return $qb->getQuery()->getResult();
    }

    /**
     * Retrieves MOT test entity and if it is not found throws an exception.
     *
     * @param $id
     *
     * @throws NotFoundException
     *
     * @return null|MotTest
     */
    public function getMotTest($id)
    {
        $motTest = $this->createQueryBuilder('mt')
            ->select('mt, rfr, t, v, btr3, btr12, o, vts')
            ->andWhere('mt.id = :id')
            ->leftJoin('mt.tester', 't')
            ->leftJoin('mt.vehicle', 'v')
            ->leftJoin('mt.vehicleTestingStation', 'vts')
            ->leftJoin('mt.motTestReasonForRejections', 'rfr')
            ->leftJoin('mt.brakeTestResultClass3AndAboveHistory', 'btr3')
            ->leftJoin('mt.brakeTestResultClass12History', 'btr12')
            ->leftJoin('mt.organisation', 'o')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$motTest) {
            throw new NotFoundException('Mot Test', $id);
        }

        return $motTest;
    }

    /**
     * Get the odometer history for a given vehicle id.
     *
     * @param int $vehicleId
     * @param DateTime $dateTo
     * @param array [MotTestTypeCode] $optionalMotTestTypeCodes (default = null)
     * @param int $limit (default = 4)
     *
     * @return array
     */
    public function getOdometerHistoryForVehicleId($vehicleId, DateTime $dateTo = null, array $optionalMotTestTypeCodes = null, $limit = 4)
    {
        $qb = $this->_em->createQueryBuilder();

        $codes = [
            MotTestTypeCode::RE_TEST,
            MotTestTypeCode::NORMAL_TEST,
            MotTestTypeCode::INVERTED_APPEAL,
            MotTestTypeCode::STATUTORY_APPEAL,
        ];

        if (!empty($optionalMotTestTypeCodes)) {
            $codes = array_merge($codes, $optionalMotTestTypeCodes);
        }

        $qb->select(
            't.issuedDate, 
            t.odometerValue AS value, 
            t.odometerUnit AS unit, 
            ts.name AS status,
            t.odometerResultType AS resultType,
            DATE(t.issuedDate) as dtIssuedDate'
        )
            ->from($this->getEntityName(), 't')
            ->innerJoin('t.motTestType', 'tt')
            ->innerJoin('t.status', 'ts')
            ->andWhere('t.vehicle = :vehicleId')
            ->andWhere('ts.name = :name')
            ->andWhere('tt.code IN (:codes)')
            ->orderBy('t.issuedDate', 'DESC')
            ->setMaxResults($limit);

        if ($dateTo != null) {
            $qb->andWhere('t.startedDate <= :dateTo')
                ->setParameter('dateTo', $dateTo->format('Y-m-d H:i:s'));
        }

        $qb->setParameter('vehicleId', $vehicleId)
            ->setParameter('name', MotTestStatusName::PASSED)
            ->setParameter('codes', $codes);

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Get odometer reading for id.
     *
     * @param $id
     *
     * @return mixed
     */
    public function getOdometerReadingForId($id)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('t.odometerValue AS value, t.odometerUnit AS unit, t.odometerResultType AS resultType')
            ->from($this->getEntityName(), 't')
            ->where('t.id = ?0')
            ->setMaxResults(1);

        $qb->setParameter(0, $id);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * This function allow us to paginate all the database to avoid memory limit.
     *
     * @param int $start
     * @param int $offset
     * @param string $orderBy
     * @param int $hydrateMode
     *
     * @return array
     *
     * @todo seems to be not used
     */
    public function getAllDataForEsIngestion(
        $start,
        $offset,
        $orderBy = 'test.id',
        $hydrateMode = Query::HYDRATE_OBJECT
    )
    {
        $qb = $this->createQueryBuilder('test')
            ->addSelect(['model', 'make'])
            ->leftJoin('test.make', 'make')
            ->leftJoin('test.model', 'model')
            ->orderBy($orderBy);

        $paginate = new Paginator($qb->getQuery(), $fetchJoinCollection = true);
        $paginate
            ->getQuery()
            ->setFirstResult($start)
            ->setMaxResults($offset)
            ->setHydrationMode($hydrateMode);

        return $paginate;
    }

    /**
     * Generic function to get MOT test count.
     *
     * @param $whereString
     * @param $whereParam
     * @param $whereValue
     * @param bool $getTestLogsForVts
     *
     * @return mixed
     */
    private function getCountOfMotTests($whereString, $whereParam, $whereValue, $getTestLogsForVts = false)
    {
        $sql = $this->getMotTestCountQuery($whereString, $getTestLogsForVts);

        $sql = $this->addMotTestSpecificConstraints($sql);

        //  ----  prepare statement and bind params   ----
        $em = $this->getEntityManager();
        $sql = $em->getConnection()->prepare($sql);

        $sql->bindValue($whereParam, $whereValue);

        $this->bindMotTestSpecificConstraints($sql);

        $sql->execute();

        return $sql->fetch();
    }

    /**
     * This function is responsible to get the number of mot test realised for
     * last 365 days, previous month, previous week and today.
     *
     * @param int $organisationId
     *
     * @return array eg. ['year' => 1234, 'month' => 9999, 'week' => 8888, 'today' => 777]
     */
    public function getCountOfMotTestsSummary($organisationId)
    {
        return $this->getCountOfMotTests(
            't.organisation_id = :ORGANISATION_ID',
            'ORGANISATION_ID',
            $organisationId,
            false
        );
    }

    /**
     * @param $siteId
     *
     * @return mixed
     */
    public function getCountOfSiteMotTestsSummary($siteId)
    {
        return $this->getCountOfMotTests('t.site_id = :SITE_ID AND t.organisation_id = s.organisation_id', 'SITE_ID', $siteId, true);
    }

    /**
     * This function is responsible to get the number of mot test realised for
     * last 365 days, previous month, previous week and today by Tester.
     *
     * @param int $testerId
     *
     * @return array eg. ['year' => 1234, 'month' => 9999, 'week' => 8888, 'today' => 777]
     */
    public function getCountOfTesterMotTestsSummary($testerId)
    {
        return $this->getCountOfMotTests('t.person_id = :PERSON_ID', 'PERSON_ID', $testerId, false);
    }


    /**
     * Prepare statement to get mot tests log data.
     *
     * @param MotTestSearchParam $searchParam
     *
     * @return NativeQueryBuilder
     */
    private function prepareMotTestLogCountQuery(MotTestSearchParam $searchParam)
    {
        //  --  prepare sub query   --
        $qb = new NativeQueryBuilder();

        $qb->select('count(mt.id) AS count', 'countPart');

        $qb
            ->from($this->getClassMetadata()->getTableName(), 'mt')
            ->join('mot_test_type', 'tt', 'tt.id = mt.mot_test_type_id');

        if ($searchParam->getTesterId()) {
            $qb->andwhere('mt.person_id = :TESTER_ID')
                ->setParameter('TESTER_ID', $searchParam->getTesterId());
        }

        if ($searchParam->getOrganisationId()) {
            $qb->andWhere('mt.organisation_id = :ORGANISATION_ID')
                ->setParameter('ORGANISATION_ID', $searchParam->getOrganisationId());
        }

        if ($searchParam->getSiteId()) {
            $qb->andWhere('mt.site_id = :SITE_ID')
                ->setParameter('SITE_ID', $searchParam->getSiteId());
        }

        $testType = $searchParam->getTestType();
        if (!empty($testType)) {
            $query = [];
            foreach ($testType as $key => $item) {
                $query[] = ':TEST_TYPE' . $key;

                $qb->setParameter('TEST_TYPE' . $key, $item);
            }

            $qb->andwhere('tt.code IN (' . implode(',', $query) . ')');
        }

        $statuses = $searchParam->getStatus();

         if ($searchParam->getDateFrom() || $searchParam->getDateTo()) {
            if (in_array(MotTestStatusName::ACTIVE, $statuses, true))
            {
                $qb->andwhere('
                (
                    (
                    mt.completed_date IS NULL
                    AND mt.started_date BETWEEN :DATE_FROM AND :DATE_TO
                    )
                OR (
                    mt.completed_date IS NOT NULL
                    AND mt.completed_date BETWEEN :DATE_FROM AND :DATE_TO
                    )
                )
                ')
                    ->setParameter('DATE_FROM', $searchParam->getDateFrom() ?: (new \DateTime())->sub(new \DateInterval('P1D')))
                    ->setParameter('DATE_TO', $searchParam->getDateTo() ?: new \DateTime());}
            else {
                $qb->andwhere('mt.completed_date BETWEEN :DATE_FROM AND :DATE_TO ')
                    ->setParameter('DATE_FROM', $searchParam->getDateFrom() ?: (new \DateTime())->sub(new \DateInterval('P1D')))
                    ->setParameter('DATE_TO', $searchParam->getDateTo() ?: new \DateTime());
            }
        }

        /** Note: Test logs at the minute has no means of searching on:
         *  Registration, Vin, or Vehicle Id, Site Number or Status
         * Debatable if the following conditions should even be included
         * as they only serve to confuse.
         */
        if ($searchParam->getRegistration() || $searchParam->getVin()) {
            $qb
                ->join('vehicle', 'v', 'v.id = mt.vehicle_id AND v.version = mt.vehicle_version', NativeQueryBuilder::JOIN_TYPE_LEFT)
                ->join('vehicle_hist', 'vh', 'vh.id = mt.vehicle_id AND vh.version = mt.vehicle_version', NativeQueryBuilder::JOIN_TYPE_LEFT);
        }

        if ($searchParam->getRegistration()) {
            $qb->andwhere('(v.registration = :VRM OR vh.registration = :VHVRM)')
                ->setParameter('VRM', $searchParam->getRegistration())
                ->setParameter('VHVRM', $searchParam->getRegistration());
        }

        if ($searchParam->getVin()) {
            $qb->andwhere('(v.vin = :VIN OR vh.vin = :VHVIN)')
                ->setParameter('VIN', $searchParam->getVin())
                ->setParameter('VHVIN', $searchParam->getVin());
        }

        if ($searchParam->getVehicleId()) {
            $qb->andwhere('mt.vehicle_id = :VEHICLE_ID')
                ->setParameter('VEHICLE_ID', $searchParam->getVehicleId());
        }

        if ($searchParam->getSiteNumber()) {
            $qb->join('site', 's', 's.id = mt.site_id');

            $qb->andwhere('s.site_number = :SITE_NR')
                ->setParameter('SITE_NR', $searchParam->getSiteNumber());
        }

        if (!empty($statuses) && in_array(MotTestStatusName::ACTIVE, $statuses, true)) {
            // Optimisation : Not including statuses in query unless Active is included,
            // because likelihood is that search params just includes all completed statues.
            // Active tests will otherwise be excluded by completed_date section below.

            $qb->join('mot_test_status', 'ts', 'ts.id = mt.status_id');

            $query = [];
            foreach ($statuses as $key => $item) {
                $query[] = ':STATUS' . $key;

                $qb->setParameter('STATUS' . $key, $item);
            }

            $qb->andwhere('ts.name IN (' . implode(',', $query) . ')');
        }

        return $qb;
    }

    /**
     * Prepare statement to get mot tests log data.
     *
     * @param MotTestSearchParam $searchParam
     *
     * @return NativeQueryBuilder
     */
    private function prepareMotTestLogResultQuery(MotTestSearchParam $searchParam)
    {
        $useSubQuery = false;
        $subQuerySql = $this->getClassMetadata()->getTableName();

        if ($searchParam->getFormat() === SearchParamConst::FORMAT_DATA_CSV) {
            $useSubQuery = false;
        }
        else
        {   $orderBy = $searchParam->getSortColumnNameDatabase();
            if (!empty($orderBy)) {
                if (!is_array($orderBy)) {
                    if ($orderBy === 'testDate' ||
                        $orderBy === 'mt.id'
                    ) {
                        $useSubQuery = true;
                    }
                }
            }

            // Override above to only use subquery for AE level
            // This is only place where a real benefit has been found in testing
            // Note: The testing was limited to hand running queries on an inactive
            // acceptance database so god knows what to expect with an active
            // DB with specific new indexes where tests are being created during execution.
            if ($searchParam->getSiteId() || $searchParam->getTesterId() ) {
                $useSubQuery = false;
            }
        }

        if ($useSubQuery) {
            /**
             * It has been found to be more efficient to use a cut-down sub-query
             * for AE level test results with the default testDate order by clause.
             * The subquery takes care of ordering and paging when the display
             * ordering involves only fields from the mot_test (current or history)
             * tables, rather than joining with all the other tables first.
             */

            /* Re-use the count query to construct the sub-query structure */
            /** @var NativeQueryBuilder $subQuery */
            $subQuery = $this->prepareMotTestLogCountQuery($searchParam);

            // take the necessary fields out of mot_test in the sub-query.
            $subQuery
                ->resetPart('select', 'countPart')
                ->select('mt.id')
                ->select('COALESCE(mt.completed_date, mt.started_date) AS testDate')
                ->select('mt.number')
                ->select('mt.client_ip')
                ->select('mt.status_id')
                ->select('mt.vehicle_id')
                ->select('mt.vehicle_version')
                ->select('mt.organisation_id')
                ->select('mt.site_id')
                ->select('mt.completed_date')
                ->select('mt.started_date')
                ->select('tt.description')
                ->select('mt.person_id')
                ->select('mt.created_by')
                ->select('mt.created_on')
                ->select('mt.mot_test_type_id');

            //  logical block: define order by statement
            $orderBy = $searchParam->getSortColumnNameDatabase();
            if (!empty($orderBy)) {
                if (!is_array($orderBy)) {
                    $orderBy = [$orderBy];
                }

                foreach ($orderBy as $order) {
                    $subQuery->orderBy($order . ' ' . $searchParam->getSortDirection());
                }
            }

            //  logical block: define pagination statement
            if ($searchParam->getStart() > 0) {
                $subQuery->setOffset($searchParam->getStart());
            }

            // Limit the subquery to the number of rows required for paging.
            if ($searchParam->getRowCount() > 0) {
                $subQuery->setLimit($searchParam->getRowCount());
            }

            $subQuerySql = '(' . $subQuery->getSql() . ')';
        }

        //  --  prepare main query   --
        $qb = new NativeQueryBuilder();

        $qb
            ->select('COALESCE(mt.completed_date, mt.started_date) AS testDate')
            ->select('TIMESTAMPDIFF(SECOND, mt.started_date, COALESCE(mt.completed_date, mt.started_date)) as testDuration',
                'additionalFields')
            ->select('mt.number, mt.client_ip, ts.name AS status')
            ->select('COALESCE(vh.registration, v.registration) AS registration')
            ->select('COALESCE(vh.vin,v.vin) AS vin')
            ->select('COALESCE(vma.name,vhma.name) AS makeName')
            ->select('COALESCE(vmo.name,vhmo.name) AS modelName')
            ->select('COALESCE(vc.name,vhvc.name)  AS vehicle_class')
            ->select('s.site_number AS siteNumber, p.username as userName, tt.description as testTypeName')
            ->select('emr.emergency_log_id AS emLogId');

        $qb
            ->from($subQuerySql, 'mt')
            ->join('mot_test_type', 'tt', 'tt.id = mt.mot_test_type_id')
            ->join('vehicle', 'v', 'v.id = mt.vehicle_id AND v.version = mt.vehicle_version', NativeQueryBuilder::JOIN_TYPE_LEFT)
            ->join('vehicle_hist', 'vh', 'vh.id = mt.vehicle_id AND vh.version = mt.vehicle_version', NativeQueryBuilder::JOIN_TYPE_LEFT)
            ->join('model_detail', 'md', 'md.id = v.model_detail_id', NativeQueryBuilder::JOIN_TYPE_LEFT)
            ->join('model', 'vmo', 'vmo.id = md.model_id', NativeQueryBuilder::JOIN_TYPE_LEFT)
            ->join('make', 'vma', 'vma.id = vmo.make_id', NativeQueryBuilder::JOIN_TYPE_LEFT)
            ->join('model_detail', 'vhmd', 'vhmd.id = vh.model_detail_id', NativeQueryBuilder::JOIN_TYPE_LEFT)
            ->join('model', 'vhmo', 'vhmo.id = vhmd.model_id', NativeQueryBuilder::JOIN_TYPE_LEFT)
            ->join('make', 'vhma', 'vhma.id = vhmo.make_id', NativeQueryBuilder::JOIN_TYPE_LEFT)
            ->join('person', 'p', 'p.id = mt.person_id')
            ->join('mot_test_emergency_reason', 'emr', 'emr.id = mt.id', NativeQueryBuilder::JOIN_TYPE_LEFT)
            ->join('emergency_log', 'eml', 'eml.id = emr.emergency_log_id', NativeQueryBuilder::JOIN_TYPE_LEFT)
            ->join('emergency_reason_lookup', 'emrl', 'emrl.id = emr.emergency_reason_lookup_id', NativeQueryBuilder::JOIN_TYPE_LEFT)
            ->join('comment', 'emcm', 'emcm.id = emr.emergency_reason_comment_id', NativeQueryBuilder::JOIN_TYPE_LEFT)
            ->join('vehicle_class', 'vc', 'vc.id = md.vehicle_class_id', NativeQueryBuilder::JOIN_TYPE_LEFT)
            ->join('vehicle_class', 'vhvc', 'vhvc.id = vhmd.vehicle_class_id', NativeQueryBuilder::JOIN_TYPE_LEFT)
            ->join('site', 's', 's.id = mt.site_id')
            ->join('mot_test_status', 'ts', 'ts.id = mt.status_id');

        if ($searchParam->getTesterId()) {
                    $qb->andwhere('mt.person_id = :TESTER_ID')
                        ->setParameter('TESTER_ID', $searchParam->getTesterId());
        }

        if ($searchParam->getOrganisationId()) {
            $qb->andWhere('mt.organisation_id = :ORGANISATION_ID')
                ->setParameter('ORGANISATION_ID', $searchParam->getOrganisationId());
        }

        if ($searchParam->getSiteId()) {
            $qb->andWhere('mt.site_id = :SITE_ID')
                ->setParameter('SITE_ID', $searchParam->getSiteId());
        }

        $testType = $searchParam->getTestType();
        if (!empty($testType)) {
            $query = [];
            foreach ($testType as $key => $item) {
                $query[] = ':TEST_TYPE' . $key;

                $qb->setParameter('TEST_TYPE' . $key, $item);
            }

            $qb->andwhere('tt.code IN (' . implode(',', $query) . ')');
        }

        $statuses = $searchParam->getStatus();

        if ($searchParam->getDateFrom() || $searchParam->getDateTo()) {
            if (in_array(MotTestStatusName::ACTIVE, $statuses, true)) {
                $qb->andwhere('
            (  (  mt.completed_date IS NULL
                  AND mt.started_date BETWEEN :DATE_FROM AND :DATE_TO
               )
            OR ( mt.completed_date IS NOT NULL
                AND mt.completed_date BETWEEN :DATE_FROM AND :DATE_TO
               )
            )
            ')
                    ->setParameter('DATE_FROM', $searchParam->getDateFrom() ?: (new \DateTime())->sub(new \DateInterval('P1D')))
                    ->setParameter('DATE_TO', $searchParam->getDateTo() ?: new \DateTime());
            } else {
                $qb->andwhere('mt.completed_date BETWEEN :DATE_FROM AND :DATE_TO ')
                    ->setParameter('DATE_FROM', $searchParam->getDateFrom() ?: (new \DateTime())->sub(new \DateInterval('P1D')))
                    ->setParameter('DATE_TO', $searchParam->getDateTo() ?: new \DateTime());
            }
        }

        /** Note: Test logs at the minute has no means of using:
         *  Registration, Vin, or Vehicle Id, SiteNumber or Status for searching.
         * Debatable if the following conditions should even be included
         * as they only serve to confuse.
         */

        if ($searchParam->getRegistration()) {
            $qb->andwhere('(v.registration = :VRM OR vh.registration = :VHVRM)')
                ->setParameter('VRM', $searchParam->getRegistration())
                ->setParameter('VHVRM', $searchParam->getRegistration());
        }

        if ($searchParam->getVin()) {
            $qb->andwhere('(v.vin = :VIN OR vh.vin = :VHVIN)')
                ->setParameter('VIN', $searchParam->getVin())
                ->setParameter('VHVIN', $searchParam->getVin());
        }

        if ($searchParam->getVehicleId()) {
            $qb->andwhere('mt.vehicle_id = :VEHICLE_ID')
                ->setParameter('VEHICLE_ID', $searchParam->getVehicleId());
        }

        if ($searchParam->getSiteNumber()) {
            $qb->join('site', 's', 's.id = mt.site_id');

            $qb->andwhere('s.site_number = :SITE_NR')
                ->setParameter('SITE_NR', $searchParam->getSiteNumber());
        }

        if (!empty($statuses) && in_array(MotTestStatusName::ACTIVE, $statuses, true)) {
            // Optimisation : Not including statuses in query unless Active is included,
            // because likelihood is that search params just includes all completed statues.
            // Active tests will otherwise be excluded by completed_date section below.

            $qb->join('mot_test_status', 'ts', 'ts.id = mt.status_id');

            $query = [];
            foreach ($statuses as $key => $item) {
                $query[] = ':STATUS' . $key;

                $qb->setParameter('STATUS' . $key, $item);
            }
            $qb->andwhere('ts.name IN (' . implode(',', $query) . ')');
        }

        if ($searchParam->getFormat() === SearchParamConst::FORMAT_DATA_CSV) {
            $qb
                ->orderBy('siteNumber ASC')
                ->orderBy('testDate ASC')
                ->select(
                    'CASE WHEN eml.id IS NOT NULL THEN emp.username ELSE NULL END AS emRecTester,
                    CASE WHEN eml.id IS NOT NULL THEN mt.created_on ELSE NULL END AS emRecDateTime,
                    COALESCE(emcm.comment, emrl.name) AS emReason,
                    eml.number AS emCode',
                    'emergency'
                )
                ->join('person', 'emp', 'emp.id = mt.created_by');
        }
        else {
            //  logical block: define order by statement
            $orderBy = $searchParam->getSortColumnNameDatabase();
            if (!empty($orderBy)) {
                if (!is_array($orderBy)) {
                    $orderBy = [$orderBy];
                }

                foreach ($orderBy as $order) {
                    $qb->orderBy($order . ' ' . $searchParam->getSortDirection());
                }
            }
        }

        if (!$useSubQuery) {
            //Offset gets applied in Sub-Query when it is being used
            if ($searchParam->getStart() > 0) {
                $qb->setOffset($searchParam->getStart());
            }
        }

        if ($searchParam->getRowCount() > 0) {
            $qb->setLimit($searchParam->getRowCount());
        }

        return $qb;
    }

    public function getMotTestLogsResult(MotTestSearchParam $searchParam)
    {
        $qb = $this->prepareMotTestLogResultQuery($searchParam);
        $sql = $this->getEntityManager()->getConnection()->prepare($qb->getSql());
        $qb->bindParametersToStatement($sql);
        $sql->execute();

        return $sql->fetchAll();
    }

    /**
     * @param \DvsaEntities\DqlBuilder\SearchParam\MotTestSearchParam $searchParam
     *
     * @return mixed
     */
    public function getMotTestLogsResultCount(MotTestSearchParam $searchParam)
    {
        $qb = $this->prepareMotTestLogCountQuery($searchParam);
        $sql = $this->getEntityManager()->getConnection()->prepare($qb->getSql());
        $qb->bindParametersToStatement($sql);
        $sql->execute();

        return $sql->fetch();
    }

    /**
     * NOT FINISHED.
     *
     * @param MotTestSearchParam $searchParam
     * @param array $optionalMotTestTypes
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function prepareMotSearch(MotTestSearchParam $searchParam, array $optionalMotTestTypes)
    {
        $qb = $this->createQueryBuilder('test');

        $qb
            ->leftJoin(Site::class, 'site', 'WITH', 'site.id = test.vehicleTestingStation')
            ->leftJoin(Vehicle::class, 'vehicle', 'WITH', 'vehicle.id = test.vehicle')
            ->leftJoin(VehicleHistory::class, 'vh', 'WITH', 'vh.vehicle = vehicle.id AND vh.version = test.vehicleVersion')
            ->innerJoin(ModelDetail::class, 'modelDetail', 'WITH', 'modelDetail.id = vehicle.modelDetail')
            ->leftJoin(Model::class, 'model', 'WITH', 'model.id = modelDetail.model')
            ->leftJoin(Make::class, 'make', 'WITH', 'make.id = model.make')
            ->innerJoin(MotTestType::class, 'testType', 'WITH', 'test.motTestType = testType.id')
            ->innerJoin(Person::class, 'tester', 'WITH', 'tester.id = test.tester')
            ->andWhere('testType.code IN (:testTypes)')
            ->setParameter('testTypes', $this->getMotTestHistoryTestTypes($optionalMotTestTypes));

        if ($searchParam->getDateFrom()) {
            $qb->andwhere('test.startedDate >= :DATE_FROM')
                ->setParameter('DATE_FROM', $searchParam->getDateFrom());
        }
        if ($searchParam->getDateTo()) {
            $qb->andwhere('test.startedDate <= :DATE_TO')
                ->setParameter('DATE_TO', $searchParam->getDateTo()->add(new \DateInterval('P1M')));
        }

        if ($searchParam->getSiteNumber()) {
            $qb->andwhere('site.siteNumber = :SITE_NR')
                ->setParameter('SITE_NR', $searchParam->getSiteNumber());
        }

        if ($searchParam->getTesterId()) {
            $qb->andwhere('test.tester = :TESTER_ID')
                ->setParameter('TESTER_ID', $searchParam->getTesterId());
        }

        if ($searchParam->getRegistration()) {
            $qb->andwhere('vehicle.registration = :VRM OR vh.registration = :VRM')
                ->setParameter('VRM', $searchParam->getRegistration());
        }

        if ($searchParam->getVin()) {
            $qb->andwhere('vehicle.vin = :VIN')
                ->setParameter('VIN', $searchParam->getVin());
        }

        if ($searchParam->getVehicleId()) {
            $qb->andwhere('test.vehicle = :VEHICLE_ID')
                ->setParameter('VEHICLE_ID', $searchParam->getVehicleId());
        }
        if ($searchParam->getTestNumber()) {
            $qb->andwhere('test.number = :TEST_NUMBER')
                ->setParameter('TEST_NUMBER', $searchParam->getTestNumber());
        }

        return $qb;
    }

    /**
     * @param MotTestSearchParam $searchParam
     * @param array $optionalMotTestTypes
     *
     * @return array
     */
    public function getMotTestSearchResult(MotTestSearchParam $searchParam, array $optionalMotTestTypes)
    {
        $dql = $this->prepareMotSearch($searchParam, $optionalMotTestTypes);

        $orderBy = $searchParam->getSortColumnNameDatabase();
        if (is_array($orderBy)) {
            foreach ($orderBy as $order) {
                $dql->addOrderBy($order, $searchParam->getSortDirection());
            }
        } else {
            $dql->orderBy($orderBy, $searchParam->getSortDirection());
        }
        if ($searchParam->getStart() > 0) {
            $dql->setFirstResult($searchParam->getStart());
        }

        if ($searchParam->getRowCount() > 0) {
            $dql->setMaxResults($searchParam->getRowCount());
        }

        $query = $dql->getQuery()
            ->setFetchMode(MotTest::class, 'make', ClassMetadata::FETCH_EAGER)
            ->setFetchMode(MotTest::class, 'model', ClassMetadata::FETCH_EAGER);

        return $query->getResult();
    }

    /**
     * @param MotTestSearchParam $searchParam
     * @param array $optionalMotTestTypes
     *
     * @return int
     */
    public function getMotTestSearchResultCount(MotTestSearchParam $searchParam, array $optionalMotTestTypes)
    {
        $dql = $this->prepareMotSearch($searchParam, $optionalMotTestTypes);
        $dql->select('count(test)');

        return (int)$dql->getQuery()->getSingleScalarResult();
    }

    /**
     * Checks whether the supplied User ID is the same as the one held against
     * the specified MOT Test.
     *
     * @param $testerId
     * @param $motTestNumber
     *
     * @return bool
     */
    public function isTesterForMot($testerId, $motTestNumber)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('mt.number')
            ->from(MotTest::class, 'mt')
            ->andwhere('mt.tester = :tester')
            ->andWhere('mt.number = :motTestNumber')
            ->setMaxResults(1);

        $qb->setParameter('tester', $testerId);
        $qb->setParameter('motTestNumber', str_replace(' ', '', $motTestNumber));

        $result = $qb->getQuery()->getOneOrNullResult();

        return $result !== null;
    }

    /**
     * @param int $lastSurveyMotTestId
     *
     * @return int
     */
    public function getNormalMotTestCountSinceLastSurvey($lastSurveyMotTestId)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('COUNT(mt.id)')
            ->from(MotTest::class, 'mt')
            ->join('mt.motTestType', 't')
            ->where('t.code = :testTypeCode')
            ->andWhere('mt.id > :lastSurveyMotTestId');

        $qb->setParameter('testTypeCode', MotTestTypeCode::NORMAL_TEST);
        $qb->setParameter('lastSurveyMotTestId', $lastSurveyMotTestId);

        $count = (int)$qb->getQuery()->getSingleScalarResult();

        return $count;
    }

    /**
     * @return int
     */
    public function getLastMotTestId()
    {
        $result = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('MAX(mt.id)')
            ->from($this->getEntityName(), 'mt')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleScalarResult();

        if (!$result) {
            return 0;
        }

        return (int)$result;
    }

    /**
     * Finds an odometer reading for a given MOT test
     *
     * @param int $motTestNumber
     *
     * @return null|OdometerReading
     */
    public function findReadingForTest($motTestNumber)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select('
            mt.odometerValue AS value,
            mt.odometerUnit AS unit,
            mt.odometerResultType AS resultType,
            mt.issuedDate AS issuedDate'
        )
            ->from(MotTest::class, 'mt')
            ->where('mt.number = :motTestNumber')
            ->setParameter('motTestNumber', $motTestNumber);

        $result = $qb->getQuery()->getOneOrNullResult();

        return $this->odometerReadingToDto($result);
    }

    /**
     * Searches for a previous test odometer reading looking from a given mot test's perspective.
     * The test must be a normal test either PASSED or FAILED.
     *
     * @param $motTestNumber
     *
     * @return OdometerReading
     */
    public function findPreviousReading($motTestNumber)
    {
        $sql = strtr('
            SELECT
                pmt.odometer_value AS  value,
                pmt.odometer_unit AS unit,
                pmt.odometer_result_type AS resultType,
                pmt.issued_date AS issuedDate
            FROM
                %TABLE_NAME% AS mt
                    INNER JOIN
				mot_test_type AS tt ON tt.id = mt.mot_test_type_id
                    INNER JOIN
                %TABLE_NAME% AS pmt ON pmt.vehicle_id = mt.vehicle_id
            WHERE
                mt.number = :motTestNumber
                AND mt.vehicle_id = pmt.vehicle_id
                AND mt.number <> pmt.number
                AND tt.code IN (:testTypeNormal, :testTypeRetest)
            ORDER BY pmt.issued_date DESC
                LIMIT 1
        ', ['%TABLE_NAME%' => $this->getClassMetadata()->getTableName()]);

        $query = $this->getEntityManager()->getConnection()->prepare($sql);

        $query->bindValue('motTestNumber', $motTestNumber);
        $query->bindValue("testTypeNormal", MotTestTypeCode::NORMAL_TEST);
        $query->bindValue("testTypeRetest", MotTestTypeCode::RE_TEST);

        $query->execute();

        $result = $query->fetch();

        return $this->odometerReadingToDto($result);
    }

    /**
     * @param $vehicleId
     * @param $selectClause
     *
     * @return array
     */
    private function findInProgressTestDataForVehicle($vehicleId, $selectClause)
    {
        $demoTestTypes = [
            MotTestTypeCode::ROUTINE_DEMONSTRATION_TEST,
            MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING,
        ];
        $qb = $this->_em->createQueryBuilder();
        $testNotADemo = $qb->expr()->notIn('t.code', $demoTestTypes);

        return $qb->select($selectClause)
            ->from(MotTest::class, 'mt')
            ->join('mt.motTestType', 't')
            ->join('mt.status', 'ts')
            ->where($testNotADemo)
            ->andWhere('ts.name = :status')
            ->andWhere('mt.vehicle = :vehicleId')
            ->getQuery()
            ->setMaxResults(1)
            ->setParameter('vehicleId', $vehicleId)
            ->setParameter('status', MotTestStatusName::ACTIVE)
            ->getResult();
    }

    /**
     * @param array $optionalMotTestTypes
     *
     * @return array
     */
    private function getMotTestHistoryTestTypes(array $optionalMotTestTypes)
    {
        return array_merge(\DvsaCommon\Domain\MotTestType::getMotTestHistoryTypes(), $optionalMotTestTypes);
    }

    /**
     * @param string $whereString
     * @param bool $isQueryForVts
     *
     * @return string
     */
    private function getMotTestCountQuery($whereString, $isQueryForVts)
    {
        $joins = '';
        if ($isQueryForVts) {
            $joins = 'INNER JOIN site AS s ON s.id = t.site_id ';
        }

        return "
        SELECT COUNT(t.id) AS `year`,
            SUM(CASE
               WHEN t.completed_date BETWEEN LAST_DAY(CURRENT_DATE() - INTERVAL 2 MONTH) + INTERVAL 1 DAY
                   AND LAST_DAY(CURRENT_DATE()) - INTERVAL 1 MONTH + INTERVAL 1 DAY
               THEN 1
               ELSE 0
               END) AS `month`,
            SUM(CASE
                WHEN t.completed_date BETWEEN CURRENT_DATE() - INTERVAL WEEKDAY(CURRENT_DATE()) + 7 DAY
                     AND CURRENT_DATE() - INTERVAL WEEKDAY(CURRENT_DATE()) DAY
                THEN 1
                ELSE 0
                END ) AS `week`,
            SUM(
            CASE
            WHEN t.completed_date >= CURRENT_DATE()
            THEN 1
            ELSE 0
            END ) AS `today`
         FROM {$this->getClassMetadata()->getTableName()} AS t
         INNER JOIN mot_test_type AS tt ON  tt.id = t.mot_test_type_id
         {$joins}
         WHERE
             {$whereString}
         AND t.completed_date >= (CURRENT_DATE() - INTERVAL 1 YEAR + INTERVAL 1 DAY)
        ";
    }
    /**
     * @param string $sql
     *
     * @return string
     */
    private function addMotTestSpecificConstraints($sql)
    {
        //  --  add test type where clause --
        $whereParams = [];
        foreach (static::$testLogTestTypes as $key => $val) {
            $whereParams[] = ':' . $key;
        }
        $sql .= ' AND tt.code IN (' . implode(', ', $whereParams) . ')';

        return $sql;
    }

    /**
     * @param \Doctrine\DBAL\Driver\Statement $sql
     *
     * @return \Doctrine\DBAL\Driver\Statement
     */
    private function bindMotTestSpecificConstraints($sql)
    {
        //  --  bind test types --
        foreach (static::$testLogTestTypes as $key => $val) {
            $sql->bindValue($key, $val);
        }

        return $sql;
    }

    /**
     * @param $reading
     * @return OdometerReadingDto|null
     */
    private function odometerReadingToDto($reading)
    {
        if (!$reading || !array_filter($reading)) {
            return null;
        }

        $odometerReadingDto = new OdometerReadingDto();
        $odometerReadingDto->setValue($reading['value']);
        $odometerReadingDto->setUnit($reading['unit']);
        $odometerReadingDto->setResultType($reading['resultType']);
        $odometerReadingDto->setIssuedDate($reading['issuedDate']);

        return $odometerReadingDto;
    }

    /**
     * @return string
     */
    protected function getVehicleIndexName()
    {
        return 'fk_mot_test_current_vehicle';
    }

    /**
     * comparator function used to sort tests by issued date, completed date, site
     * intended to be used with call to usort passing in two arrays of MotTest entities
     * sorting performed in-memory to reduce impact on DB query
     * @param MotTest $testOne
     * @param MotTest $testTwo
     *
     * @return int
     * */
    private function compareTests($testOne, $testTwo)
    {
        $firstIssuedDate = $testOne->getIssuedDate()->getTimestamp();
        $secondIssuedDate = $testTwo->getIssuedDate()->getTimestamp();

        $firstCompletedDate = $testOne->getCompletedDate()->getTimestamp();
        $secondCompletedDate = $testTwo->getCompletedDate()->getTimestamp();

        $firstSite = $testOne->getVehicleTestingStation()->getSiteNumber();
        $secondSite = $testTwo->getVehicleTestingStation()->getSiteNumber();

        if ($firstIssuedDate === $secondIssuedDate) {
            if ($firstCompletedDate === $secondCompletedDate) {
                if ($firstSite === $secondSite) {
                    return 0;
                } else if ($firstSite > $secondSite) {
                    return 1;
                } else {
                    return -1;
                }
            } else if ($firstCompletedDate > $secondCompletedDate) {
                return 1;
            } else {
                return -1;
            }
        } else if ($firstIssuedDate > $secondIssuedDate) {
            return 1;
        } else {
            return -1;
        }
    }
}

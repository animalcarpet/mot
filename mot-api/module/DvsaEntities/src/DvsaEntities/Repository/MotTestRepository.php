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
use Doctrine\ORM\Tools\Pagination\Paginator;
use DvsaCommon\Constants\SearchParamConst;
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
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestType;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\Vehicle;
use DvsaMotApi\Helper\MysteryShopperHelper;

/**
 * MotTest Repository.
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

    public static $testLogOrganisationSiteStatuses = [
        'OSS_APPLIED' => OrganisationSiteStatusCode::APPLIED,
        'OSS_UNKNOWN' => OrganisationSiteStatusCode::UNKNOWN,
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
        $qb = $this
            ->createQueryBuilder('mt')
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
     * @param $vehicleId
     * @param DateTime $from
     * @param null $contingencyDto
     * @param bool $isMysteryShopper
     *
     * @return int
     */
    public function countNotCancelledTests($vehicleId, DateTime $from, $contingencyDto = null)
    {
        $qb = $this
            ->createQueryBuilder('mt')
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

        return (int) $qb->getQuery()->getSingleScalarResult();
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
        $qb = $this
            ->createQueryBuilder('mt')
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
     * @return int
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
        $qb = $this
            ->createQueryBuilder('mt')
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
     * @param int  $personId
     * @param bool $routine  To set the demo test type
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
     * @param int  $personId
     * @param bool $routine  To set the demo test type
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
        $qb = $this
            ->createQueryBuilder('mt')
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
            $qb
            ->innerJoin('mt.status', 'ts')
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

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $motTestNumber
     *
     * @return MotTest
     */
    public function findRetestForMotTest($motTestNumber)
    {
        $qb = $this
            ->createQueryBuilder('mt')
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
        $mtQb = $this
            ->createQueryBuilder('it')
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

        $qb = $this
            ->createQueryBuilder('t')
            ->select(['t', 'v', 'o', 'p', 'vts'])
            ->innerJoin('t.tester', 'p')
            ->innerJoin('t.vehicle', 'v')
            ->innerJoin('t.vehicleTestingStation', 'vts')
            ->innerJoin('t.motTestType', 'tt')
            ->innerJoin('t.status', 'ts')
            ->leftJoin('t.odometerReading', 'o')
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
            ->addSelect(['rfr', 'rfrMarkedAsRepaired', 'vts', 'dsbtc3a', 'dpbtc3a'])
            ->innerJoin('mt.motTestType', 'tt')
            ->innerJoin('mt.status', 's')
            ->leftJoin('mt.motTestReasonForRejections', 'rfr')
            ->leftJoin('rfr.markedAsRepaired', 'rfrMarkedAsRepaired')
            ->leftJoin('mt.vehicleTestingStation', 'vts')
            ->leftJoin('vts.defaultServiceBrakeTestClass3AndAbove', 'dsbtc3a')
            ->leftJoin('vts.defaultParkingBrakeTestClass3AndAbove', 'dpbtc3a')
            ->where('mt.number = :number')
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
        $qb = $this
            ->createQueryBuilder('mt')
            ->innerJoin('mt.motTestType', 't')
            ->innerJoin('mt.status', 'ts')
            ->where('ts.name IN (:statuses)')
            ->andWhere('t.code IN (:testTypes)')
            ->andWhere('mt.registration = :registration')
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

        $qb = $this
            ->createQueryBuilder('mt')
            ->innerJoin('mt.motTestType', 't')
            ->innerJoin('mt.status', 'ts')
            ->where('ts.name IN (:statuses)');

        $qb = is_callable($testTypeWhereClauseFunction) ?
            $testTypeWhereClauseFunction($qb) : $qb->andWhere('t.code IN (:testTypes)');

        $qb = $qb
            ->andWhere('mt.vehicle = :vehicleId')
            ->orderBy('mt.issuedDate', 'DESC')
            ->addOrderBy('mt.completedDate', 'DESC')
            ->addOrderBy('mt.vehicleTestingStation', 'ASC')
            ->setParameter('statuses', $statuses)
            ->setParameter('testTypes', $testTypes)
            ->setParameter('vehicleId', $vehicleId);

        if ($startDate !== null) {
            $qb
                ->andWhere('mt.issuedDate >= :startDate OR mt.issuedDate IS NULL')
                ->setParameter('startDate', $startDate);
        }

        return $qb->getQuery()->getResult();
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
            ->orderBy('mt.issuedDate', 'DESC')
            ->addOrderBy('mt.completedDate', 'DESC')
            ->addOrderBy('mt.vehicleTestingStation', 'ASC')
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

        return $qb->getQuery()->getResult();
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
    ) {
        $testTypeCodes = \DvsaCommon\Domain\MotTestType::getExpiryDateDefiningTypes();

        $qb = $this
            ->createQueryBuilder('t')
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
     * @param array  $excludeCodes
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
    ) {
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
     * @param int    $vehicleId Non-obfuscated vehicle ID
     * @param string $status    Status of MOT test to retrieve, default passed
     *
     * @throws NotFoundException
     *
     * @return null|string Numeric MOT test number or null if no test with $status exists for vehicle ID
     */
    public function getLatestMotTestIdByVehicleId($vehicleId, $status = MotTestStatusName::PASSED)
    {
        $qb = $this
            ->createQueryBuilder('t')
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
                MotTestTypeCode::NORMAL_TEST, //'NT'
                MotTestTypeCode::PARTIAL_RETEST_LEFT_VTS, //'PL'
                MotTestTypeCode::PARTIAL_RETEST_REPAIRED_AT_VTS, //'PV'
                MotTestTypeCode::RE_TEST, //'RT'
                MotTestTypeCode::INVERTED_APPEAL, //'EI'
                MotTestTypeCode::STATUTORY_APPEAL, //'ES'
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
        $qb = $this
            ->createQueryBuilder('mt')
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
        $motTest = $this->find($id);
        if (!$motTest) {
            throw new NotFoundException('Mot Test', $id);
        }

        return $motTest;
    }

    /**
     * Get the odometer history for a given vehicle id.
     *
     * @param int                      $vehicleId
     * @param DateTime                 $dateTo
     * @param array [MotTestTypeCode]  $optionalMotTestTypeCodes (default = null)
     * @param int                      $limit (default = 4)
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

        $qb->select('t.issuedDate, o.value, o.unit, ts.name as status, o.resultType as resultType, DATE(t.issuedDate) as dtIssuedDate')
            ->from($this->getEntityName(), 't')
            ->innerJoin('t.odometerReading', 'o')
            ->innerJoin('t.motTestType', 'tt')
            ->innerJoin('t.status', 'ts')
            ->where('t.vehicle = :vehicleId')
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

        $qb->select('o.value, o.unit, o.resultType')
            ->from($this->getEntityName(), 't')
            ->innerJoin('t.odometerReading', 'o')
            ->where('t.id = ?0')
            ->setMaxResults(1);

        $qb->setParameter(0, $id);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * This function allow us to paginate all the database to avoid memory limit.
     *
     * @param int    $start
     * @param int    $offset
     * @param string $orderBy
     * @param int    $hydrateMode
     *
     * @return array
     */
    public function getAllDataForEsIngestion(
        $start,
        $offset,
        $orderBy = 'test.id',
        $hydrateMode = Query::HYDRATE_OBJECT
    ) {
        $qb = $this
            ->createQueryBuilder('test')
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
     * Generic function to get MOT test count through organisation_site_map.
     *
     * @param $whereString
     * @param $whereParam
     * @param $whereValue
     * @param bool $getTestLogsForAe
     *
     * @return mixed
     */
    private function getCountOfMotTests($whereString, $whereParam, $whereValue, $getTestLogsForAe = false)
    {
        $sql = $this->getMotTestCountQuery($whereString, $getTestLogsForAe);

        if (!$getTestLogsForAe) {
            $sql = $this->addMotTestSpecificConstraints($sql);
        }

        //fiter out organisation_site_statuses
        $whereParams = [];
        foreach (static::$testLogOrganisationSiteStatuses as $key => $val) {
            $whereParams[] = ':' . $key;
        }
        $sql .= ' AND oss.code NOT IN (' . implode(', ', $whereParams) . ')';

        //  ----  prepare statement and bind params   ----
        $em = $this->getEntityManager();
        $sql = $em->getConnection()->prepare($sql);

        $sql->bindValue($whereParam, $whereValue);

        if (!$getTestLogsForAe) {
            $this->bindMotTestSpecificConstraints($sql);
        }

        //  --  bind statuses --
        foreach (static::$testLogOrganisationSiteStatuses as $key => $val) {
            $sql->bindValue($key, $val);
        }

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
            'osm.organisation_id = :ORGANISATION_ID',
            'ORGANISATION_ID',
            $organisationId,
            true
        );
    }

    /**
     * @param $siteId
     *
     * @return mixed
     */
    public function getCountOfSiteMotTestsSummary($siteId)
    {
        return $this->getCountOfMotTests('osm.site_id = :SITE_ID AND osm.organisation_id = s.organisation_id', 'SITE_ID', $siteId);
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
        return $this->getCountOfMotTests('t.person_id = :PERSON_ID', 'PERSON_ID', $testerId);
    }

    /**
     * Prepare statement to get mot tests log data.
     *
     * @param MotTestSearchParam $searchParam
     *
     * @return NativeQueryBuilder
     */
    public function prepareMotTestLogQuery(MotTestSearchParam $searchParam)
    {
        //  --  prepare sub query   --
        $qb = new NativeQueryBuilder();
        $qb
            ->select('*', 'all')
            ->select(
                'COALESCE(mt.completed_date, mt.started_date) AS testDate,
                TIMESTAMPDIFF(SECOND, mt.started_date, COALESCE(mt.completed_date, mt.started_date)) as testDuration
                ',
                'additionalFields'
            )
            ->from('mot_test', 'mt')
            ->join('site', 's', 's.id = mt.site_id')
            ->join('make', 'vma', 'vma.id = mt.make_id', NativeQueryBuilder::JOIN_TYPE_LEFT)
            ->join('model', 'vmo', 'vmo.id = mt.model_id', NativeQueryBuilder::JOIN_TYPE_LEFT)
            ->join('mot_test_type', 'tt', 'tt.id = mt.mot_test_type_id')
            ->join('mot_test_status', 'ts', 'ts.id = mt.status_id')
            ->join('person', 'p', 'p.id = mt.person_id')
            ->join('organisation_site_map', 'osm', 'osm.site_id = mt.site_id')
            ->join('organisation_site_status', 'oss', 'oss.id = osm.status_id');

        if ($searchParam->getSiteNumber()) {
            $qb->andwhere('s.site_number = :SITE_NR')
                ->setParameter('SITE_NR', $searchParam->getSiteNumber());
        }

        if ($searchParam->getTesterId()) {
            $qb->andwhere('mt.person_id = :TESTER_ID')
                ->setParameter('TESTER_ID', $searchParam->getTesterId());
        }

        if ($searchParam->getRegistration()) {
            $qb->andwhere('mt.registration = :VRM')
                ->setParameter('VRM', $searchParam->getRegistration());
        }

        if ($searchParam->getVin()) {
            $qb->andwhere('mt.vin = :VIN')
                ->setParameter('VIN', $searchParam->getVin());
        }

        if ($searchParam->getVehicleId()) {
            $qb->andwhere('mt.vehicle_id = :VEHICLE_ID')
                ->setParameter('VEHICLE_ID', $searchParam->getVehicleId());
        }

        if ($searchParam->getOrganisationId()) {
            $qb->andWhere('osm.organisation_id = :OSM_ORGANISATION_ID')
                ->setParameter('OSM_ORGANISATION_ID', $searchParam->getOrganisationId());
        }

        if ($searchParam->getSiteId()) {
            $qb->andWhere('osm.site_id = :OSM_SITE_ID')
                ->setParameter('OSM_SITE_ID', $searchParam->getSiteId());
        }

        $qb->andWhere('oss.code NOT IN (:ORGANISATION_SITE_STATUS_ID)')
                ->setParameter('ORGANISATION_SITE_STATUS_ID', implode(',', [
                    OrganisationSiteStatusCode::APPLIED,
                    OrganisationSiteStatusCode::UNKNOWN,
            ]));

        $statuses = $searchParam->getStatus();
        if (!empty($statuses)) {
            $query = [];
            foreach ($statuses as $key => $item) {
                $query[] = ':STATUS' . $key;

                $qb->setParameter('STATUS' . $key, $item);
            }

            $qb->andwhere('ts.name IN (' . implode(',', $query) . ')');
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

        if ($searchParam->getDateFrom() || $searchParam->getDateTo()) {
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
                ->andWhere('mt.completed_date >= osm.start_date')
                ->andWhere('(mt.completed_date <= osm.end_date OR osm.end_date IS NULL)')
                ->setParameter('DATE_FROM', $searchParam->getDateFrom() ?: new \DateTime('1900-01-01'))
                ->setParameter('DATE_TO', $searchParam->getDateTo() ?: new \DateTime());
        }

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

        //  logical block: define pagination statement
        if ($searchParam->getStart() > 0) {
            $qb->setOffset($searchParam->getStart());
        }

        if ($searchParam->getRowCount() > 0) {
            $qb->setLimit($searchParam->getRowCount());
        }

        return $qb;
    }

    public function getMotTestLogsResult(MotTestSearchParam $searchParam)
    {
        $qb = $this->prepareMotTestLogQuery($searchParam);
        $qb
            ->resetPart('select', 'all')
            ->select('mt.number, mt.client_ip, ts.name AS status')
            ->select('mt.registration, mt.vin')
            ->select('vma.code as make_code, COALESCE(vma.name, mt.make_name) AS makeName')
            ->select('vma.code as make_code, COALESCE(vmo.name, mt.model_name) AS modelName')
            ->select('vc.name AS vehicle_class')
            ->select('s.site_number AS siteNumber, p.username as userName, tt.description as testTypeName')
            ->select('mt.emergency_log_id AS emLogId')
            ->join('vehicle_class', 'vc', 'vc.id = mt.vehicle_class_id');

        if ($searchParam->getFormat() === SearchParamConst::FORMAT_DATA_CSV) {
            $qb
                ->resetPart('orderBy')
                ->orderBy('siteNumber ASC')
                ->orderBy('testDate ASC')
                ->select(
                    'CASE WHEN eml.id IS NOT NULL THEN emp.username ELSE NULL END AS emRecTester,
                    CASE WHEN eml.id IS NOT NULL THEN mt.created_on ELSE NULL END AS emRecDateTime,
                    COALESCE(emcm.comment, emrl.name) AS emReason,
                    eml.number AS emCode',
                    'emergency'
                )
                ->join('emergency_log', 'eml', 'eml.id = mt.emergency_log_id', NativeQueryBuilder::JOIN_TYPE_LEFT)
                ->join(
                    'emergency_reason_lookup',
                    'emrl',
                    'emrl.id = mt.emergency_reason_lookup_id',
                    NativeQueryBuilder::JOIN_TYPE_LEFT
                )
                ->join(
                    'comment', 'emcm', 'emcm.id = mt.emergency_reason_comment_id', NativeQueryBuilder::JOIN_TYPE_LEFT
                )
                ->join('person', 'emp', 'emp.id = mt.created_by');
        }

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
        $qb = $this->prepareMotTestLogQuery($searchParam)
            ->resetPart('select')
            ->select('count(mt.id) AS count')
            ->resetPart('orderBy')
            ->resetPart('offset')
            ->resetPart('limit');

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
    public function prepareMotSearch(MotTestSearchParam $searchParam, array $optionalMotTestTypes)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('test')
            ->from(MotTest::class, 'test')
            ->leftJoin(Site::class, 'site', 'WITH', 'site.id = test.vehicleTestingStation')
            ->innerJoin(Vehicle::class, 'vehicle', 'WITH', 'vehicle.id = test.vehicle')
            ->leftJoin(Make::class, 'make', 'WITH', 'make.id = test.make')
            ->leftJoin(Model::class, 'model', 'WITH', 'model.id = test.model')
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
            $qb->andwhere('vehicle.registration = :VRM')
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

        return (int) $dql->getQuery()->getSingleScalarResult();
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

        $count = (int) $qb->getQuery()->getSingleScalarResult();

        return $count;
    }

    /**
     * @return int
     */
    public function getLastMotTestId()
    {
        $result = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('MAX(mt.id)')
            ->from($this->getEntityName(), 'mt')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleScalarResult();

        if (!$result) {
            return 0;
        }

        return (int) $result;
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
     * @param bool   $isQueryForAe
     *
     * @return string
     */
    private function getMotTestCountQuery($whereString, $isQueryForAe)
    {
        $joins = '';
        if (!$isQueryForAe) {
            $joins = '
                INNER JOIN mot_test_type AS tt ON
                    tt.id = t.mot_test_type_id

                INNER JOIN mot_test_status AS ts ON
                    ts.id = t.status_id
            ';
        }

        return "
        SELECT COUNT(t.id) AS `year`,
            SUM(CASE 
               WHEN coalesce(t.completed_date, t.started_date) BETWEEN LAST_DAY(CURRENT_DATE() - INTERVAL 2 MONTH) + INTERVAL 1 DAY
                   AND LAST_DAY(CURRENT_DATE()) - INTERVAL 1 MONTH + INTERVAL 1 DAY
                   AND (osm.end_date IS NULL OR coalesce(t.completed_date, t.started_date) <= osm.end_date)
                   AND coalesce(t.completed_date, t.started_date) >= osm.start_date
               THEN
                   1
               ELSE
                   0
               END) AS `month`,
            SUM(CASE 
                WHEN coalesce(t.completed_date, t.started_date) BETWEEN CURRENT_DATE() - INTERVAL WEEKDAY(CURRENT_DATE()) + 7 DAY
                     AND CURRENT_DATE() - INTERVAL WEEKDAY(CURRENT_DATE()) DAY
                     AND (osm.end_date IS NULL OR coalesce(t.completed_date, t.started_date) <= osm.end_date)
                     AND coalesce(t.completed_date, t.started_date) >= osm.start_date
                THEN
                    1
                ELSE
                    0
                END ) AS `week`,
            SUM(
            CASE
            WHEN coalesce(t.completed_date, t.started_date) >= CURRENT_DATE()
                 AND (osm.end_date IS NULL OR coalesce(t.completed_date, t.started_date) <= osm.end_date)
                 AND coalesce(t.completed_date, t.started_date) >= osm.start_date
            THEN 
                1
            ELSE
                0
            END ) AS `today`
         FROM site AS s
            INNER JOIN organisation_site_map AS osm     ON osm.site_id = s.id
            INNER JOIN organisation_site_status AS oss  ON oss.id = osm.status_id
            INNER JOIN mot_test AS t                    ON t.site_id = s.id
            {$joins}
            WHERE 
                {$whereString}
                AND 
                  (
                    (t.completed_date IS NULL AND t.started_date >= (CURRENT_DATE() - INTERVAL 1 YEAR + INTERVAL 1 DAY)) 
                    OR (t.completed_date IS NOT NULL AND t.completed_date >= (CURRENT_DATE() - INTERVAL 1 YEAR + INTERVAL 1 DAY))
                  )
                AND (coalesce(t.completed_date, t.started_date) <= osm.end_date OR osm.end_date IS NULL)
                AND coalesce(t.completed_date, t.started_date) >= osm.start_date
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

        //  --  add test status where clause --
        $whereParams = [];
        foreach (static::$testLogTestStatuses as $key => $val) {
            $whereParams[] = ':' . $key;
        }
        $sql .= ' AND ts.name IN (' . implode(', ', $whereParams) . ')';

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

        //  --  bind statuses --
        foreach (static::$testLogTestStatuses as $key => $val) {
            $sql->bindValue($key, $val);
        }

        return $sql;
    }
}

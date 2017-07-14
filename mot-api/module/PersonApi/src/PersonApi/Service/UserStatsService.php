<?php

namespace PersonApi\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use DvsaCommon\ApiClient\Person\PerformanceDashboardStats\Dto\DayPerformanceDashboardStatsDto;
use DvsaCommon\ApiClient\Person\PerformanceDashboardStats\Dto\MonthPerformanceDashboardStatsDto;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Date\TimeSpan;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommonApi\Service\AbstractService;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\MotTestRepository;

/**
 * Service to gather a tester's statistics.
 */
class UserStatsService extends AbstractService
{
    /**
     * @var MotTestRepository
     */
    private $motTestRepository;

    /**
     * UserStatsService constructor.
     *
     * @param EntityManager     $entityManager
     * @param MotTestRepository $repository
     */
    public function __construct(EntityManager $entityManager, MotTestRepository $repository)
    {
        $this->motTestRepository = $repository;
        parent::__construct($entityManager);
    }

    /**
     * @param $personId
     *
     * @return DayPerformanceDashboardStatsDto
     */
    public function getUserDayStatsByPersonId($personId)
    {
        //TODO: OPENAM - confirm the current user is allowed to access these stats
        $person = $this->findOrThrowException(Person::class, $personId, Person::ENTITY_NAME);

        $optionalMotTestTypes = [MotTestTypeCode::MYSTERY_SHOPPER];

        $motTests = $this->getTestsCompletedByTesterFromCompletedDate($person, DateUtils::today(), $optionalMotTestTypes);

        $dayStats = $this->calculateDayStats($motTests);

        return $dayStats;
    }

    /**
     * @param $personId
     *
     * @return MonthPerformanceDashboardStatsDto
     */
    public function getUserCurrentMonthStatsByPersonId($personId)
    {
        //TODO: OPENAM - confirm the current user is allowed to access these stats
        $person = $this->findOrThrowException(Person::class, $personId, Person::ENTITY_NAME);

        $optionalMotTestTypes = [MotTestTypeCode::MYSTERY_SHOPPER];

        $motTests = $this->getTestsCompletedByTesterFromCompletedDate($person, DateUtils::firstOfThisMonth(), $optionalMotTestTypes);

        $monthStats = $this->calculateMonthStats($motTests);

        return $monthStats;
    }

    /**
     * @param       $person
     * @param       $startDate
     * @param array $optionalMotTestTypes
     *
     * @return MotTest[]
     */
    private function getTestsCompletedByTesterFromCompletedDate($person, $startDate, array $optionalMotTestTypes = [])
    {
        //!!! This report query should be rewritten to do counts in database, instead of fetching objects
        //DVSA predicts that in future they will need stats for longer periods than month, then this query must be rewritten
        /** @var \Doctrine\ORM\QueryBuilder $queryBuilder */
        $queryBuilder = $this->motTestRepository->createQueryBuilder('t');
        $queryBuilder->join(
            \DvsaEntities\Entity\MotTestType::class,
            'tt',
            Query\Expr\Join::INNER_JOIN,
            't.motTestType = tt.id'
        );
        $queryBuilder->join(
            \DvsaEntities\Entity\MotTestStatus::class,
            's',
            Query\Expr\Join::INNER_JOIN,
            't.status = s.id'
        );
        $queryBuilder->where('t.tester = :person');
        $queryBuilder->andWhere("t.startedDate  >= DATE_SUB(:startDate, 10, 'day')"); /* VM-11281 */
        $queryBuilder->andWhere('t.completedDate >= :completeDate');
        $queryBuilder->andWhere('s.name IN (:status)');
        $queryBuilder->setParameter('status', [
            MotTestStatusName::PASSED,
            MotTestStatusName::FAILED,
        ]);
        $queryBuilder->setParameter('person', $person);
        $queryBuilder->setParameter('startDate', $startDate);                        /* VM-11281 */
        $queryBuilder->setParameter('completeDate', $startDate);

        $queryBuilder->andWhere('tt.code IN (:SLOT_TEST_TYPES)');

        $slotTestTypes = [MotTestTypeCode::NORMAL_TEST];
        if (!empty($optionalMotTestTypes)) {
            $slotTestTypes = array_merge($slotTestTypes, $optionalMotTestTypes);
        }

        $queryBuilder->setParameter('SLOT_TEST_TYPES', $slotTestTypes);

        $motTests = $queryBuilder->getQuery()->getResult();

        return $motTests;
    }

    /**
     * @param MotTest[] $motTests
     *
     * @return DayPerformanceDashboardStatsDto
     */
    private function calculateDayStats($motTests)
    {
        $passCount = 0;
        $failCount = 0;

        foreach ($motTests as $motTest) {
            if ($motTest->getPrsMotTest()) {
                //PRS tests come as a pair of passed and failed test, but we don't count the passed one
                if ($motTest->getStatus() == MotTestStatusName::FAILED) {
                    ++$failCount;
                }
            } else {
                if ($motTest->getStatus() == MotTestStatusName::PASSED) {
                    ++$passCount;
                } elseif ($motTest->getStatus() == MotTestStatusName::FAILED) {
                    ++$failCount;
                }
            }
        }

        //PRS comes as pair of tests so counting array is not enough
        $total = $passCount + $failCount;

        $result = (new DayPerformanceDashboardStatsDto())
            ->setTotal($total)
            ->setNumberOfPasses($passCount)
            ->setNumberOfFails($failCount);

        return $result;
    }

    /**
     * @param MotTest[] $motTests
     *
     * @return MonthPerformanceDashboardStatsDto
     */
    private function calculateMonthStats($motTests)
    {
        $passCount = 0;
        $sumOfTestTimes = 0;
        $failCount = 0;
        $failRate = 0;
        $averageTestTime = new TimeSpan(0,0,0,0);

        foreach ($motTests as $motTest) {
            $testTime = DateUtils::getTimeDifferenceInSeconds($motTest->getCompletedDate(), $motTest->getStartedDate());

            if ($motTest->getPrsMotTest()) {
                //PRS tests come as a pair of passed and failed test, but we don't count the passed one
                if ($motTest->getStatus() == MotTestStatusName::FAILED) {
                    ++$failCount;
                    $sumOfTestTimes += $testTime;
                }
            } else {
                if ($motTest->getStatus() == MotTestStatusName::PASSED) {
                    ++$passCount;
                } elseif ($motTest->getStatus() == MotTestStatusName::FAILED) {
                    ++$failCount;
                }
                $sumOfTestTimes += $testTime;
            }
        }

        //PRS comes as pair of tests so counting array is not enough
        $total = $passCount + $failCount;

        if ($total) {
            $failRate = $failCount * 100 / $total;
        }

        if ($total) {
            $averageTestTime = new TimeSpan(0,0,0, (int)($sumOfTestTimes / $total));
        }

        $monthStats = (new MonthPerformanceDashboardStatsDto())
            ->setPassedTestsCount($passCount)
            ->setFailedTestsCount($failCount)
            ->setAverageTime($averageTestTime)
            ->setFailRate($failRate)
            ->setTotalTestsCount($total);

        return $monthStats;
    }
}

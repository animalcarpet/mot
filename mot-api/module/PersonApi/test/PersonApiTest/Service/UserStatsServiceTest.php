<?php

namespace PersonApiTest\Service;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestStatus;
use DvsaEntities\Entity\MotTestType;
use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\MotTestRepository;
use UserApi\Dashboard\Dto\DayStats;
use UserApi\Dashboard\Dto\MonthStats;
use PersonApi\Service\UserStatsService;

/**
 * Tests for UserStatsService.
 */
class UserStatsServiceTest extends AbstractServiceTestCase
{
    /**
     * @var UserStatsService
     */
    public $statsService;

    /** @var MotTestRepository | \PHPUnit_Framework_MockObject_MockObject $mockRepository */
    private $mockRepository;

    /** @var EntityManager | \PHPUnit_Framework_MockObject_MockObject $mockEntityManager */
    private $mockEntityManager;

    private $personId = 9;

    public function testConstructor()
    {
        $motTests = $this->getArrayOfMotTests(MotTestTypeCode::NORMAL_TEST);
        $this->setUpMocks($motTests);
        $this->statsService = new UserStatsService(
            $this->mockEntityManager,
            $this->mockRepository
        );

        $this->assertEquals(get_class($this->statsService), UserStatsService::class);
    }

    public function testGetUserDayStatsByPersonId()
    {
        //given
        $motTests = $this->getArrayOfMotTests(MotTestTypeCode::NORMAL_TEST);
        $this->setUpMocks($motTests);
        $this->statsService = new UserStatsService(
            $this->mockEntityManager,
            $this->mockRepository
        );

        //when
        $result = $this->statsService->getUserDayStatsByPersonId($this->personId);

        //then
        $this->assertEquals(get_class($result), DayStats::class);
        $this->assertEquals(
            $result->toArray(),
            [
                'total' => 3,
                'numberOfPasses' => 1,
                'numberOfFails' => 2,
            ]
        );
    }

    public function testGetUserCurrentMonthStatsByPersonId()
    {
        //given
        $motTests = $this->getArrayOfMotTests(MotTestTypeCode::NORMAL_TEST);
        $this->setUpMocks($motTests);
        $this->statsService = new UserStatsService(
            $this->mockEntityManager,
            $this->mockRepository
        );

        //when
        $result = $this->statsService->getUserCurrentMonthStatsByPersonId($this->personId);

        //then
        $this->assertEquals(get_class($result), MonthStats::class);
        $this->assertEquals(
            $result->toArray(),
            [
                'averageTime' => 4800,
                'failRate' => (float) 100 * 2 / 3,
            ]
        );
    }

    /**
     * @param string $motTestTypeCode
     *
     * @return array
     */
    private function getArrayOfMotTests($motTestTypeCode)
    {
        $mtt = new MotTestType();
        $mtt->setCode($motTestTypeCode);
        $motTestPassed = new MotTest();
        $motTestPassed->setStatus($this->createMotTestStatus(MotTestStatusName::PASSED))
            ->setMotTestType($mtt)
            ->setStartedDate(new \DateTime('2014-07-01 10:00:00'))
            ->setCompletedDate(new \DateTime('2014-07-01 11:00:00'));

        $motTestFailed = new MotTest();
        $motTestFailed->setStatus($this->createMotTestStatus(MotTestStatusName::FAILED))
            ->setMotTestType($mtt)
            ->setStartedDate(new \DateTime('2014-07-01 11:00:00'))
            ->setCompletedDate(new \DateTime('2014-07-01 12:00:00'));

        $motTestPrs1 = new MotTest();
        $motTestPrs1->setStatus($this->createMotTestStatus(MotTestStatusName::PASSED))
            ->setMotTestType($mtt)
            ->setStartedDate(new \DateTime('2014-07-01 14:00:00'))
            ->setCompletedDate(new \DateTime('2014-07-01 16:00:00'));

        $motTestPrs2 = new MotTest();
        $motTestPrs2->setStatus($this->createMotTestStatus(MotTestStatusName::FAILED))
            ->setMotTestType($mtt)
            ->setStartedDate(new \DateTime('2014-07-01 14:00:00'))
            ->setCompletedDate(new \DateTime('2014-07-01 16:00:00'));

        $motTestPrs1->setPrsMotTest($motTestPrs2);
        $motTestPrs2->setPrsMotTest($motTestPrs1);

        return [$motTestPassed, $motTestFailed, $motTestPrs1, $motTestPrs2];
    }

    private function setUpMocks($motTests)
    {
        $person = new Person();

        $mockQuery = $this->getMockBuilder(AbstractQuery::class)
            ->setMethods(['setParameter', 'getResult'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->setupMockForCalls($mockQuery, 'getResult', $motTests);

        $mockQueryBuilder = $this->getMockBuilder(QueryBuilder::class, [], [], '', false)->disableOriginalConstructor()->getMock();
        $this->setupMockForCalls($mockQueryBuilder, 'getQuery', $mockQuery);

        /* @var MotTestRepository $mockRepository */
        $this->mockRepository = $this->getMockRepository(MotTestRepository::class);
        $this->setupMockForCalls($this->mockRepository, 'matching', $motTests);
        $this->setupMockForCalls($this->mockRepository, 'createQueryBuilder', $mockQueryBuilder, 't');

        /* @var EntityManager $mockEntityManager */
        $this->mockEntityManager = $this->getMockEntityManager();
        $this->setupMockForCalls($this->mockEntityManager, 'find', $person, Person::class);
        $this->setupMockForCalls($this->mockEntityManager, 'getRepository', $this->mockRepository, MotTest::class);
    }

    /**
     * @param $name
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|MotTestStatus
     *
     * @throws \Exception
     */
    private function createMotTestStatus($name)
    {
        $status = XMock::of(MotTestStatus::class);
        $status
            ->expects($this->any())
            ->method('getName')
            ->willReturn($name);

        return $status;
    }
}

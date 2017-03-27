<?php

namespace DvsaEntitiesTest\Repository;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Constants\SearchParamConst;
use DvsaCommon\Dto\Common\OdometerReadingDto;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommonTest\Bootstrap;
use DvsaEntities\DqlBuilder\SearchParam\MotTestSearchParam;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Repository\MotTestHistoryRepository;
use DvsaMotApi\Helper\MysteryShopperHelper;

/**
 * @group integration
 */
class MotTestRepositoryTest extends \PHPUnit_Framework_TestCase
{
    const VEHICLE_ID = 101663605;
    const VEHICLE_ID_WITH_NO_TEST_IN_PROGRESS = 101663407;
    const VEHICLE_ID_WITH_PASSED_TEST = 101663605;
    const VEHICLE_ID_WITH_TEST_IN_PROGRESS = 101663405;
    const PERSON_WITH_DEMO_TEST_IN_PROGRESS = 3467;
    const PERSON_WITH_TEST_IN_PROGRESS = 3029;
    const VTS_ID_WITH_TESTS_IN_PROGRESS = 3021;
    const SITE_NUMBER_WITH_TESTS = 'S100002';
    const SITE_ID = 3021;
    const TEST_NUMBER_WITH_RETEST = '811553090017';
    const TEST_NUMBER_WITH_PREVIOUS_READING = '811553090017';
    const TEST_NUMBER = '656402615654';
    const TEST_ID = 7;
    const VEHICLE_REGISTRATION = 'IFDWOKG';
    const ORGANISATION_ID = 2053;
    const TESTER_ID = 3069;

    protected function setUp()
    {
        // Tests to cover the MotTestRepository were added to safely implement BL-3199.
        // Since we don't have a similar integration test in place it would be too time
        // consuming to set it up now. Given features we need to update are being migrated
        // to new endpoints, it's not worth to make this effort now.
        // Once BL-3199 is implement we'll either remove these tests, or decide
        // to implement database fixtures to go with it.
        $this->markTestIncomplete('Database fixtures are not set up.');
    }

    public function testItFindsTheLastNormalNotAbortedTest()
    {
        $test = $this->getMotTestRepository()->findLastNormalNotAbortedTest(self::VEHICLE_ID);

        $this->assertInstanceOf(MotTest::class, $test);
    }

    public function testItCountsNotCancelledTests()
    {
        $count = $this->getMotTestRepository()->countNotCancelledTests(self::VEHICLE_ID, new \DateTime('-10 years'));

        $this->assertGreaterThan(0, $count);
    }

    public function testItFindsTheLastCertificateExpiryDate()
    {
        $expiryDate = $this->getMotTestRepository()->findLastCertificateExpiryDate(self::VEHICLE_ID);

        $this->assertInstanceOf(\DateTime::class, $expiryDate);
    }

    public function testItFindsAnInProgressTestNumberForAPerson()
    {
        $testNumber = $this->getMotTestRepository()->findInProgressTestNumberForPerson(self::PERSON_WITH_TEST_IN_PROGRESS);

        $this->assertInternalType('string', $testNumber);
        $this->assertGreaterThan(0, $testNumber);
    }

    public function testItFindsAnInProgressTestForAPerson()
    {
        $test = $this->getMotTestRepository()->findInProgressTestForPerson(self::PERSON_WITH_TEST_IN_PROGRESS);

        $this->assertInstanceOf(MotTest::class, $test);
    }

    public function testItFindsAnInProgressDemoTestNumberForAPerson()
    {
        $testNumber = $this->getMotTestRepository()->findInProgressDemoTestNumberForPerson(self::PERSON_WITH_DEMO_TEST_IN_PROGRESS);

        $this->assertInternalType('string', $testNumber);
        $this->assertGreaterThan(0, $testNumber);
    }

    public function testItFindsAnInProgressDemoTestForAPerson()
    {
        $test = $this->getMotTestRepository()->findInProgressDemoTestForPerson(self::PERSON_WITH_DEMO_TEST_IN_PROGRESS);

        $this->assertInstanceOf(MotTest::class, $test);
    }

    public function testItChecksIfAVehicleHasATestInProgress()
    {
        $this->assertTrue($this->getMotTestRepository()->isTestInProgressForVehicle(self::VEHICLE_ID_WITH_TEST_IN_PROGRESS));
        $this->assertFalse($this->getMotTestRepository()->isTestInProgressForVehicle(self::VEHICLE_ID_WITH_NO_TEST_IN_PROGRESS));
    }

    public function testItFindsAnInProgressTestForAVehicle()
    {
        $test = $this->getMotTestRepository()->findInProgressTestForVehicle(self::VEHICLE_ID_WITH_TEST_IN_PROGRESS);

        $this->assertInstanceOf(MotTest::class, $test);
    }

    public function testItFindsInProgressTestsForAVts()
    {
        $tests = $this->getMotTestRepository()->findInProgressTestsForVts(self::VTS_ID_WITH_TESTS_IN_PROGRESS);

        $this->assertInternalType('array', $tests);
        $this->assertNotEmpty($tests);
        $this->assertContainsOnlyInstancesOf(MotTest::class, $tests);
    }

    public function testItCountsInProgressTestsForAVts()
    {
        $count = $this->getMotTestRepository()->countInProgressTestsForVts(self::VTS_ID_WITH_TESTS_IN_PROGRESS);

        $this->assertGreaterThan(0, $count);
    }

    public function testItFindsRetestForMotTest()
    {
        $test = $this->getMotTestRepository()->findRetestForMotTest(self::TEST_NUMBER_WITH_RETEST);

        $this->assertInstanceOf(MotTest::class, $test);
    }

    public function testItGetsTheLatestMotTestsBySiteNumber()
    {
        $tests = $this->getMotTestRepository()->getLatestMotTestsBySiteNumber(self::SITE_NUMBER_WITH_TESTS, []);

        $this->assertInternalType('array', $tests);
        $this->assertNotEmpty($tests);
        $this->assertContainsOnlyInstancesOf(MotTest::class, $tests);
    }

    public function testItGetsATestByNumber()
    {
        $test = $this->getMotTestRepository()->getMotTestByNumber(self::TEST_NUMBER);

        $this->assertInstanceOf(MotTest::class, $test);
    }

    public function testItFindsATestByVehicleRegistrationAndTestNumber()
    {
        $test = $this->getMotTestRepository()->findTestByVehicleRegistrationAndTestNumber(self::VEHICLE_REGISTRATION, self::TEST_NUMBER);

        $this->assertInstanceOf(MotTest::class, $test);
    }

    public function testItFindsTestsForVehicle()
    {
        $mysteryHelper = $this->getMockBuilder(MysteryShopperHelper::class)->disableOriginalConstructor()->getMock();

        $tests = $this->getMotTestRepository()->findTestsForVehicle(self::VEHICLE_ID, null, $mysteryHelper);

        $this->assertInternalType('array', $tests);
        $this->assertNotEmpty($tests);
        $this->assertContainsOnlyInstancesOf(MotTest::class, $tests);
    }

    public function testItFindsTestsExcludingNonAuthoritativeTestsForVehicle()
    {
        $tests = $this->getMotTestRepository()->findTestsExcludingNonAuthoritativeTestsForVehicle(self::VEHICLE_ID, null);

        $this->assertInternalType('array', $tests);
        $this->assertNotEmpty($tests);
        $this->assertContainsOnlyInstancesOf(MotTest::class, $tests);
    }

    public function testItGetsLatestMotTestByVehicleIdAndResult()
    {
        $test = $this->getMotTestRepository()->getLatestMotTestByVehicleIdAndResult(self::VEHICLE_ID_WITH_PASSED_TEST, MotTestStatusName::PASSED);

        $this->assertInstanceOf(MotTest::class, $test);
    }

    public function testItFindsLatestMotTestByVrmAndResult()
    {
        $test = $this->getMotTestRepository()->findLatestMotTestByVrmAndResult(self::VEHICLE_REGISTRATION, MotTestStatusName::PASSED, new \DateTime('tomorrow'));

        $this->assertInstanceOf(MotTest::class, $test);
    }

    public function testItGetsTheLatestTestByVehicleId()
    {
        $testId = $this->getMotTestRepository()->getLatestMotTestIdByVehicleId(self::VEHICLE_ID_WITH_PASSED_TEST, MotTestStatusName::PASSED);

        $this->assertInternalType('string', $testId);
        $this->assertGreaterThan(0, $testId);
    }

    public function testItGetsLatestMotTestsByVehicleId()
    {
        $tests = $this->getMotTestRepository()->getLatestMotTestsByVehicleId(self::VEHICLE_ID_WITH_PASSED_TEST);

        $this->assertInternalType('array', $tests);
        $this->assertNotEmpty($tests);
        $this->assertContainsOnlyInstancesOf(MotTest::class, $tests);
    }

    public function testItGetsAnMotTestById()
    {
        $test = $this->getMotTestRepository()->getMotTest(self::TEST_ID);

        $this->assertInstanceOf(MotTest::class, $test);
    }

    public function testItGetsOdometerHistoryForVehicleId()
    {
        $odometerHistory = $this->getMotTestRepository()->getOdometerHistoryForVehicleId(self::VEHICLE_ID);

        $this->assertInternalType('array', $odometerHistory);
        $this->assertNotEmpty($odometerHistory);
    }

    public function testItGetsOdometerReadingForId()
    {
        $reading = $this->getMotTestRepository()->getOdometerReadingForId(self::TEST_ID);

        $this->assertInternalType('array', $reading);
        $this->assertArrayHasKey('value', $reading);
        $this->assertArrayHasKey('unit', $reading);
        $this->assertArrayHasKey('resultType', $reading);
    }

    public function testItGetsCountOfMotTestsSummary()
    {
        $summary = $this->getMotTestRepository()->getCountOfMotTestsSummary(self::ORGANISATION_ID);

        $this->assertInternalType('array', $summary);
        $this->assertArrayHasKey('year', $summary);
        $this->assertArrayHasKey('month', $summary);
        $this->assertArrayHasKey('week', $summary);
        $this->assertArrayHasKey('today', $summary);
    }

    public function testItGetsCountOfSiteMotTestsSummary()
    {
        $summary = $this->getMotTestRepository()->getCountOfSiteMotTestsSummary(self::SITE_ID);

        $this->assertInternalType('array', $summary);
        $this->assertArrayHasKey('year', $summary);
        $this->assertArrayHasKey('month', $summary);
        $this->assertArrayHasKey('week', $summary);
        $this->assertArrayHasKey('today', $summary);
    }

    public function testItGetsCountOfTesterMotTestsSummary()
    {
        $summary = $this->getMotTestRepository()->getCountOfTesterMotTestsSummary(6243);

        $this->assertInternalType('array', $summary);
        $this->assertArrayHasKey('year', $summary);
        $this->assertArrayHasKey('month', $summary);
        $this->assertArrayHasKey('week', $summary);
        $this->assertArrayHasKey('today', $summary);
    }

    public function testItGetsMotTestSearchResult()
    {
        $searchParam = $this->createSearchParam();
        $searchParam->setVehicleId(self::VEHICLE_ID);
        $tests = $this->getMotTestRepository()->getMotTestSearchResult($searchParam, []);

        $this->assertInternalType('array', $tests);
        $this->assertContainsOnlyInstancesOf(MotTest::class, $tests);
    }

    public function testItGetsMotTestSearchResultCount()
    {
        $searchParam = $this->createSearchParam();
        $searchParam->setVehicleId(self::VEHICLE_ID);
        $count = $this->getMotTestRepository()->getMotTestSearchResultCount($searchParam, []);

        $this->assertGreaterThan(0, $count);
    }

    public function testItChecksIfTesterIsForMot()
    {
        $this->assertTrue($this->getMotTestRepository()->isTesterForMot(self::TESTER_ID, self::TEST_NUMBER));
        $this->assertFalse($this->getMotTestRepository()->isTesterForMot(1234, self::TEST_NUMBER));
    }

    public function testItGetsNormalMotTestCountSinceLastSurvey()
    {
        $count = $this->getMotTestRepository()->getNormalMotTestCountSinceLastSurvey(1);

        $this->assertGreaterThan(0, $count);
    }

    public function testItGetsTheLastMotTestId()
    {
        $id = $this->getMotTestRepository()->getLastMotTestId();

        $this->assertGreaterThan(0, $id);
    }

    public function testItFindsAReadingForTest()
    {
        $reading = $this->getMotTestRepository()->findReadingForTest(self::TEST_NUMBER);

        $this->assertInstanceOf(OdometerReadingDto::class, $reading);
    }

    public function testItFindsAPreviousReading()
    {
        $reading = $this->getMotTestRepository()->findPreviousReading(self::TEST_NUMBER_WITH_PREVIOUS_READING);

        $this->assertInstanceOf(OdometerReadingDto::class, $reading);
    }

    public function testItGetsMotTestLogsResult()
    {
        $searchParam = $this->createSearchParam();
        $searchParam->setSiteId(self::SITE_ID);
        $searchParam->setFormat(SearchParamConst::FORMAT_DATA_CSV);

        $results = $this->getMotTestRepository()->getMotTestLogsResult($searchParam);

        $this->assertInternalType('array', $results);
    }

    public function testItGetsMotTestLogsResultCount()
    {
        $searchParam = $this->createSearchParam();
        $searchParam->setSiteId(self::SITE_ID);

        $count = $this->getMotTestRepository()->getMotTestLogsResultCount($searchParam);

        $this->assertGreaterThan(0, $count);
    }

    /**
     * @return MotTestHistoryRepository
     */
    private function getMotTestRepository()
    {
        return $this->getEntityManager()->getRepository(MotTest::class);
    }

    /**
     * @return MotTestSearchParam
     */
    private function createSearchParam()
    {
        return new MotTestSearchParam($this->getEntityManager());
    }

    /**
     * @return EntityManager
     */
    private function getEntityManager()
    {
        return Bootstrap::getServiceManager()->get('doctrine.entitymanager.orm_default');
    }
}

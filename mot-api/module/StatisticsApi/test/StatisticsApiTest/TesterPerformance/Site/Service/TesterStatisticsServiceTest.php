<?php

namespace Dvsa\Mot\Api\StatisticsApiTest\TesterPerformance\Site\Service;

use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\SiteGroupPerformanceDto;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\QueryResult\TesterPerformanceResult;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\Repository\TesterStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\Service\TesterStatisticsService;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\SitePerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\TesterPerformanceDto;
use DvsaCommon\Auth\Assertion\ViewTesterTestQualityAssertion;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Date\TimeSpan;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommon\Model\TesterAuthorisation;
use DvsaCommonTest\Date\TestDateTimeHolder;
use DvsaCommonTest\TestUtils\Auth\AuthorisationServiceMock;
use DvsaCommonTest\TestUtils\XMock;
use PersonApi\Service\Mapper\TesterGroupAuthorisationMapper;

class TesterStatisticsServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var TesterStatisticsService */
    private $service;

    /** @var TesterStatisticsRepository | \PHPUnit_Framework_MockObject_MockObject */
    private $repository;

    private $siteId = 19;

    private $testerId = 19;

    /** @var  AuthorisationServiceMock */
    private $authorisationService;

    /** @var  ViewTesterTestQualityAssertion */
    private $viewTesterTestQualityAssertion;

    /** @var  TesterGroupAuthorisationMapper */
    private $testerGroupAuthorisationMapper;

    public function setUp()
    {
        /** @var TesterStatisticsRepository | \PHPUnit_Framework_MockObject_MockObject repository */
        $this->repository = XMock::of(TesterStatisticsRepository::class);

        $this->authorisationService = new AuthorisationServiceMock();
        $this->authorisationService->grantedAtSite(PermissionAtSite::VTS_VIEW_TEST_QUALITY, $this->siteId);

        $this->viewTesterTestQualityAssertion = XMock::of(ViewTesterTestQualityAssertion::class);

        $this->testerGroupAuthorisationMapper = XMock::of(TesterGroupAuthorisationMapper::class);
        $this
            ->testerGroupAuthorisationMapper
            ->method("getAuthorisation")
            ->willReturn(new TesterAuthorisation());


        $this->service = new \Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\Service\TesterStatisticsService(
            $this->repository,
            $this->authorisationService,
            $this->viewTesterTestQualityAssertion,
            $this->testerGroupAuthorisationMapper,
            $this->getDateTimeHolder()
        );
    }

    /**
     * @expectedException \DvsaCommon\Exception\UnauthorisedException
     */
    public function testAuthorisationIsRequiredToViewSiteStatistics()
    {
        // GIVEN I do not have any permissions
        $this->authorisationService->clearAll();

        // WHEN I query for statistics
        $this->service->getForSite($this->siteId, $this->getYear(), $this->getPrevMonth());
        // THEN an exception is thrown
    }

    /**
     * @expectedException \DvsaCommon\Exception\UnauthorisedException
     */
    public function testAuthorisationIsRequiredToViewStatisticsForTester()
    {
        $this
            ->viewTesterTestQualityAssertion
            ->method("assertGranted")
            ->willThrowException(new \DvsaCommon\Exception\UnauthorisedException(""));

        $this->service->getForTester($this->testerId, $this->getYear(), $this->getPrevMonth());
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function testGetForSiteThrowsExceptionForInvalidParameters()
    {
        $this->service->getForSite($this->siteId, $this->getYear() + 12, $this->getPrevMonth());
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function testGetForTesterThrowsExceptionForInvalidParameters()
    {
        $this->service->getForTester($this->testerId, $this->getYear() + 2, $this->getPrevMonth());
    }

    /**
     * @param TesterPerformanceResult[] $results
     * @dataProvider dataProviderDbResults
     */
    public function testGetForSiteReturnsDto(array $results)
    {
        $this
            ->repository
            ->method("getForSite")
            ->willReturn($results);

        $dto = $this->service->getForSite($this->siteId, $this->getYear(), $this->getPrevMonth());
        $this->assertInstanceOf(SitePerformanceDto::class, $dto);

        if (empty($results)) {
            $this->assertEmptySiteStatistics($dto->getA());
            $this->assertEmptySiteStatistics($dto->getB());

            $this->assertEmpty($dto->getB()->getStatistics());
        } else {
            /** @var TesterPerformanceResult $testerPerformanceResult */
            $testerPerformanceResult = $results[0];

            $siteGroupPerformanceDto = $dto->getA();
            $this->assertEquals($testerPerformanceResult->getTotalCount(), $siteGroupPerformanceDto->getTotal()->getTotal());
            $this->assertEquals(new TimeSpan(0, 0, 0, 6), $siteGroupPerformanceDto->getTotal()->getAverageTime());
            $this->assertEquals(($testerPerformanceResult->getFailedCount() / $testerPerformanceResult->getTotalCount()) * 100, $siteGroupPerformanceDto->getTotal()->getPercentageFailed());
            $this->assertEquals($testerPerformanceResult->getAverageVehicleAgeInMonths(), $siteGroupPerformanceDto->getTotal()->getAverageVehicleAgeInMonths());
            $this->assertTrue($siteGroupPerformanceDto->getTotal()->getIsAverageVehicleAgeAvailable());

            $statistics = $siteGroupPerformanceDto->getStatistics()[0];

            $this->assertEquals($testerPerformanceResult->getUsername(), $statistics->getUsername());
            $this->assertEquals($testerPerformanceResult->getPersonId(), $statistics->getPersonId());
            $this->assertEquals($testerPerformanceResult->getTotalCount(), $statistics->getTotal());
            $this->assertEquals(($testerPerformanceResult->getFailedCount() / $testerPerformanceResult->getTotalCount()) * 100, $statistics->getPercentageFailed());
            $this->assertEquals($testerPerformanceResult->getAverageVehicleAgeInMonths(), $statistics->getAverageVehicleAgeInMonths());
            $this->assertEquals($testerPerformanceResult->getIsAverageVehicleAgeAvailable(), $statistics->getIsAverageVehicleAgeAvailable());

            $this->assertEmptySiteStatistics($dto->getB());
        }
    }

    private function assertEmptySiteStatistics(SiteGroupPerformanceDto $siteGroupPerformanceDto)
    {
        $this->assertEquals(0, $siteGroupPerformanceDto->getTotal()->getTotal());
        $this->assertEquals(new TimeSpan(0, 0, 0, 0), $siteGroupPerformanceDto->getTotal()->getAverageTime());
        $this->assertEquals(0, $siteGroupPerformanceDto->getTotal()->getPercentageFailed());
        $this->assertEquals(0, $siteGroupPerformanceDto->getTotal()->getAverageVehicleAgeInMonths());
        $this->assertFalse($siteGroupPerformanceDto->getTotal()->getIsAverageVehicleAgeAvailable());

        $this->assertEmpty($siteGroupPerformanceDto->getStatistics());
    }

    /**
     * @param TesterPerformanceResult[] $results
     * @dataProvider dataProviderDbResults
     */
    public function testGetForTesterReturnsDto(array $results)
    {
        $this
            ->repository
            ->method("getForTester")
            ->willReturn($results);

        $dto = $this->service->getForTester($this->siteId, $this->getYear(), $this->getPrevMonth());
        $this->assertInstanceOf(TesterPerformanceDto::class, $dto);

        if (empty($results)) {
            $this->assertNull($dto->getGroupAPerformance());
            $this->assertNull($dto->getGroupBPerformance());
        } else {
            $testerPerformanceResult = array_shift($results);

            $this->assertEquals($testerPerformanceResult->getUsername(), $dto->getGroupAPerformance()->getUsername());
            $this->assertEquals($testerPerformanceResult->getPersonId(), $dto->getGroupAPerformance()->getPersonId());
            $this->assertEquals($testerPerformanceResult->getTotalCount(), $dto->getGroupAPerformance()->getTotal());
            $this->assertEquals(new TimeSpan(0, 0, 0, 6), $dto->getGroupAPerformance()->getAverageTime());
            $this->assertEquals(($testerPerformanceResult->getFailedCount() / $testerPerformanceResult->getTotalCount()) * 100, $dto->getGroupAPerformance()->getPercentageFailed());
            $this->assertEquals($testerPerformanceResult->getAverageVehicleAgeInMonths(), $dto->getGroupAPerformance()->getAverageVehicleAgeInMonths());
            $this->assertEquals($testerPerformanceResult->getIsAverageVehicleAgeAvailable(), $dto->getGroupAPerformance()->getIsAverageVehicleAgeAvailable());

            $this->assertNull($dto->getGroupBPerformance());
        }
    }

    public function dataProviderDbResults()
    {
        $results[] = (new TesterPerformanceResult())
            ->setUsername("tester")
            ->setPersonId(198)
            ->setTotalCount(10)
            ->setVehicleClassGroup(VehicleClassGroupCode::BIKES)
            ->setFailedCount(10)
            ->setAverageVehicleAgeInMonths(15)
            ->setIsAverageVehicleAgeAvailable(true)
            ->setTotalTime("60");

        return [
            [$results],
            [[]]
        ];
    }

    private function getDateTimeHolder()
    {
        return new TestDateTimeHolder(new \DateTime("first day of this month"));
    }

    private function getCurrentDate()
    {
        return $this->getDateTimeHolder()->getCurrentDate();
    }

    private function getPrevMonth()
    {
        return (int)$this->getCurrentDate()->sub(new \DateInterval("P1M"))->format("m");
    }

    private function getYear()
    {
        return (int)$this->getCurrentDate()->sub(new \DateInterval("P1M"))->format("Y");
    }
}

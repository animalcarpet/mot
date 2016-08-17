<?php

namespace Dvsa\Mot\Api\StatisticsApiTest\Site\Service;

use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\Site\QueryResult\TesterPerformanceResult;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\Site\Repository\TesterStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\Site\Service\TesterStatisticsService;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\SitePerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\TesterPerformanceDto;
use DvsaCommon\Auth\Assertion\ViewTesterTestQualityAssertion;
use DvsaCommon\Auth\PermissionAtSite;
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


        $this->service = new TesterStatisticsService(
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

        // WHEN todo Jareq I query for statistics
        $this->service->getForTester($this->testerId, $this->getYear(), $this->getPrevMonth());
        // THEN an exception is thrown
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function testGetForSiteThrowsExceptionForInvalidParameters()
    {
        // WHEN todo Jareq I query for statistics
        $this->service->getForSite($this->siteId, $this->getYear() + 12, $this->getPrevMonth());
        // THEN an exception is thrown
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function testGetForTesterThrowsExceptionForInvalidParameters()
    {
        // todo Jareq WHEN I query for statistics
        $this->service->getForTester($this->testerId, $this->getYear() + 2, $this->getPrevMonth());
        // THEN an exception is thrown
    }

    /**
     * @param array $results
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
    }

    /**
     * @param array $results
     * @dataProvider dataProviderDbResults
     */
    public function testGetForTesterReturnsDto(array $results)
    {
        $this
            ->repository
            ->method("getForTester")
            ->willReturn($results);

        $dto = $this->service->getForTester($this->siteId, $this->getYear(), $this->getPrevMonth());

        // todo Jareq check if the details of the dto are there
        $this->assertInstanceOf(TesterPerformanceDto::class, $dto);
    }

    public function dataProviderDbResults()
    {
        $results[] = (new TesterPerformanceResult())
            ->setUsername("tester")
            ->setTotalCount(0)
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
        return new TestDateTimeHolder(new \DateTime());
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

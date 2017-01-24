<?php
namespace Dvsa\Mot\Api\StatisticsApiTest\TesterPerformance\Tester\Service;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\Mapper\ComponentBreakdownDtoMapper;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Common\QueryResult\ComponentFailRateResult;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Tester\Repository\TesterComponentStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Tester\Repository\TesterSingleGroupStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\QueryResult\TesterAtSitePerformanceResult;
use DvsaCommon\Auth\Assertion\ViewTesterTestQualityAssertion;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommon\Model\TesterAuthorisation;
use DvsaCommonTest\TestUtils\Auth\AuthorisationServiceMock;
use DvsaCommonTest\TestUtils\MethodSpy;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Person;
use PersonApi\Service\Mapper\TesterGroupAuthorisationMapper;
use PersonApi\Service\PersonalDetailsService;

class TesterComponentStatisticsServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var  TesterComponentStatisticsRepository | \PHPUnit_Framework_MockObject_MockObject */
    private $componentStatisticsRepositoryMock;
    /** @var  TesterSingleGroupStatisticsRepository | \PHPUnit_Framework_MockObject_MockObject */
    private $testerStatisticsRepositoryMock;
    /** @var  AuthorisationServiceMock | \PHPUnit_Framework_MockObject_MockObject */
    private $authorisationService;
    /** @var  PersonalDetailsService | \PHPUnit_Framework_MockObject_MockObject */
    private $personalDetailsService;
    /** @var  ViewTesterTestQualityAssertion | \PHPUnit_Framework_MockObject_MockObject */
    private $assertion;
    /** @var  TesterGroupAuthorisationMapper | \PHPUnit_Framework_MockObject_MockObject */
    private $testerGroupAuthorisationMapper;
    /** @var  \Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Tester\Service\TesterComponentStatisticsService */
    private $sut;

    public function setUp()
    {
        $this->componentStatisticsRepositoryMock = XMock::of(TesterComponentStatisticsRepository::class);
        $this->testerStatisticsRepositoryMock = XMock::of(TesterSingleGroupStatisticsRepository::class);
        $this->authorisationService = new AuthorisationServiceMock();
        $this->authorisationService->granted(PermissionInSystem::TESTER_VIEW_TEST_QUALITY);
        $this->personalDetailsService = XMock::of(PersonalDetailsService::class);
        $this->personalDetailsService
            ->expects($this->any())
            ->method('findPerson')
            ->willReturn(new Person());

        $this->assertion = XMock::of(ViewTesterTestQualityAssertion::class);
        $this->testerGroupAuthorisationMapper = XMock::of(TesterGroupAuthorisationMapper::class);
        $this
            ->testerGroupAuthorisationMapper
            ->method("getAuthorisation")
            ->willReturn(new TesterAuthorisation());

        $this->sut = new \Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Tester\Service\TesterComponentStatisticsService($this->componentStatisticsRepositoryMock,
            $this->testerStatisticsRepositoryMock,
            $this->authorisationService,
            $this->personalDetailsService,
            $this->assertion,
            $this->testerGroupAuthorisationMapper,
            new ComponentBreakdownDtoMapper()
        );
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function testGetThrowsExceptionForInvalidData()
    {
        $date = $this->getFirstOfThisMonth()->sub(new \DateInterval("P1M"));
        $year = (int)$date->format("Y");
        $month = (int)$date->format("m");

        $this->sut->get(1, "x", $year, $month);
    }

    /**
     * @dataProvider dataProviderTestGroupPerformanceCalculation
     * @param $failedCount
     * @param $totalCount
     * @param $totalTime
     * @param $averageVehicleAge
     * @param $expectedAverageTime
     * @param $expectedPercentageFailed
     * @param $expectedAverageVehicleAge
     */
    public function testGroupPerformanceCalculation($failedCount, $totalCount, $totalTime, $averageVehicleAge,
                                                    $expectedAverageTime, $expectedPercentageFailed, $expectedAverageVehicleAge)
    {
        $date = $this->getFirstOfThisMonth()->sub(new \DateInterval("P1M"));
        $year = (int)$date->format("Y");
        $month = (int)$date->format("m");

        $this->componentStatisticsRepositoryMock->method('get')
            ->willReturn([]);

        $this->testerStatisticsRepositoryMock
            ->expects($this->any())
            ->method('get')
            ->willReturn($this->getTesterPerformanceResult($failedCount, $totalCount, $totalTime, $averageVehicleAge, "Popular Garage"));

        $result = $this->sut->get(1, VehicleClassGroupCode::BIKES, $year, $month);

        $this->assertEquals($expectedAverageTime, $result->getGroupPerformance()->getAverageTime()->getTotalSeconds());
        $this->assertEquals($expectedPercentageFailed, $result->getGroupPerformance()->getPercentageFailed());
        $this->assertEquals($expectedAverageVehicleAge, $result->getGroupPerformance()->getAverageVehicleAgeInMonths());
    }

    public function dataProviderTestGroupPerformanceCalculation()
    {
        return [
            //no tests performed, check if division by zero is handled
            [0, 0, 0, 0, 0, 0, 0],
            //only passed tests performed, check if division by zero is handled
            [0, 10, 20, 126, 2, 0, 126],
            //test calculations for average time and failure percentage
            [10, 10, 20, 139, 2, 100, 139],
            [5, 10, 10, 120, 1, 50, 120],
        ];
    }

    /**
     * @dataProvider dataProviderTestComponentFailRateCalculation
     * @param $failedCount
     * @param $expectedPercentage
     */
    public function testComponentFailRateCalculation($failedCount, $expectedPercentage)
    {
        $date = $this->getFirstOfThisMonth()->sub(new \DateInterval("P1M"));
        $year = (int)$date->format("Y");
        $month = (int)$date->format("m");

        $this->componentStatisticsRepositoryMock->method('get')
            ->willReturn($this->getComponentResultsMock([
                'Test Component' => $failedCount,
            ]));

        $this->testerStatisticsRepositoryMock
            ->expects($this->any())
            ->method('get')
            ->willReturn($this->getTesterPerformanceResult(10, 10, 0, 123, "Popular Garage"));

        $result = $this->sut->get(1, VehicleClassGroupCode::BIKES, $year, $month);

        $this->assertEquals($expectedPercentage, $result->getComponents()[0]->getPercentageFailed());

    }

    public function dataProviderTestComponentFailRateCalculation()
    {
        return [
            [1, 10],
            [2, 20],
            [10, 100],
            [0, 0],
        ];
    }

    private function getComponentResultsMock(array $data)
    {
        $results = [];
        foreach ($data as $name => $failedCount) {
            $component = (new ComponentFailRateResult())
                ->setTestItemCategoryName($name)
                ->setFailedCount($failedCount);

            $results[] = $component;
        }

        return $results;
    }

    private function getTesterPerformanceResult($failedCount, $totalCount, $totalTime, $averageVehicleAge, $siteName)
    {
        $testPerformanceResult = (new TesterAtSitePerformanceResult())
            ->setFailedCount($failedCount)
            ->setTotalCount($totalCount)
            ->setAverageVehicleAgeInMonths($averageVehicleAge)
            ->setTotalTime($totalTime)
            ->setSiteName($siteName)
        ;

        return $testPerformanceResult;
    }

    public function testLastMonthStatisticsAreFetchedFromRepository()
    {
        $date = $this->getFirstOfThisMonth()->sub(new \DateInterval("P1M"));
        $year = (int)$date->format("Y");
        $month = (int)$date->format("m");

        $this->componentStatisticsRepositoryMock->method('get')
            ->willReturn([]);
        $componentRepositorySpy = new MethodSpy($this->componentStatisticsRepositoryMock, 'get');

        $this->testerStatisticsRepositoryMock
            ->expects($this->any())
            ->method('get')
            ->willReturn(new TesterAtSitePerformanceResult());
        $testerStatisticsRepositorySpy = new MethodSpy($this->testerStatisticsRepositoryMock, 'get');

        $this->sut->get(1, VehicleClassGroupCode::BIKES, $year, $month);

        $this->assertRepositoryParameters($componentRepositorySpy->getInvocations()[0]->parameters, $year, $month);
        $this->assertRepositoryParameters($testerStatisticsRepositorySpy->getInvocations()[0]->parameters, $year, $month);
    }

    public function testGetFetchedStatisticsFromRepository()
    {
        $this->componentStatisticsRepositoryMock->method('get')
            ->willReturn([]);
        $componentRepositorySpy = new MethodSpy($this->componentStatisticsRepositoryMock, 'get');

        $this->testerStatisticsRepositoryMock->method('get')
            ->willReturn(new TesterAtSitePerformanceResult());
        $testerStatisticsRepositorySpy = new MethodSpy($this->testerStatisticsRepositoryMock, 'get');

        $date = $this->getFirstOfThisMonth();
        $date->sub(new \DateInterval("P3M"));

        $year = (int)$date->format("Y");
        $month = (int)$date->format("m");

        $this->sut->get(1, VehicleClassGroupCode::BIKES, $year, $month);

        $this->assertRepositoryParameters($componentRepositorySpy->getInvocations()[0]->parameters, $year, $month);
        $this->assertRepositoryParameters($testerStatisticsRepositorySpy->getInvocations()[0]->parameters, $year, $month);
    }

    public function assertRepositoryParameters($parameters, $expectedYear, $expectedMonth)
    {
        $repositoryYear = $parameters[2];
        $repositoryMonth = $parameters[3];
        $repositoryGroup = $parameters[1];

        $this->assertEquals($expectedYear, $repositoryYear);
        $this->assertEquals($expectedMonth, $repositoryMonth);
        $this->assertEquals(VehicleClassGroupCode::BIKES, $repositoryGroup);
    }

    private function getFirstOfThisMonth()
    {
       return DateUtils::firstOfThisMonth();
    }
}

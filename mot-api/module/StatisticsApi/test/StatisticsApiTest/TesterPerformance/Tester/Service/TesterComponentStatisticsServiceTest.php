<?php
namespace Dvsa\Mot\Api\StatisticsApiTest\TesterPerformance\Tester\Service;

use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\QueryResult\ComponentFailRateResult;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\Site\QueryResult\TesterPerformanceResult;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\Tester\Repository\TesterComponentStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\Tester\Repository\TesterStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\Tester\Service\TesterComponentStatisticsService;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommonTest\Date\TestDateTimeHolder;
use DvsaCommonTest\TestUtils\Auth\AuthorisationServiceMock;
use DvsaCommonTest\TestUtils\MethodSpy;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Person;
use PersonApi\Service\PersonalDetailsService;

class TesterComponentStatisticsServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var  TesterComponentStatisticsRepository | \PHPUnit_Framework_MockObject_MockObject */
    private $componentStatisticsRepositoryMock;
    /** @var  TesterStatisticsRepository | \PHPUnit_Framework_MockObject_MockObject */
    private $testerStatisticsRepositoryMock;
    /** @var  AuthorisationServiceMock | \PHPUnit_Framework_MockObject_MockObject */
    private $authorisationService;
    /** @var  PersonalDetailsService | \PHPUnit_Framework_MockObject_MockObject */
    private $personalDetailsService;
    /** @var  TesterComponentStatisticsService */
    private $sut;

    private $siteId = 1;

    public function setUp()
    {
        $this->componentStatisticsRepositoryMock = XMock::of(TesterComponentStatisticsRepository::class);
        $this->testerStatisticsRepositoryMock = XMock::of(TesterStatisticsRepository::class);
        $this->authorisationService = new AuthorisationServiceMock();
        $this->authorisationService = $this->authorisationService->grantedAtSite(PermissionAtSite::VTS_VIEW_TEST_QUALITY, $this->siteId);
        $this->personalDetailsService = XMock::of(PersonalDetailsService::class);
        $this->personalDetailsService
            ->expects($this->any())
            ->method('findPerson')
            ->willReturn(new Person());

        $this->sut = new TesterComponentStatisticsService($this->componentStatisticsRepositoryMock,
            $this->testerStatisticsRepositoryMock,
            $this->getDateTimeHolder(),
            $this->authorisationService,
            $this->personalDetailsService
        );
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function testGetThrowsExceptionForInvalidData()
    {
        $date = $this->getDateTimeHolder()->getCurrentDate()->sub(new \DateInterval("P1M"));
        $year = (int)$date->format("Y");
        $month = (int)$date->format("m");

        $this->sut->get(1, 1, "x", $year, $month);
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
        $date = $this->getDateTimeHolder()->getCurrentDate()->sub(new \DateInterval("P1M"));
        $year = (int)$date->format("Y");
        $month = (int)$date->format("m");

        $this->componentStatisticsRepositoryMock->method('get')
            ->willReturn([]);

        $this->testerStatisticsRepositoryMock
            ->expects($this->any())
            ->method('get')
            ->willReturn($this->getTesterPerformanceResult($failedCount, $totalCount, $totalTime, $averageVehicleAge));

        $result = $this->sut->get(1, 1, VehicleClassGroupCode::BIKES, $year, $month);

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
        $date = $this->getDateTimeHolder()->getCurrentDate()->sub(new \DateInterval("P1M"));
        $year = (int)$date->format("Y");
        $month = (int)$date->format("m");

        $this->componentStatisticsRepositoryMock->method('get')
            ->willReturn($this->getComponentResultsMock([
                'Test Component' => $failedCount,
            ]));

        $this->testerStatisticsRepositoryMock
            ->expects($this->any())
            ->method('get')
            ->willReturn($this->getTesterPerformanceResult(10, 10, 0, 123));

        $result = $this->sut->get(1, 1, VehicleClassGroupCode::BIKES, $year, $month);

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

    private function getTesterPerformanceResult($failedCount, $totalCount, $totalTime, $averageVehicleAge)
    {
        $testPerformanceResult = (new TesterPerformanceResult())
            ->setFailedCount($failedCount)
            ->setTotalCount($totalCount)
            ->setAverageVehicleAgeInMonths($averageVehicleAge)
            ->setTotalTime($totalTime);

        return $testPerformanceResult;
    }

    public function testLastMonthStatisticsAreFetchedFromRepository()
    {
        $date = $this->getDateTimeHolder()->getCurrentDate()->sub(new \DateInterval("P1M"));
        $year = (int)$date->format("Y");
        $month = (int)$date->format("m");

        $this->componentStatisticsRepositoryMock->method('get')
            ->willReturn([]);
        $componentRepositorySpy = new MethodSpy($this->componentStatisticsRepositoryMock, 'get');

        $this->testerStatisticsRepositoryMock
            ->expects($this->any())
            ->method('get')
            ->willReturn(new TesterPerformanceResult());
        $testerStatisticsRepositorySpy = new MethodSpy($this->testerStatisticsRepositoryMock, 'get');

        $this->sut->get(1, 1, VehicleClassGroupCode::BIKES, $year, $month);

        $this->assertRepositoryParameters($componentRepositorySpy->getInvocations()[0]->parameters, $year, $month);
        $this->assertRepositoryParameters($testerStatisticsRepositorySpy->getInvocations()[0]->parameters, $year, $month);
    }

    public function testGetFetchedStatisticsFromRepository()
    {
        $this->componentStatisticsRepositoryMock->method('get')
            ->willReturn([]);
        $componentRepositorySpy = new MethodSpy($this->componentStatisticsRepositoryMock, 'get');

        $this->testerStatisticsRepositoryMock->method('get')
            ->willReturn(new TesterPerformanceResult());
        $testerStatisticsRepositorySpy = new MethodSpy($this->testerStatisticsRepositoryMock, 'get');

        $date = $this->getDateTimeHolder()->getCurrentDate();
        $date->sub(new \DateInterval("P3M"));

        $year = (int)$date->format("Y");
        $month = (int)$date->format("m");

        $this->sut->get(1, 1, VehicleClassGroupCode::BIKES, $year, $month);

        $this->assertRepositoryParameters($componentRepositorySpy->getInvocations()[0]->parameters, $year, $month);
        $this->assertRepositoryParameters($testerStatisticsRepositorySpy->getInvocations()[0]->parameters, $year, $month);
    }

    /**
     * @expectedException \DvsaCommon\Exception\UnauthorisedException
     */
    public function testGetThrowsExceptionIfUserHasIncorrectPermission()
    {
        // GIVEN I do not have any permissions
        $this->authorisationService->clearAll();

        $date = $this->getDateTimeHolder()->getCurrentDate();
        $year = (int)$date->format("Y");
        $month = (int)$date->format("m");

        $this->sut->get(1, 1, VehicleClassGroupCode::BIKES, $year, $month);
    }

    public function assertRepositoryParameters($parameters, $expectedYear, $expectedMonth)
    {
        $repositoryYear = $parameters[3];
        $repositoryMonth = $parameters[4];
        $repositoryGroup = $parameters[2];

        $this->assertEquals($expectedYear, $repositoryYear);
        $this->assertEquals($expectedMonth, $repositoryMonth);
        $this->assertEquals(VehicleClassGroupCode::BIKES, $repositoryGroup);
    }

    private function getDateTimeHolder()
    {
        return new TestDateTimeHolder(new \DateTime("2016-06-21"));
    }
}

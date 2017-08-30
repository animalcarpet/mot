<?php

namespace Dvsa\Mot\Api\StatisticsApiTest\TesterPerformance\Tester\Service;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\Mapper\ComponentBreakdownDtoMapper;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Common\QueryResult\ComponentFailRateResult;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterAtSite\Repository\TesterAtSiteComponentStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterAtSite\Service\TesterAtSiteComponentStatisticsService;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\QueryResult\TesterAtSitePerformanceResult;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\Repository\TesterAtSiteSingleGroupStatisticsRepository;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Date\DateTimeHolderInterface;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Date\LastMonthsDateRange;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommonApiTest\Stub\ApiIdentityProviderStub;
use DvsaCommonApiTest\Stub\IdentityStub;
use DvsaCommonTest\Date\TestDateTimeHolder;
use DvsaCommonTest\TestUtils\Auth\AuthorisationServiceMock;
use DvsaCommonTest\TestUtils\MethodSpy;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Site;
use DvsaEntities\Repository\SiteRepository;
use PersonApi\Service\PersonalDetailsService;

class TesterAtSiteComponentStatisticsServiceTest extends \PHPUnit_Framework_TestCase
{
    const PERIOD_LAST_3_MONTHS = 3;
    private $siteRepository;
    /** @var TesterAtSiteComponentStatisticsRepository | \PHPUnit_Framework_MockObject_MockObject */
    private $componentStatisticsRepositoryMock;
    /** @var \Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\Repository\TesterAtSiteSingleGroupStatisticsRepository | \PHPUnit_Framework_MockObject_MockObject */
    private $testerStatisticsRepositoryMock;
    /** @var AuthorisationServiceMock | \PHPUnit_Framework_MockObject_MockObject */
    private $authorisationService;
    /** @var PersonalDetailsService | \PHPUnit_Framework_MockObject_MockObject */
    private $personalDetailsService;
    /** @var TesterAtSiteComponentStatisticsService */
    private $sut;

    private $siteId = 1;
    /** @var ApiIdentityProviderStub */
    private $identityProvider;
    /** @var DateTimeHolderInterface */
    private $dateTimeHolder;

    public function setUp()
    {
        $this->dateTimeHolder = XMock::of(DateTimeHolder::class);
        $this->componentStatisticsRepositoryMock = XMock::of(TesterAtSiteComponentStatisticsRepository::class);
        $this->testerStatisticsRepositoryMock = XMock::of(TesterAtSiteSingleGroupStatisticsRepository::class);
        $this->authorisationService = new AuthorisationServiceMock();
        $this->authorisationService = $this->authorisationService->grantedAtSite(PermissionAtSite::VTS_VIEW_TEST_QUALITY,
            $this->siteId);
        $this->personalDetailsService = XMock::of(PersonalDetailsService::class);
        $this->personalDetailsService
            ->expects($this->any())
            ->method('findPerson')
            ->willReturn(new Person());

        $identityStub = new IdentityStub('user');
        $identityStub->setUserId(1);
        $this->identityProvider = new ApiIdentityProviderStub();
        $this->identityProvider->setIdentity($identityStub);

        $this->siteRepository = XMock::of(SiteRepository::class);
        $this->siteRepository->method('get')->willReturn(new Site());

        $this->sut = new TesterAtSiteComponentStatisticsService($this->componentStatisticsRepositoryMock,
            $this->testerStatisticsRepositoryMock,
            $this->authorisationService,
            $this->personalDetailsService,
            new ComponentBreakdownDtoMapper(),
            $this->identityProvider,
            new TestDateTimeHolder(DateUtils::today()),
            $this->siteRepository
        );
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function testGetThrowsExceptionForInvalidData()
    {
        $this->sut->get(1, 1, 'x', 0);
    }

    /**
     * @dataProvider dataProviderTestGroupPerformanceCalculation
     *
     * @param $failedCount
     * @param $totalCount
     * @param $totalTime
     * @param $averageVehicleAge
     * @param $expectedAverageTime
     * @param $expectedPercentageFailed
     * @param $expectedAverageVehicleAge
     */
    public function testGroupPerformanceCalculation(
        $failedCount,
        $totalCount,
        $totalTime,
        $averageVehicleAge,
        $expectedAverageTime,
        $expectedPercentageFailed,
        $expectedAverageVehicleAge
    ) {
        $this->componentStatisticsRepositoryMock->method('get')
            ->willReturn([]);

        $this->testerStatisticsRepositoryMock
            ->expects($this->any())
            ->method('get')
            ->willReturn($this->getTesterPerformanceResult($failedCount, $totalCount, $totalTime, $averageVehicleAge, 'Popular Garage'));

        $result = $this->sut->get(1, 1, VehicleClassGroupCode::BIKES, self::PERIOD_LAST_3_MONTHS);

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
     *
     * @param $failedCount
     * @param $expectedPercentage
     */
    public function testComponentFailRateCalculation($failedCount, $expectedPercentage)
    {
        $this->componentStatisticsRepositoryMock->method('get')
            ->willReturn($this->getComponentResultsMock([
                'Test Component' => $failedCount,
            ]));

        $this->testerStatisticsRepositoryMock
            ->expects($this->any())
            ->method('get')
            ->willReturn($this->getTesterPerformanceResult(10, 10, 0, 123, 'Popular Garage'));

        $result = $this->sut->get(1, 1, VehicleClassGroupCode::BIKES, self::PERIOD_LAST_3_MONTHS);

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
        $monthRange = 1;

        $this->componentStatisticsRepositoryMock->method('get')
            ->willReturn([]);
        $componentRepositorySpy = new MethodSpy($this->componentStatisticsRepositoryMock, 'get');

        $this->testerStatisticsRepositoryMock
            ->expects($this->any())
            ->method('get')
            ->willReturn(new TesterAtSitePerformanceResult());
        $testerStatisticsRepositorySpy = new MethodSpy($this->testerStatisticsRepositoryMock, 'get');

        $this->sut->get(1, 1, VehicleClassGroupCode::BIKES, $monthRange);

        $this->assertRepositoryParameters($componentRepositorySpy->getInvocations()[0]->parameters, $monthRange);
        $this->assertRepositoryParameters($testerStatisticsRepositorySpy->getInvocations()[0]->parameters, $monthRange);
    }

    public function testGetFetchedStatisticsFromRepository()
    {
        $this->componentStatisticsRepositoryMock->method('get')
            ->willReturn([]);
        $componentRepositorySpy = new MethodSpy($this->componentStatisticsRepositoryMock, 'get');

        $this->testerStatisticsRepositoryMock->method('get')
            ->willReturn(new TesterAtSitePerformanceResult());
        $testerStatisticsRepositorySpy = new MethodSpy($this->testerStatisticsRepositoryMock, 'get');

        $this->sut->get(1, 1, VehicleClassGroupCode::BIKES, self::PERIOD_LAST_3_MONTHS);

        $this->assertRepositoryParameters($componentRepositorySpy->getInvocations()[0]->parameters, self::PERIOD_LAST_3_MONTHS);
        $this->assertRepositoryParameters($testerStatisticsRepositorySpy->getInvocations()[0]->parameters, self::PERIOD_LAST_3_MONTHS);
    }

    /**
     * @expectedException \DvsaCommon\Exception\UnauthorisedException
     */
    public function testGetThrowsExceptionIfUserHasIncorrectPermission()
    {
        // GIVEN I do not have any permissions
        $this->authorisationService->clearAll();

        $this->sut->get(1, 2, VehicleClassGroupCode::BIKES, self::PERIOD_LAST_3_MONTHS);
    }

    public function assertRepositoryParameters($parameters, $expectedMonthRange)
    {
        /** @var LastMonthsDateRange $repositoryMonthRange */
        $repositoryMonthRange = $parameters[3];
        $repositoryGroup = $parameters[2];

        $this->assertEquals($expectedMonthRange, $repositoryMonthRange->getNumberOfMonths());
        $this->assertEquals(VehicleClassGroupCode::BIKES, $repositoryGroup);
    }
}

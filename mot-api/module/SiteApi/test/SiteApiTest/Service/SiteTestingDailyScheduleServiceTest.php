<?php

namespace SiteApiTest\Service;

use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Date\Time;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonApiTest\Transaction\TestTransactionExecutor;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\SiteTestingDailySchedule;
use DvsaEntities\Repository\SiteRepository;
use DvsaEntities\Repository\SiteTestingDailyScheduleRepository;
use SiteApi\Service\SiteTestingDailyScheduleService;
use SiteApi\Service\Validator\SiteTestingDailyScheduleValidator;

/**
 * Class SiteTestingDailyScheduleServiceTest.
 */
class SiteTestingDailyScheduleServiceTest extends AbstractServiceTestCase
{
    const SITE_ID = 1;
    /**
     * @var SiteTestingDailyScheduleRepository
     */
    private $scheduleRepository;
    /**
     * @var SiteRepository
     */
    private $siteRepository;

    public function setUp()
    {
        $this->scheduleRepository = XMock::of(SiteTestingDailyScheduleRepository::class);
        $this->siteRepository = XMock::of(SiteRepository::class);
    }

    public function testGetSchedule_givenValidSiteId_shouldReturnSchedule()
    {
        $weeklyScheduleExpectedData = $this->getDummyScheduleResultData();
        $service = $this->getService();

        $this->setupMockForCalls(
            $this->scheduleRepository,
            'findBy',
            $this->getDummyScheduleObjects(),
            ['site' => self::SITE_ID]
        );

        $weeklyScheduleData = $service->getSchedule(self::SITE_ID);
        $this->assertEquals($weeklyScheduleExpectedData, $weeklyScheduleData);
    }

    private function getDummyScheduleResultData()
    {
        $siteData = [];

        for ($i = 1; $i < 8; ++$i) {
            $siteData[] = [
                'weekday' => $i,
                'openTime' => '09:00:00',
                'closeTime' => '17:00:00',
            ];
        }

        return $siteData;
    }

    /**
     * @return SiteTestingDailyScheduleService
     */
    private function getService()
    {
        $service = new SiteTestingDailyScheduleService(
            $this->scheduleRepository,
            $this->siteRepository,
            new SiteTestingDailyScheduleValidator(),
            $this->getAuthorisationService()
        );

        return TestTransactionExecutor::inject($service);
    }

    /**
     * @return MotAuthorisationServiceInterface
     */
    private function getAuthorisationService()
    {
        $service = XMock::of(MotAuthorisationServiceInterface::class);

        return $service;
    }

    private function getDummyScheduleObjects()
    {
        $weeklySchedule = [];

        for ($i = 1; $i < 8; ++$i) {
            $tmp = new SiteTestingDailySchedule();
            $tmp->setWeekday($i)
                ->setOpenTime(Time::fromIso8601('09:00:00'))
                ->setCloseTime(Time::fromIso8601('17:00:00'));
            $weeklySchedule[] = $tmp;
        }

        return $weeklySchedule;
    }

    public function testUpdateSchedule_givenValidSchedule_shouldUpdateSchedule()
    {
        $service = $this->getService();

        $scheduleData = $this->getDummyScheduleData();
        $expectedScheduleData = $this->getDummyScheduleResultData();

        $this->setupMockForSingleCall(
            $this->scheduleRepository,
            'findBy',
            $this->getDummyScheduleObjects(),
            ['site' => self::SITE_ID]
        );

        $resultScheduleData = $service->updateSchedule(self::SITE_ID, $scheduleData);
        $this->assertTrue(TestTransactionExecutor::isFlushed($service));
        $this->assertEquals($expectedScheduleData, $resultScheduleData);
    }

    private function getDummyScheduleData()
    {
        $siteData = [];

        for ($i = 1; $i < 8; ++$i) {
            $siteData[] = [
                'weekday' => $i,
                'openTime' => '09:00:00',
                'closeTime' => '17:00:00',
                'isClosed' => false,
            ];
        }

        return ['weeklySchedule' => $siteData];
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\NotFoundException
     * @expectedExceptionMessage weekly schedule for site: 1 not found
     */
    public function testGetSchedule_givenNoSchedule_shouldThrowException()
    {
        $service = $this->getService();

        $this->setupMockForSingleCall(
            $this->scheduleRepository,
            'findBy',
            null,
            ['site' => self::SITE_ID]
        );
        $service->getSchedule(self::SITE_ID);
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Invalid time format provided
     */
    public function testUpdateSchedule_givenInvalidScheduleData_shouldReturnError()
    {
        $service = $this->getService();

        $scheduleData = $this->getDummyScheduleData();

        $scheduleData['weeklySchedule'][3]['closeTime'] = 'apple';
        $this->setupMockForCalls(
            $this->siteRepository,
            'getReference',
            new Site(),
            self::SITE_ID
        );

        $resultScheduleData = $service->updateSchedule(self::SITE_ID, $scheduleData);
        $this->assertTrue(TestTransactionExecutor::isFlushed($service));
        $this->assertEquals($scheduleData, $resultScheduleData);
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage A valid site opening times schedule has not been provided
     */
    public function testUpdateSchedule_givenEmptySchedule_shouldThrowException()
    {
        $service = $this->getService();
        $service->updateSchedule(self::SITE_ID, '');
    }
}

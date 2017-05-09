<?php

namespace DvsaClientTest\Mapper;

use DvsaClient\Mapper\VehicleTestingStationOpeningHoursMapper;
use DvsaCommon\HttpRestJson\Client;
use DvsaCommon\UrlBuilder\UrlBuilder;

/**
 * Class VehicleTestingStationOpeningHoursMapperTest.
 */
class VehicleTestingStationOpeningHoursMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var VehicleTestingStationOpeningHoursMapper
     */
    private $mapper;

    /** @var $client \PHPUnit_Framework_MockObject_MockBuilder */
    private $client;

    public function setUp()
    {
        $this->client = \DvsaCommonTest\TestUtils\XMock::of(Client::class);

        $this->mapper = new VehicleTestingStationOpeningHoursMapper($this->client);
    }

    public function testUpdate_givenValidData_shouldPut()
    {
        $testData = $this->getDummyScheduleFormData();
        $jsonData = $this->getJsonScheduleData();
        $apiUrl = UrlBuilder::create()->vehicleTestingStation()->queryParam('id', 1)->siteOpeningHours();

        $this->client->expects($this->any())->method('putJson')->with($apiUrl, $jsonData);

        $this->mapper->update(1, $testData);
    }

    public function testUpdate_givenTextForMondayOpenTime_shouldPut()
    {
        $testData = $this->getDummyScheduleFormData();

        $testData['mondayOpenTime'] = 'apple';
        $jsonData = $this->getJsonScheduleData();

        $jsonData['weeklySchedule'][0]['openTime'] = 'appleam';
        $apiUrl = UrlBuilder::create()->vehicleTestingStation()->queryParam('id', 1)->siteOpeningHours();

        $this->client->expects($this->any())->method('putJson')->with($apiUrl, $jsonData);

        $this->mapper->update(1, $testData);
    }

    public function testUpdate_givenTextForMondayCloseTime_shouldPut()
    {
        $testData = $this->getDummyScheduleFormData();

        $testData['mondayCloseTime'] = 'waffles';
        $jsonData = $this->getJsonScheduleData();

        $jsonData['weeklySchedule'][0]['closeTime'] = 'wafflespm';
        $apiUrl = UrlBuilder::create()->vehicleTestingStation()->queryParam('id', 1)->siteOpeningHours();

        $this->client->expects($this->any())->method('putJson')->with($apiUrl, $jsonData);

        $this->mapper->update(1, $testData);
    }

    public function testUpdate_givenMondayOpenTimeEmpty_shouldPut()
    {
        $testData = $this->getDummyScheduleFormData();

        $testData['mondayOpenTime'] = '';
        $jsonData = $this->getJsonScheduleData();

        $jsonData['weeklySchedule'][0]['openTime'] = 'am';
        $apiUrl = UrlBuilder::create()->vehicleTestingStation()->queryParam('id', 1)->siteOpeningHours();

        $this->client->expects($this->any())->method('putJson')->with($apiUrl, $jsonData);

        $this->mapper->update(1, $testData);
    }

    public function testUpdate_givenMondayCloseTimeEmpty_shouldPut()
    {
        $testData = $this->getDummyScheduleFormData();

        $testData['mondayCloseTime'] = '';
        $jsonData = $this->getJsonScheduleData();

        $jsonData['weeklySchedule'][0]['closeTime'] = 'pm';
        $apiUrl = UrlBuilder::create()->vehicleTestingStation()->queryParam('id', 1)->siteOpeningHours();

        $this->client->expects($this->any())->method('putJson')->with($apiUrl, $jsonData);

        $this->mapper->update(1, $testData);
    }

    public function testUpdate_givenTextForSaturdayOpenTime_shouldPut()
    {
        $testData = $this->getDummyScheduleFormData();

        $testData['saturdayOpenTime'] = 'apple';
        $jsonData = $this->getJsonScheduleData();

        $jsonData['weeklySchedule'][5]['openTime'] = 'appleam';
        $apiUrl = UrlBuilder::create()->vehicleTestingStation()->queryParam('id', 1)->siteOpeningHours();

        $this->client->expects($this->any())->method('putJson')->with($apiUrl, $jsonData);

        $this->mapper->update(1, $testData);
    }

    public function testUpdate_givenTextForSaturdayCloseTime_shouldPut()
    {
        $testData = $this->getDummyScheduleFormData();

        $testData['saturdayCloseTime'] = 'waffles';
        $jsonData = $this->getJsonScheduleData();

        $jsonData['weeklySchedule'][5]['closeTime'] = 'wafflespm';
        $apiUrl = UrlBuilder::create()->vehicleTestingStation()->queryParam('id', 1)->siteOpeningHours();

        $this->client->expects($this->any())->method('putJson')->with($apiUrl, $jsonData);

        $this->mapper->update(1, $testData);
    }

    public function testUpdate_givenPositiveOutOfBoundsTuesdayOpenTime_shouldPut()
    {
        $testData = $this->getDummyScheduleFormData();

        $testData['tuesdayOpenTime'] = '13.00';
        $jsonData = $this->getJsonScheduleData();

        $jsonData['weeklySchedule'][1]['openTime'] = '13.00am';
        $apiUrl = UrlBuilder::create()->vehicleTestingStation()->queryParam('id', 1)->siteOpeningHours();

        $this->client->expects($this->any())->method('putJson')->with($apiUrl, $jsonData);

        $this->mapper->update(1, $testData);
    }

    public function testUpdate_givenPositiveOutOfBoundsTuesdayCloseTime_shouldPut()
    {
        $testData = $this->getDummyScheduleFormData();

        $testData['tuesdayCloseTime'] = '13.00';
        $jsonData = $this->getJsonScheduleData();

        $jsonData['weeklySchedule'][1]['closeTime'] = '13.00pm';
        $apiUrl = UrlBuilder::create()->vehicleTestingStation()->queryParam('id', 1)->siteOpeningHours();

        $this->client->expects($this->any())->method('putJson')->with($apiUrl, $jsonData);

        $this->mapper->update(1, $testData);
    }

    public function testUpdate_givenMissingOpenTimeWednesday_shouldPut()
    {
        $testData = $this->getDummyScheduleFormData();

        unset($testData['wednesdayOpenTime']);
        $jsonData = $this->getJsonScheduleData();

        $jsonData['weeklySchedule'][2]['openTime'] = 'am';
        $apiUrl = UrlBuilder::create()->vehicleTestingStation()->queryParam('id', 1)->siteOpeningHours();

        $this->client->expects($this->any())->method('putJson')->with($apiUrl, $jsonData);

        $this->mapper->update(1, $testData);
    }

    public function testUpdate_givenMissingCloseTimeWednesday_shouldPut()
    {
        $testData = $this->getDummyScheduleFormData();

        unset($testData['wednesdayCloseTime']);
        $jsonData = $this->getJsonScheduleData();
        $jsonData['weeklySchedule'][2]['closeTime'] = 'pm';
        $apiUrl = UrlBuilder::create()->vehicleTestingStation()->queryParam('id', 1)->siteOpeningHours();

        $this->client->expects($this->any())->method('putJson')->with($apiUrl, $jsonData);

        $this->mapper->update(1, $testData);
    }

    public function testUpdate_givenMissingOpenTimeThursday_shouldPut()
    {
        $testData = $this->getDummyScheduleFormData();

        unset($testData['thursdayOpenTime']);
        $jsonData = $this->getJsonScheduleData();

        $jsonData['weeklySchedule'][3]['openTime'] = 'am';
        $apiUrl = UrlBuilder::create()->vehicleTestingStation()->queryParam('id', 1)->siteOpeningHours();

        $this->client->expects($this->any())->method('putJson')->with($apiUrl, $jsonData);

        $this->mapper->update(1, $testData);
    }

    public function testUpdate_givenMissingCloseTimeThursday_shouldPut()
    {
        $testData = $this->getDummyScheduleFormData();

        unset($testData['thursdayCloseTime']);
        $jsonData = $this->getJsonScheduleData();
        $jsonData['weeklySchedule'][3]['closeTime'] = 'pm';
        $apiUrl = UrlBuilder::create()->vehicleTestingStation()->queryParam('id', 1)->siteOpeningHours();

        $this->client->expects($this->any())->method('putJson')->with($apiUrl, $jsonData);

        $this->mapper->update(1, $testData);
    }

    public function testUpdate_givenMissingTimePeriodFriday_shouldPut()
    {
        $testData = $this->getDummyScheduleFormData();
        $testData['fridayOpenTimePeriod'] = '';

        $jsonData = $this->getJsonScheduleData();
        $jsonData['weeklySchedule'][4]['openTime'] = '9.00';

        $apiUrl = UrlBuilder::create()->vehicleTestingStation()->queryParam('id', 1)->siteOpeningHours();

        $this->client->expects($this->any())->method('putJson')->with($apiUrl, $jsonData);

        $this->mapper->update(1, $testData);
    }

    public function testUpdate_givenOpenTimeSaturdayWithoutMinutes_shouldPut()
    {
        $testData = $this->getDummyScheduleFormData();
        $testData['saturdayOpenTime'] = '9';

        $jsonData = $this->getJsonScheduleData();

        $apiUrl = UrlBuilder::create()->vehicleTestingStation()->queryParam('id', 1)->siteOpeningHours();

        $this->client->expects($this->any())->method('putJson')->with($apiUrl, $jsonData);

        $this->mapper->update(1, $testData);
    }

    public function testUpdate_givenCloseTimeSaturdayWithoutMinutes_shouldPut()
    {
        $testData = $this->getDummyScheduleFormData();
        $testData['saturdayCloseTime'] = '5.60';

        $jsonData = $this->getJsonScheduleData();
        $jsonData['weeklySchedule'][5]['closeTime'] = '5.60pm';

        $apiUrl = UrlBuilder::create()->vehicleTestingStation()->queryParam('id', 1)->siteOpeningHours();

        $this->client->expects($this->any())->method('putJson')->with($apiUrl, $jsonData);

        $this->mapper->update(1, $testData);
    }
    public function testUpdate_givenOpenTimeSundayWithSeconds_shouldPut()
    {
        $testData = $this->getDummyScheduleFormData();
        $testData['sundayOpenTime'] = '9:00:23';

        $jsonData = $this->getJsonScheduleData();
        $jsonData['weeklySchedule'][6]['openTime'] = '9:00:23am';

        $apiUrl = UrlBuilder::create()->vehicleTestingStation()->queryParam('id', 1)->siteOpeningHours();

        $this->client->expects($this->any())->method('putJson')->with($apiUrl, $jsonData);

        $this->mapper->update(1, $testData);
    }

    public function testUpdate_givenOpenTimeWithDotsDivider_shouldPut()
    {
        $testData = $this->getDummyScheduleFormData();
        $testData['sundayOpenTime'] = '9.00';

        $jsonData = $this->getJsonScheduleData();

        $apiUrl = UrlBuilder::create()->vehicleTestingStation()->queryParam('id', 1)->siteOpeningHours();

        $this->client->expects($this->any())->method('putJson')->with($apiUrl, $jsonData);

        $this->mapper->update(1, $testData);
    }

    public function testUpdate_givenOpenTimeWithColonDivider_shouldPut()
    {
        $testData = $this->getDummyScheduleFormData();
        $testData['sundayOpenTime'] = '9:00';

        $jsonData = $this->getJsonScheduleData();

        $apiUrl = UrlBuilder::create()->vehicleTestingStation()->queryParam('id', 1)->siteOpeningHours();

        $this->client->expects($this->any())->method('putJson')->with($apiUrl, $jsonData);

        $this->mapper->update(1, $testData);
    }

    public function testUpdate_givenOpenTimeWithCommaDivider_shouldPut()
    {
        $testData = $this->getDummyScheduleFormData();
        $testData['sundayOpenTime'] = '9,00';

        $jsonData = $this->getJsonScheduleData();
        $jsonData['weeklySchedule'][6]['openTime'] = '9,00am';

        $apiUrl = UrlBuilder::create()->vehicleTestingStation()->queryParam('id', 1)->siteOpeningHours();

        $this->client->expects($this->any())->method('putJson')->with($apiUrl, $jsonData);

        $this->mapper->update(1, $testData);
    }

    public function testUpdate_givenDayIsClosed_shouldPut()
    {
        $testData = $this->getDummyScheduleFormData();
        $testData['saturdayIsClosed'] = 'true';

        $jsonData = $this->getJsonScheduleData();
        $jsonData['weeklySchedule'][5]['openTime'] = '';
        $jsonData['weeklySchedule'][5]['closeTime'] = '';
        $jsonData['weeklySchedule'][5]['isClosed'] = true;

        $apiUrl = UrlBuilder::create()->vehicleTestingStation()->queryParam('id', 1)->siteOpeningHours();

        $this->client->expects($this->any())->method('putJson')->with($apiUrl, $jsonData);

        $this->mapper->update(1, $testData);
    }

    private function getDummyScheduleFormData()
    {
        $data = [
            'mondayOpenTime' => '9.00',
            'mondayOpenTimePeriod' => 'am',
            'mondayCloseTime' => '5.00',
            'mondayCloseTimePeriod' => 'pm',
            'mondayIsClosed' => '',
            'tuesdayOpenTime' => '9.00',
            'tuesdayOpenTimePeriod' => 'am',
            'tuesdayCloseTime' => '5.00',
            'tuesdayCloseTimePeriod' => 'pm',
            'tuesdayIsClosed' => '',
            'wednesdayOpenTime' => '9.00',
            'wednesdayOpenTimePeriod' => 'am',
            'wednesdayCloseTime' => '5.00',
            'wednesdayCloseTimePeriod' => 'pm',
            'wednesdayIsClosed' => '',
            'thursdayOpenTime' => '9.00',
            'thursdayOpenTimePeriod' => 'am',
            'thursdayCloseTime' => '5.00',
            'thursdayCloseTimePeriod' => 'pm',
            'thursdayIsClosed' => '',
            'fridayOpenTime' => '9.00',
            'fridayOpenTimePeriod' => 'am',
            'fridayCloseTime' => '5.00',
            'fridayCloseTimePeriod' => 'pm',
            'fridayIsClosed' => '',
            'saturdayOpenTime' => '9.00',
            'saturdayOpenTimePeriod' => 'am',
            'saturdayCloseTime' => '5.00',
            'saturdayCloseTimePeriod' => 'pm',
            'saturdayIsClosed' => '',
            'sundayOpenTime' => '9.00',
            'sundayOpenTimePeriod' => 'am',
            'sundayCloseTime' => '5.00',
            'sundayCloseTimePeriod' => 'pm',
            'sundayIsClosed' => '',
        ];

        return $data;
    }

    private function getJsonScheduleData()
    {
        $siteData = [];

        for ($i = 1; $i < 8; ++$i) {
            $siteData['weeklySchedule'][] = [
                'weekday' => $i,
                'openTime' => '09:00:00',
                'closeTime' => '17:00:00',
                'isClosed' => '',
            ];
        }

        return $siteData;
    }
}

<?php

namespace SiteApiTest\Service\Validator;

use SiteApi\Service\Validator\SiteTestingDailyScheduleValidator;

/**
 * Class SiteTestingDailyScheduleValidatorTest.
 */
class SiteTestingDailyScheduleValidatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var $scheduleValidator SiteTestingDailyScheduleValidator */
    private $scheduleValidator;

    public function setup()
    {
        $this->scheduleValidator = new SiteTestingDailyScheduleValidator();
    }

    public function testValidateOpeningHours_givenValidDaysAndTimes_shouldRun()
    {
        $data = $this->getDummyScheduleData();
        $this->scheduleValidator->validateOpeningHours($data);
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

    public function testValidateOpeningHours_givenZeroMinutes_shouldRun()
    {
        $data = $this->getDummyScheduleData();
        $data['weeklySchedule'][1]['closeTime'] = '16:00:00';
        $this->scheduleValidator->validateOpeningHours($data);
    }

    public function testValidateOpeningHours_given30Minutes_shouldRun()
    {
        $data = $this->getDummyScheduleData();
        $data['weeklySchedule'][1]['closeTime'] = '13:30:00';
        $this->scheduleValidator->validateOpeningHours($data);
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\RequiredFieldException
     */
    public function testValidateOpeningHours_givenMissingDayMonday_shouldThrowRequiredFieldException()
    {
        $data = $this->getDummyScheduleData();
        $data['weeklySchedule'][0]['weekday'] = null;
        $this->scheduleValidator->validateOpeningHours($data);
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\RequiredFieldException
     */
    public function testValidateOpeningHours_givenMissingDayWednesday_shouldRequiredFieldThrowException()
    {
        $data = $this->getDummyScheduleData();
        $data['weeklySchedule'][2]['weekday'] = null;
        $this->scheduleValidator->validateOpeningHours($data);
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\RequiredFieldException
     */
    public function testValidateOpeningHours_givenMissingDaySunday_shouldThrowRequiredFieldException()
    {
        $data = $this->getDummyScheduleData();
        $data['weeklySchedule'][6]['weekday'] = null;
        $this->scheduleValidator->validateOpeningHours($data);
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\RequiredFieldException
     */
    public function testValidateOpeningHours_givenWeekdayIsZero_shouldThrowRequiredFieldException()
    {
        $data = $this->getDummyScheduleData();
        $data['weeklySchedule'][1]['weekday'] = 0;
        $this->scheduleValidator->validateOpeningHours($data);
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Weekday must be numeric from 1 to 7
     */
    public function testValidateOpeningHours_givenWeekdayLessThanOne_shouldThrowBadRequestException()
    {
        $data = $this->getDummyScheduleData();
        $data['weeklySchedule'][1]['weekday'] = -1;
        $this->scheduleValidator->validateOpeningHours($data);
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Weekday must be numeric from 1 to 7
     */
    public function testValidateOpeningHours_givenWeekdayGreaterThanSeven_shouldThrowBadRequestException()
    {
        $data = $this->getDummyScheduleData();
        $data['weeklySchedule'][3]['weekday'] = 8;
        $this->scheduleValidator->validateOpeningHours($data);
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Opening time must be provided when the site is indicated as open.
     */
    public function testValidateOpeningHours_givenMondayNotClosedAndOpenTimeMissing_shouldThrowBadRequestException()
    {
        $data = $this->getDummyScheduleData();
        $data['weeklySchedule'][0]['openTime'] = null;
        $this->scheduleValidator->validateOpeningHours($data);
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Closing time must be provided when the site is indicated as open.
     */
    public function testValidateOpeningHours_givenMondayNotClosedAndCloseTimeMissing_shouldThrowBadRequestException()
    {
        $data = $this->getDummyScheduleData();
        $data['weeklySchedule'][0]['closeTime'] = null;
        $this->scheduleValidator->validateOpeningHours($data);
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Opening time must be provided when the site is indicated as open.
     */
    public function testValidateOpeningHours_givenWednesdayNotClosedAndOpenTimeMissing_shouldThrowBadRequestException()
    {
        $data = $this->getDummyScheduleData();
        $data['weeklySchedule'][2]['openTime'] = null;
        $this->scheduleValidator->validateOpeningHours($data);
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Closing time must be provided when the site is indicated as open.
     */
    public function testValidateOpeningHours_givenWednesdayNotClosedAndCloseTimeMissing_shouldThrowBadRequestException()
    {
        $data = $this->getDummyScheduleData();
        $data['weeklySchedule'][2]['closeTime'] = null;
        $this->scheduleValidator->validateOpeningHours($data);
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Opening time must be provided when the site is indicated as open.
     */
    public function testValidateOpeningHours_givenSundayNotClosedAndOpenTimeMissing_shouldThrowBadRequestException()
    {
        $data = $this->getDummyScheduleData();
        $data['weeklySchedule'][6]['openTime'] = null;
        $this->scheduleValidator->validateOpeningHours($data);
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Closing time must be provided when the site is indicated as open.
     */
    public function testValidateOpeningHours_givenSundayNotClosedAndCloseTimeMissing_shouldThrowBadRequestException()
    {
        $data = $this->getDummyScheduleData();
        $data['weeklySchedule'][6]['closeTime'] = null;
        $this->scheduleValidator->validateOpeningHours($data);
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Invalid time format provided
     */
    public function testValidateOpeningHours_givenTuesdayNotClosedAndOpenTimeInvalid_shouldThrowBadRequestException()
    {
        $data = $this->getDummyScheduleData();
        $data['weeklySchedule'][1]['openTime'] = 'apple';
        $this->scheduleValidator->validateOpeningHours($data);
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Invalid time format provided
     */
    public function testValidateOpeningHours_givenTuesdayNotClosedAndCloseTimeInvalid_shouldThrowBadRequestException()
    {
        $data = $this->getDummyScheduleData();
        $data['weeklySchedule'][1]['closeTime'] = '24:00:00';
        $this->scheduleValidator->validateOpeningHours($data);
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Invalid time format provided
     */
    public function testValidateOpeningHours_givenThursdayNotClosedAndOpenTimeInvalid_shouldThrowBadRequestException()
    {
        $data = $this->getDummyScheduleData();
        $data['weeklySchedule'][3]['openTime'] = '12.00';
        $this->scheduleValidator->validateOpeningHours($data);
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Invalid time format provided
     */
    public function testValidateOpeningHours_givenThursdayNotClosedAndCloseTimeInvalid_shouldThrowBadRequestException()
    {
        $data = $this->getDummyScheduleData();
        $data['weeklySchedule'][3]['closeTime'] = '$%£)@%)£@%)£@';
        $this->scheduleValidator->validateOpeningHours($data);
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Invalid time format provided
     */
    public function testValidateOpeningHours_givenSaturdayNotClosedAndOpenTimeInvalid_shouldThrowBadRequestException()
    {
        $data = $this->getDummyScheduleData();
        $data['weeklySchedule'][5]['openTime'] = 2131241241321;
        $this->scheduleValidator->validateOpeningHours($data);
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Invalid time format provided
     */
    public function testValidateOpeningHours_givenSaturdayNotClosedAndCloseTimeInvalid_shouldThrowBadRequestException()
    {
        $data = $this->getDummyScheduleData();
        $data['weeklySchedule'][5]['closeTime'] = 10082014;
        $this->scheduleValidator->validateOpeningHours($data);
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Invalid time format provided: minutes must be in increments of 30
     */
    public function testValidateOpeningHours_givenInvalidMinutesAround30_shouldThrowBadRequestException()
    {
        $data = $this->getDummyScheduleData();
        $data['weeklySchedule'][5]['closeTime'] = '12:31:00';
        $this->scheduleValidator->validateOpeningHours($data);
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Invalid time format provided: minutes must be in increments of 30
     */
    public function testValidateOpeningHours_givenInvalidMinutesAroundZero_shouldThrowBadRequestException()
    {
        $data = $this->getDummyScheduleData();
        $data['weeklySchedule'][5]['closeTime'] = '12:01:00';
        $this->scheduleValidator->validateOpeningHours($data);
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage A valid site opening times schedule has not been provided
     */
    public function testValidateOpeningHours_givenInvalidSchedule_shouldThrowBadRequestException()
    {
        $this->scheduleValidator->validateOpeningHours('break');
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Opening time can not be provided when the site is indicated as closed.
     */
    public function testValidateOpeningHours_givenIsClosedAndOpenTimeProvide_shouldThrowBadRequestException()
    {
        $data = $this->getDummyScheduleData();
        $data['weeklySchedule'][2]['isClosed'] = true;
        $this->scheduleValidator->validateOpeningHours($data);
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Closing time can not be provided when the site is indicated as closed.
     */
    public function testValidateOpeningHours_givenIsClosedAndCloseTimeProvide_shouldThrowBadRequestException()
    {
        $data = $this->getDummyScheduleData();
        $data['weeklySchedule'][2]['openTime'] = '';
        $data['weeklySchedule'][2]['isClosed'] = true;
        $this->scheduleValidator->validateOpeningHours($data);
    }
}

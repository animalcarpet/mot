<?php

namespace NonWorkingDaysApiTest;

use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Country;
use NonWorkingDaysApi\Constants\CountryCode;
use NonWorkingDaysApi\Provider\HolidaysProvider;
use DvsaEntities\Repository\NonWorkingDayRepository;
use DvsaEntities\Entity\NonWorkingDayCountry;
use DvsaEntities\Repository\NonWorkingDayCountryRepository;
use NonWorkingDaysApi\NonWorkingDaysHelper;
use NonWorkingDaysApi\NonWorkingDaysLookupManager;

/**
 * Class NonWorkingDaysHelperTest.
 */
class NonWorkingDaysHelperTest extends \PHPUnit_Framework_TestCase
{
    /** @var NonWorkingDayRepository */
    private $nonWorkingDayLookupRepository;

    /** @var NonWorkingDayCountryRepository */
    private $nonWorkingDayCountryLookupRepository;

    protected function setUp()
    {
        $this->nonWorkingDayCountryLookupRepository = $this->mockNonWorkingDayCountryLookupRepository();
        $this->nonWorkingDayLookupRepository = $this->mockNonWorkingDayLookupRepository();
    }

    /**
     * @dataProvider workingDaysData
     */
    public function testIsWithinWorkingDaysPeriodInScotland(array $testCase)
    {
        $this->checkIsNotWorkingDaysPeriod($testCase, CountryCode::SCOTLAND);
    }

    public static function workingDaysData()
    {
        return [

            // weekdays, 5 total touching on Saturday
            [['startDate' => '2014/01/06', 'expectedDate' => '2014/01/10', 'nthWorkingDay' => 4]],

            [['startDate' => '2014/01/01', 'expectedDate' => '2014/01/16', 'nthWorkingDay' => 10]],

            // over the weekend, 3 total incl. weekend  => 2 days
            [['startDate' => '2014/01/10', 'expectedDate' => '2014/01/13', 'nthWorkingDay' => 1]],

            // over the weekend commencing on Saturday, 3 total, weekend => 1d
            [['startDate' => '2014/01/11', 'expectedDate' => '2014/01/13', 'nthWorkingDay' => 1]],

            // changing year, 3 total, 2 nwd => 2d
            [['startDate' => '2013/12/31', 'expectedDate' => '2014/01/03', 'nthWorkingDay' => 1]],

            // changing year, 6 total, 2nwd, weekend => 3d
            [['startDate' => '2013/12/31', 'expectedDate' => '2014/01/06', 'nthWorkingDay' => 2]],

            // weekdays, 4 total, 0nwd
            [['startDate' => '2014/01/06', 'expectedDate' => '2014/01/10', 'nthWorkingDay' => 4]],

        ];
    }

    private function checkIsNotWorkingDaysPeriod(array $testCase, $country)
    {
        // assume
        $startDate = $testCase['startDate'];
        $expectedDate = $testCase['expectedDate'];
        $expectedNoOfWorkingDays = $testCase['nthWorkingDay'];
        $holidaysProvider = new HolidaysProvider($this->nonWorkingDayLookupRepository, $this->nonWorkingDayCountryLookupRepository);
        $nonWorkingDaysLookupManager = new NonWorkingDaysLookupManager($holidaysProvider);
        $nonWorkingDaysHelper = new NonWorkingDaysHelper($nonWorkingDaysLookupManager);

        // act
        $answer = $nonWorkingDaysHelper->calculateNthWorkingDayAfter(
            new \DateTime($startDate),
            $expectedNoOfWorkingDays,
            $country
        );

        // assert
        $this->assertEquals($expectedDate, $answer->format('Y/m/d'));
    }

    /**
     * @return array
     */
    private function getHolidays()
    {
        $date2013 = [
            new \DateTime('2013/12/25'),
            new \DateTime('2013/12/26'),
        ];

        $date2014 = [
            new \DateTime('2014/01/01'),
            new \DateTime('2014/04/18'),
            new \DateTime('2014/05/05'),
            new \DateTime('2014/05/26'),
            new \DateTime('2014/08/04'),
            new \DateTime('2014/12/25'),
            new \DateTime('2014/12/26'),
        ];

        $date2014Sct = $date2014;
        $date2014Sct[] = new \DateTime('2014/01/02');

        $fixedTable2013 = [
            CountryCode::SCOTLAND => $date2013,
            CountryCode::ENGLAND => $date2013,
            CountryCode::WALES => $date2013,
        ];

        $fixedTable2014 = [
            CountryCode::SCOTLAND => $date2014Sct,
            CountryCode::ENGLAND => $date2014,
            CountryCode::WALES => $date2014,
        ];

        return [2013 => $fixedTable2013, 2014 => $fixedTable2014];
    }

    /**
     * @return NonWorkingDayCountryRepository
     */
    private function mockNonWorkingDayCountryLookupRepository()
    {
        $nonWorkingDayCountryLookupRepository = XMock::of(NonWorkingDayCountryRepository::class, ['getOneByCode']);
        $nonWorkingDayCountryLookupRepository
            ->expects($this->any())
            ->method('getOneByCode')
            ->willReturnCallback(function ($code) {
                $country = new Country();
                $country->setCode($code);
                $nonWorkingDayCountryLookup = new NonWorkingDayCountry();
                $nonWorkingDayCountryLookup->setCountry($country);

                return $nonWorkingDayCountryLookup;
            });

        return $nonWorkingDayCountryLookupRepository;
    }

    /**
     * @return NonWorkingDayRepository
     */
    private function mockNonWorkingDayLookupRepository()
    {
        $holidays = $this->getHolidays();
        $nonWorkingDayLookupRepository = XMock::of(NonWorkingDayRepository::class, ['findDaysByCountryAndYear']);
        $nonWorkingDayLookupRepository
            ->expects($this->any())
            ->method('findDaysByCountryAndYear')
            ->willReturnCallback(function ($countryCode, $year) use ($holidays) {
                return $holidays[$year][$countryCode];
            });

        return $nonWorkingDayLookupRepository;
    }
}

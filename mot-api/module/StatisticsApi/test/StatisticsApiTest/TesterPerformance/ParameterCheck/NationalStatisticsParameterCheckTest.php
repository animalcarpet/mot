<?php

namespace Dvsa\Mot\Api\StatisticsApiTest\TesterPerformance\ParameterCheck;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\ParameterCheck\StatisticsParameterCheck;
use DvsaCommon\Date\DateUtils;

class NationalStatisticsParameterCheckTest extends \PHPUnit_Framework_TestCase
{
    /** @var StatisticsParameterCheck */
    private $sut;

    protected function setUp()
    {
        $this->sut = new StatisticsParameterCheck();
    }

    /**
     * @dataProvider getInvalidYear
     *
     * @param $year
     * @param $month
     */
    public function testIsValidReturnsFalseIfYearIsInvalid($year, $month)
    {
        $this->validate($year, $month, false);
    }

    public function getInvalidYear()
    {
        $year = $this->getYearInThePast('P1M');
        $month = $this->getMonthInThePast('P1M');

        return [
            [
                $year + 1,
                $month,
            ],
            [
                $year + 30,
                $month,
            ],
            [
                $year - 2,
                $month,
            ],
            [
                '2001',
                $month,

            ],
            [
                'MM',
                $month,
            ],
            [
                $this->getFirstOfThisMonth()->format('Y'),
                $month,
            ],
        ];
    }

    /**
     * @dataProvider getInvalidMonth
     *
     * @param $year
     * @param $month
     */
    public function testIsValidReturnsFalseIfMonthIsInvalid($year, $month)
    {
        $this->validate($year, $month, false);
    }

    public function getInvalidMonth()
    {
        $year = $this->getYearInThePast('P1M');

        return [
            [
                $year,
                0,
            ],
            [
                $year,
                13,
            ],
            [
                $year,
                '2',
            ],
            [
                $year,
                'XI',
            ],
            [
                $this->getCurrentYear(),
                $this->getCurrentMonth(),
            ],
            [
                $this->getYearInTheFuture('P1M'),
                $this->getMonthInTheFuture('P1M'),
            ],
            [
                $this->getYearInThePast('P13M'),
                $this->getMonthInThePast('P13M'),
            ],
        ];
    }

    /**
     * @dataProvider getValidData
     *
     * @param $year
     * @param $month
     */
    public function testIsValidReturnsTrueForValidData($year, $month)
    {
        $this->validate($year, $month, true);
    }

    public function getValidData()
    {
        return [
            [
                $this->getYearInThePast('P1M'),
                $this->getMonthInThePast('P1M'),
            ],
            [
                $this->getYearInThePast('P6M'),
                $this->getMonthInThePast('P6M'),
            ],
            [
                $this->getYearInThePast('P12M'),
                $this->getMonthInThePast('P12M'),
            ],
        ];
    }

    private function validate($year, $month, $expectedResponse)
    {
        $this->assertEquals($expectedResponse, $this->sut->isValid($year, $month));
    }

    /**
     * @return \DateTime
     */
    private function getFirstOfThisMonth()
    {
        return DateUtils::firstOfThisMonth();
    }

    private function getDateInThePast($dateInterval)
    {
        return DateUtils::firstOfThisMonth()->sub(new \DateInterval($dateInterval));
    }

    private function getYearInThePast($dateInterval)
    {
        return (int) $this->getDateInThePast($dateInterval)->format('Y');
    }

    private function getMonthInThePast($dateInterval)
    {
        return (int) $this->getDateInThePast($dateInterval)->format('m');
    }

    private function getDateInTheFuture($dateInterval)
    {
        return DateUtils::firstOfThisMonth()->add(new \DateInterval($dateInterval));
    }

    private function getYearInTheFuture($dateInterval)
    {
        return (int) $this->getDateInTheFuture($dateInterval)->format('Y');
    }

    private function getMonthInTheFuture($dateInterval)
    {
        return (int) $this->getDateInTheFuture($dateInterval)->format('m');
    }

    private function getCurrentMonth()
    {
        return (int) DateUtils::firstOfThisMonth()->format('m');
    }

    private function getCurrentYear()
    {
        return (int) DateUtils::firstOfThisMonth()->format('Y');
    }
}

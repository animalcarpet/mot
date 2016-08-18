<?php
namespace Dvsa\Mot\Api\StatisticsApiTest\TesterPerformance\ParameterCheck;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Common\ParameterCheck\GroupStatisticsParameterCheck;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommonTest\Date\TestDateTimeHolder;

class GroupStatisticsParameterCheckTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Common\ParameterCheck\GroupStatisticsParameterCheck */
    private $sut;

    protected function setUp()
    {
        $this->sut = new \Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Common\ParameterCheck\GroupStatisticsParameterCheck($this->getDateTimeHolder());
    }

    /**
     * @dataProvider getInvalidGroup
     *
     * @param $year
     * @param $month
     * @param $group
     */
    public function testIsValidReturnsFalseIfGroupIsInvalid($year, $month, $group)
    {
        $this->validate($year, $month, $group, false);
    }

    public function getInvalidGroup()
    {
        $year = $this->getYear();
        $month = $this->getMonth();

        return [
            [
                $year,
                $month,
                "C",
            ],
            [
                $year,
                $month,
                "",
            ],
            [
                $year,
                $month,
                " ",
            ],
            [
                $year,
                $month,
                null,
            ],
            [
                $year,
                $month,
                "a",
            ],
            [
                $year,
                $month,
                "b",
            ],
        ];
    }

    /**
     * @dataProvider getInvalidDate
     *
     * @param $year
     * @param $month
     * @param $group
     */
    public function testIsValidReturnsFalseWhenDateIsInvalid($year, $month, $group)
    {
        $this->validate($year, $month, $group, false);
    }

    public function getInvalidDate()
    {
        return [
            [
                "1001",
                "01",
                VehicleClassGroupCode::BIKES
            ],
            [
                1999,
                2,
                VehicleClassGroupCode::CARS_ETC
            ]
        ];
    }

    /**
     * @dataProvider getValidData
     *
     * @param $year
     * @param $month
     * @param $group
     */
    public function testIsValidReturnsTrueForValidData($year, $month, $group)
    {
        $this->validate($year, $month, $group, true);
    }

    public function getValidData()
    {
        $year = $this->getYear();
        $month = $this->getMonth();

        return [
            [
                $year,
                $month,
                VehicleClassGroupCode::BIKES,
            ],
            [
                $year,
                $month,
                VehicleClassGroupCode::CARS_ETC,
            ],
        ];
    }

    private function validate($year, $month, $group, $expectedResponse)
    {
        $this->assertEquals($expectedResponse, $this->sut->isValid($year, $month, $group));
    }

    private function getDateTimeHolder()
    {
        return new TestDateTimeHolder(new \DateTime("2016-06-21"));
    }

    private function getYear()
    {
        return (int)$this->getDateTimeHolder()->getCurrentDate()->sub(new \DateInterval("P1M"))->format("Y");
    }

    private function getMonth()
    {
        return (int)$this->getDateTimeHolder()->getCurrentDate()->sub(new \DateInterval("P1M"))->format("m");
    }
}
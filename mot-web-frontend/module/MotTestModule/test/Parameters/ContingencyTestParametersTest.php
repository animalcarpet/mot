<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModuleTest\Parameters;

use Dvsa\Mot\Frontend\MotTestModule\Parameters\ContingencyTestParameters;
use PHPUnit_Framework_TestCase;

class ContingencyTestParametersTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testLeadingZerosAddedWhenParametersCreatedWithConstructor($value, $expected)
    {
        $data = $this->initDatetimeFields($value);
        $parameters = new ContingencyTestParameters($data);
        foreach (array_keys($data) as $k) {
            $this->assertEquals($expected, $parameters->get($k));
        }
    }

    /**
     * @dataProvider dataProvider
     */
    public function testLeadingZerosAddedWhenParametersCreatedWithFactoryMethod($value, $expected)
    {
        $data = $this->initDatetimeFields($value);
        $parameters = new ContingencyTestParameters();
        $parameters->fromArray($data);
        foreach (array_keys($data) as $k) {
            $this->assertEquals($expected, $parameters->get($k));
        }
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return [
            [null, null],
            ['', ''],
            ['1', '01'],
            ['01', '01'],
            ['11', '11'],
            ['A', 'A'],
        ];
    }

    /**
     * @param mixed $value
     *
     * @return array
     */
    private function initDatetimeFields($value)
    {
        return [
            'performed_at_month' => $value,
            'performed_at_day' => $value,
            'performed_at_minute' => $value,
        ];
    }
}

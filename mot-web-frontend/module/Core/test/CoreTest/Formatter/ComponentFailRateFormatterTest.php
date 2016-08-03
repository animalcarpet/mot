<?php


namespace CoreTest\Formatter;


use Core\Formatting\ComponentFailRateFormatter;
use InvalidArgumentException;

class ComponentFailRateFormatterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProviderVehicleAgeFormatting
     */
    public function testFormatting($failRate, $expectedFailRate, $throwsException = null)
    {
        if($throwsException){
            $this->setExpectedException($throwsException);
        }
        $this->assertSame($expectedFailRate, ComponentFailRateFormatter::format($failRate));
    }

    public function dataProviderVehicleAgeFormatting()
    {
        return [
            [null, null, InvalidArgumentException::class],
            [[], null, InvalidArgumentException::class],
            ["", null, InvalidArgumentException::class],
            [-1, "0"],
            [0, "0"],
            [0.0000, "0"],
            [0.0001, "0"],
            [0.0032789483319717, "0"],
            [0.04, "0"],
            [0.049999, "0"],
            [0.05, "0.1"],
            [0.06, "0.1"],
            [99.444, "99.4"],
            [99.909, "99.9"],
            [99.999, "100"],
            [100, "100"],
            [100.0000, "100"],
            [100.00001, "100"],
            [100.01, "100"],
            [100.1, "100"],
            [120, "100"],
            [1.50, "1.5"],
            [1.56, "1.6"],
            [1.81, "1.8"],
        ];
    }
}
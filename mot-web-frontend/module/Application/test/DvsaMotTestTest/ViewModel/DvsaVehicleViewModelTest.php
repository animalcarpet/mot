<?php

namespace DvsaMotTestTest\ViewModel\MotTestLog;

use Dvsa\Mot\ApiClient\Resource\Item\Colour;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use DvsaMotTest\ViewModel\DvsaVehicleViewModel;
use DvsaMotTestTest\TestHelper\Fixture;

class DvsaVehicleViewModelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getColours
     */
    public function testGetColours($colour, $secondaryColor, $expectedColours)
    {
        $dvsaVehicleViewMOdel = new DvsaVehicleViewModel($this->getVehicleWithColoursSetTo($colour, $secondaryColor));

        $this->assertEquals($expectedColours, $dvsaVehicleViewMOdel->getColours());
    }

    /**
     * @return array
     */
    public function getColours()
    {
        return [
            [
                'primary' => $this->getColour('ABC', 'CBA'),
                'secondary' => $this->getColour('DEF', 'FED'),
                'expected' => 'ABC and DEF',
            ],
            [
                'primary' => $this->getColour('ABC', 'CBA'),
                'secondary' => null,
                'expected' => 'ABC',
            ],
        ];
    }

    /**
     * @param Colour|null $colour
     * @param Colour|null $secondaryColour
     *
     * @return DvsaVehicle
     */
    private function getVehicleWithColoursSetTo(Colour $colour = null, Colour $secondaryColour = null)
    {
        $vehicleDetail = Fixture::getDvsaVehicleTestDataVehicleClass4(true);

        if (!is_null($colour)) {
            $vehicleDetail->colour = $this->initColourStandardClass($colour);
        }

        if (!is_null($secondaryColour)) {
            $vehicleDetail->colourSecondary = $this->initColourStandardClass($secondaryColour);
        }

        return new DvsaVehicle($vehicleDetail);
    }

    /**
     * @param Colour $colour
     *
     * @return \stdClass
     */
    private function initColourStandardClass(Colour $colour)
    {
        $colorDetail = new \stdClass();
        $colorDetail->code = $colour->getCode();
        $colorDetail->name = $colour->getName();

        return $colorDetail;
    }

    /**
     * @param string $name
     * @param string $code
     *
     * @return Colour
     */
    private function getColour($name, $code)
    {
        $colourDetail = new \stdClass();
        $colourDetail->name = $name;
        $colourDetail->code = $code;

        return new Colour($colourDetail);
    }
}

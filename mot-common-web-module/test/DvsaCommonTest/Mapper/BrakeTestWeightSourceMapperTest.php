<?php
namespace ApplicationTest\Mapper;

use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Enum\WeightSourceCode;
use DvsaCommon\Mapper\BrakeTestWeightSourceMapper;

class BrakeTestWeightSourceMapperTest  extends \PHPUnit_Framework_TestCase
{
    /** @var BrakeTestWeightSourceMapper */
    private $sut;

    public function setUp() {
        $this->sut = new BrakeTestWeightSourceMapper();
    }

    /**
     * @dataProvider dataProviderWeightSources
     * @param string $vehicleClass
     * @param string $weightSource
     * @param string $expectedResult
     */
    public function testMapOfficialWeightSourceToVehicleWeightSource($vehicleClass, $weightSource, $expectedResult) {
        if($expectedResult == null) {
            $this->expectException(\InvalidArgumentException::class);
        }

        $result = $this->sut->mapOfficialWeightSourceToVehicleWeightSource($vehicleClass, $weightSource);
        if($expectedResult != null) {
            $this->assertEquals($expectedResult, $result);
        }
    }

    /**
     * @dataProvider dataProviderWeightSources
     * @param string $vehicleClass
     * @param string $weightSource
     * @param string $expectedResult
     */
    public function testIsOfficialWeightSource($vehicleClass, $weightSource, $expectedResult) {
        $this->assertEquals(!empty($expectedResult), $this->sut->isOfficialWeightSource($vehicleClass, $weightSource));
    }

    public function dataProviderWeightSources() {
        return [
            [VehicleClassCode::CLASS_1, WeightSourceCode::VSI, null],
            [VehicleClassCode::CLASS_2, WeightSourceCode::VSI, null],
            [VehicleClassCode::CLASS_3, WeightSourceCode::VSI, WeightSourceCode::ORD_MISW],
            [VehicleClassCode::CLASS_3, WeightSourceCode::PRESENTED, null],
            [VehicleClassCode::CLASS_4, WeightSourceCode::VSI, WeightSourceCode::ORD_MISW],
            [VehicleClassCode::CLASS_4, WeightSourceCode::PRESENTED, null],
            [VehicleClassCode::CLASS_5, WeightSourceCode::VSI, WeightSourceCode::ORD_DGW_MAM],
            [VehicleClassCode::CLASS_5, WeightSourceCode::PRESENTED, null],
            [VehicleClassCode::CLASS_7, WeightSourceCode::VSI, WeightSourceCode::ORD_DGW],
        ];
    }
}
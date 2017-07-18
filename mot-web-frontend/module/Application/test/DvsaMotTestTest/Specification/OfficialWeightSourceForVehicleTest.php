<?php
namespace DvsaMotTestTest\Service;

use Dvsa\Mot\ApiClient\Resource\Item\DvlaVehicle;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use DvsaMotTest\Specification\OfficialWeightSourceForVehicle;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Enum\WeightSourceCode;

class OfficialWeightSourceForVehicleTest extends \PHPUnit_Framework_TestCase
{
    /** @var OfficialWeightSourceForVehicle */
    private $sut;

    public function setUp()
    {
        $this->sut = new OfficialWeightSourceForVehicle();
    }

    /**
     * @dataProvider notDvsaVehicleDP
     * @param $candidate
     */
    public function testNotDvsaVehicle_shouldReturnFalse($candidate)
    {
        $result = $this->sut->isSatisfiedBy($candidate);

        $this->assertFalse($result);
    }

    /**
     * @dataProvider vehicleWeightNotSetDP
     * @param $candidate
     */
    public function testVehicleWeightNotSet_shouldReturnFalse($candidate)
    {
        $result = $this->sut->isSatisfiedBy($candidate);

        $this->assertFalse($result);
    }

    /**
     * @dataProvider VehicleClassNotSetDP
     * @param $candidate
     */
    public function testVehicleClassNotSet_shouldReturnFalse($candidate)
    {
        $result = $this->sut->isSatisfiedBy($candidate);

        $this->assertFalse($result);
    }

    /**
     * @dataProvider VehicleWeightSourceNotSetDP
     * @param $candidate
     */
    public function testVehicleWeightSourceNotSet_shouldReturnFalse($candidate)
    {
        $result = $this->sut->isSatisfiedBy($candidate);

        $this->assertFalse($result);
    }

    /**
     * @dataProvider OfficialWeightSourceDP
     * @param $candidate
     */
    public function testIsWeightSourceAnOfficialOne_shouldReturnTrue($candidate)
    {
        $result = $this->sut->isSatisfiedBy($candidate);

        $this->assertTrue($result);
    }

    /**
     * @dataProvider NotOfficialWeightSourceDP
     * @param $candidate
     */
    public function testIsWeightSourceAnOfficialOne_shouldReturnFalse($candidate)
    {
        $result = $this->sut->isSatisfiedBy($candidate);

        $this->assertFalse($result);
    }

    /**
     * @dataProvider testIfItIgnoresVehiclesOfGroupADP
     * @param $candidate
     */
    public function testIfItIgnoresVehiclesOfGroupA_shouldReturnFalse($candidate)
    {
        $result = $this->sut->isSatisfiedBy($candidate);

        $this->assertFalse($result);
    }


    public function notDvsaVehicleDP()
    {
        return [
          [ null ],
          [ '' ],
          [ 0 ],
          [ new \StdClass() ],
          [ $this->buildVehicle() ],
          [ new OfficialWeightSourceForVehicle() ],
        ];
    }

    public function vehicleWeightNotSetDP()
    {
        return [
            [ $this->buildVehicle() ],
            [ $this->buildVehicle('') ],
            [ $this->buildVehicle(0) ],
            [ $this->buildVehicle('0') ],
            [ $this->buildVehicle(0.0) ],
            [ $this->buildVehicle(false) ],
        ];
    }

    public function VehicleClassNotSetDP()
    {
        return [
            [$this->buildVehicle(1500, null)],
            [$this->buildVehicle(1500, '')],
            [$this->buildVehicle(1500, 0)],
            [$this->buildVehicle(1500, 0.0)],
            [$this->buildVehicle(1500, '0')],
        ];
    }

    public function VehicleWeightSourceNotSetDP()
    {
        return [
            [ $this->buildVehicle(1500, VehicleClassCode::CLASS_4 , null) ],
            [ $this->buildVehicle(1500, VehicleClassCode::CLASS_4 , '') ],
            [ $this->buildVehicle(1500, VehicleClassCode::CLASS_4 , 0 ) ],
            [ $this->buildVehicle(1500, VehicleClassCode::CLASS_4 , false ) ],
            [ $this->buildVehicle(1500, VehicleClassCode::CLASS_4 , '0') ],
            [ $this->buildVehicle(1500, VehicleClassCode::CLASS_4 , 0.0) ],
        ];
    }

    public function OfficialWeightSourceDP()
    {
        return [
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_3, WeightSourceCode::MISW) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_3, WeightSourceCode::VSI) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_3, WeightSourceCode::ORD_MISW) ],

          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_4, WeightSourceCode::MISW) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_4, WeightSourceCode::VSI) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_4, WeightSourceCode::ORD_MISW) ],

          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_5, WeightSourceCode::DGW ) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_5, WeightSourceCode::DGW_MAM) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_5, WeightSourceCode::VSI) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_5, WeightSourceCode::ORD_DGW_MAM) ],

          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_7, WeightSourceCode::DGW) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_7, WeightSourceCode::VSI) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_7, WeightSourceCode::ORD_DGW) ],
        ];
    }

    public function NotOfficialWeightSourceDP()
    {
        return [
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_3, WeightSourceCode::CALCULATED) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_3, WeightSourceCode::DGW) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_3, WeightSourceCode::DGW_MAM) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_3, WeightSourceCode::MAM) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_3, WeightSourceCode::MOTORCYCLE) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_3, WeightSourceCode::NOT_APPLICABLE) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_3, WeightSourceCode::PRESENTED) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_3, WeightSourceCode::UNKNOWN) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_3, WeightSourceCode::UNLADEN) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_3, WeightSourceCode::ORD_DGW) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_3, WeightSourceCode::ORD_DGW_MAM) ],

          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_4, WeightSourceCode::CALCULATED) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_4, WeightSourceCode::DGW) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_4, WeightSourceCode::DGW_MAM) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_4, WeightSourceCode::MAM) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_4, WeightSourceCode::MOTORCYCLE) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_4, WeightSourceCode::NOT_APPLICABLE) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_4, WeightSourceCode::PRESENTED) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_4, WeightSourceCode::UNKNOWN) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_4, WeightSourceCode::UNLADEN) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_4, WeightSourceCode::ORD_DGW) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_4, WeightSourceCode::ORD_DGW_MAM) ],

          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_5, WeightSourceCode::CALCULATED) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_5, WeightSourceCode::MAM) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_5, WeightSourceCode::MOTORCYCLE) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_5, WeightSourceCode::NOT_APPLICABLE) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_5, WeightSourceCode::PRESENTED) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_5, WeightSourceCode::UNKNOWN) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_5, WeightSourceCode::UNLADEN) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_5, WeightSourceCode::ORD_DGW) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_5, WeightSourceCode::MISW) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_5, WeightSourceCode::ORD_MISW) ],

          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_7, WeightSourceCode::MOTORCYCLE) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_7, WeightSourceCode::CALCULATED) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_7, WeightSourceCode::NOT_APPLICABLE) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_7, WeightSourceCode::MAM) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_7, WeightSourceCode::PRESENTED) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_7, WeightSourceCode::UNKNOWN) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_7, WeightSourceCode::UNLADEN) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_7, WeightSourceCode::MISW) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_7, WeightSourceCode::ORD_MISW) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_7, WeightSourceCode::ORD_DGW_MAM) ],
          [ $this->buildVehicle(1500, VehicleClassCode::CLASS_7, WeightSourceCode::DGW_MAM) ],
        ];
    }

    public function testIfItIgnoresVehiclesOfGroupADP()
    {
        return [
            [ $this->buildVehicle(1500, VehicleClassCode::CLASS_1, WeightSourceCode::CALCULATED) ],
            [ $this->buildVehicle(1500, VehicleClassCode::CLASS_2, WeightSourceCode::CALCULATED) ],
            [ $this->buildVehicle(1500, VehicleClassCode::CLASS_1, WeightSourceCode::DGW) ],
            [ $this->buildVehicle(1500, VehicleClassCode::CLASS_2, WeightSourceCode::DGW) ],
            [ $this->buildVehicle(1500, VehicleClassCode::CLASS_1, WeightSourceCode::DGW_MAM) ],
            [ $this->buildVehicle(1500, VehicleClassCode::CLASS_2, WeightSourceCode::DGW_MAM) ],
            [ $this->buildVehicle(1500, VehicleClassCode::CLASS_1, WeightSourceCode::MAM) ],
            [ $this->buildVehicle(1500, VehicleClassCode::CLASS_2, WeightSourceCode::MAM) ],
            [ $this->buildVehicle(1500, VehicleClassCode::CLASS_1, WeightSourceCode::MISW) ],
            [ $this->buildVehicle(1500, VehicleClassCode::CLASS_2, WeightSourceCode::MISW) ],
            [ $this->buildVehicle(1500, VehicleClassCode::CLASS_1, WeightSourceCode::MOTORCYCLE) ],
            [ $this->buildVehicle(1500, VehicleClassCode::CLASS_2, WeightSourceCode::MOTORCYCLE) ],
            [ $this->buildVehicle(1500, VehicleClassCode::CLASS_1, WeightSourceCode::NOT_APPLICABLE) ],
            [ $this->buildVehicle(1500, VehicleClassCode::CLASS_2, WeightSourceCode::NOT_APPLICABLE) ],
            [ $this->buildVehicle(1500, VehicleClassCode::CLASS_1, WeightSourceCode::ORD_DGW) ],
            [ $this->buildVehicle(1500, VehicleClassCode::CLASS_2, WeightSourceCode::ORD_DGW) ],
            [ $this->buildVehicle(1500, VehicleClassCode::CLASS_1, WeightSourceCode::ORD_DGW_MAM) ],
            [ $this->buildVehicle(1500, VehicleClassCode::CLASS_2, WeightSourceCode::ORD_DGW_MAM) ],
            [ $this->buildVehicle(1500, VehicleClassCode::CLASS_1, WeightSourceCode::ORD_MISW) ],
            [ $this->buildVehicle(1500, VehicleClassCode::CLASS_2, WeightSourceCode::ORD_MISW) ],
            [ $this->buildVehicle(1500, VehicleClassCode::CLASS_1, WeightSourceCode::PRESENTED) ],
            [ $this->buildVehicle(1500, VehicleClassCode::CLASS_2, WeightSourceCode::PRESENTED) ],
            [ $this->buildVehicle(1500, VehicleClassCode::CLASS_1, WeightSourceCode::UNKNOWN) ],
            [ $this->buildVehicle(1500, VehicleClassCode::CLASS_2, WeightSourceCode::UNKNOWN) ],
            [ $this->buildVehicle(1500, VehicleClassCode::CLASS_1, WeightSourceCode::UNLADEN) ],
            [ $this->buildVehicle(1500, VehicleClassCode::CLASS_2, WeightSourceCode::UNLADEN) ],
            [ $this->buildVehicle(1500, VehicleClassCode::CLASS_1, WeightSourceCode::VSI) ],
            [ $this->buildVehicle(1500, VehicleClassCode::CLASS_2, WeightSourceCode::VSI) ],
        ];
    }


    /**
     * @param null $weight
     * @param null $vehicleClassCode
     * @param null $vehicleWeightSourceCode
     *
     * @return DvsaVehicle
     */
    private function buildVehicle(
        $weight = null,
        $vehicleClassCode = null,
        $vehicleWeightSourceCode = null
    )
    {
        $data = $this->setUpCommonData($weight, $vehicleClassCode, $vehicleWeightSourceCode);

        return new DvsaVehicle($data);
    }

    /**
     * @param $weight
     * @param $vehicleClassCode
     * @param $vehicleWeightSourceCode
     *
     * @return \StdClass
     */
    private function setUpCommonData(
        $weight,
        $vehicleClassCode,
        $vehicleWeightSourceCode
    )
    {
        $data = new \StdClass();

        $data->fuelType = null;
        $data->colour = null;
        $data->colourSecondary = null;
        $data->make = new \StdClass();
        $data->model = new \StdClass();
        $data->weight = $weight;

        $class = new \StdClass();
        $class->code = $vehicleClassCode;
        $class->name = $vehicleClassCode;

        $weightCode = new \StdClass();
        $weightCode->code = $vehicleWeightSourceCode;
        $weightCode->name = $vehicleWeightSourceCode;

        $data->vehicleClass = $class;
        $data->weightSource = $weightCode;

        return $data;
    }

}
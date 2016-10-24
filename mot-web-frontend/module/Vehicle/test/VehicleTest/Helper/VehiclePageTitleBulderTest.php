<?php
namespace VehicleTest\Helper;

use Core\ViewModel\Header\HeaderTertiaryList;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use stdClass;
use DvsaCommon\Enum\VehicleClassCode;
use Vehicle\Helper\VehiclePageTitleBuilder;

class VehiclePageTitleBulderTest extends \PHPUnit_Framework_TestCase
{
    const MAKE_NAME = 'RENAULT';
    const MODEL_NAME = 'CLIO';
    const VIN = '1M8GDM9AXKP042788';
    const REGISTRATION = 'FNZ6110';
    const PAGE_SUBTITLE = 'Vehicle';

    /**
     * @dataProvider dataProviderTestUrlGeneration
     * @param DvsaVehicle $dvlaVehicle
     * @param $title
     * @param $secondTitle
     * @param $thirdTitle
     */
    public function testPageTile(DvsaVehicle $dvlaVehicle, $title, $secondTitle, $thirdTitle)
    {
        $helper = new VehiclePageTitleBuilder();
        $helper->setVehicle($dvlaVehicle);

        $primaryTitle = $helper->getPageTitle();
        $secondaryTitle = $helper->getPageSecondaryTitle();
        $tertiaryTitle = $helper->getPageTertiaryTitle();

        $this->assertInstanceOf(HeaderTertiaryList::class, $tertiaryTitle);
        $this->assertEquals($title, $primaryTitle);
        $this->assertEquals($secondTitle, $secondaryTitle);
        $this->assertEquals($thirdTitle, $tertiaryTitle);
    }

    public function dataProviderTestUrlGeneration()
    {
        $vehicleWithModel = $this->getVehicle();
        $vehicleWithoutModel = $this->getVehicleWithoutModel();

        $tertiaryTitle = new HeaderTertiaryList();
        $tertiaryTitle->addElement(self::REGISTRATION);
        $tertiaryTitle->addElement(self::VIN);

        return [
            [new DvsaVehicle($vehicleWithModel), $vehicleWithModel->make->name . ', ' . $vehicleWithModel->model->name, self::PAGE_SUBTITLE, $tertiaryTitle],
            [new DvsaVehicle($vehicleWithoutModel), $vehicleWithModel->make->name, self::PAGE_SUBTITLE, $tertiaryTitle],
        ];
    }

    private function getVehicle()
    {
        return json_decode(json_encode([
            'id' => 1,
            'amendedOn' => '2016-09-07',
            'registration' => self::REGISTRATION,
            'vin' => self::VIN,
            'emptyVrmReason' => NULL,
            'emptyVinReason' => NULL,
            'make' => [
                'id' => 5,
                'name' => self::MAKE_NAME,
            ],
            'model' => [
                'id' => 6,
                'name' => self::MODEL_NAME,
            ],
            'colour' => [
                'code' => 'L',
                'name' => 'Grey',
            ],
            'colourSecondary' => [
                'code' => 'W',
                'name' => 'Not Stated',
            ],
            'countryOfRegistration' => 'GB, UK, ENG, CYM, SCO (UK) - Great Britain',
            'vehicleClass' => ['code' => VehicleClassCode::CLASS_4, 'name' => '4'],
            'fuelType' => [
                'code' => 'PE',
                'name' => 'Petrol',
            ],
            'bodyType' => '2 Door Saloon',
            'cylinderCapacity' => 1700,
            'transmissionType' => 'Automatic',
            'firstRegistrationDate' => '2004-01-02',
            'firstUsedDate' => '2004-01-02',
            'manufactureDate' => '2004-01-02',
            'isNewAtFirstReg' => false,
            'weight' => 12467,
            'version' => 2,
        ]));
    }

    private function getVehicleWithoutModel()
    {
        $vehicle = $this->getVehicle();
        $model = new stdClass();
        $model->id = null;
        $model->name = null;
        $vehicle->model = $model;

        return $vehicle;
    }
}
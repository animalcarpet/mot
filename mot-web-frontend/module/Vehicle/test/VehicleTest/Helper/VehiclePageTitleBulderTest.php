<?php


namespace VehicleTest\Helper;


use Core\ViewModel\Header\HeaderTertiaryList;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Vehicle\Helper\VehiclePageTitleBulder;

class VehiclePageTitleBulderTest extends \PHPUnit_Framework_TestCase
{
    const MAKE_NAME = 'RENAULT';
    const MODEL_NAME = 'CLIO';
    const VIN = '1M8GDM9AXKP042788';
    const REGISTRATION = 'FNZ6110';
    const PAGE_SUBTITLE = 'Vehicle';

    /**
     * @dataProvider dataProviderTestUrlGeneration
     */
    public function testPageTile(DvsaVehicle $dvlaVehicle, $title, $secondTitle, $thirdTitle)
    {
        $helper = new VehiclePageTitleBulder();
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
        $vehicleWithoutModel = $this->getVehicle();
        $vehicleWithoutModel->model = null;

        $tertiaryTitle = new HeaderTertiaryList();
        $tertiaryTitle->addRow(self::REGISTRATION);
        $tertiaryTitle->addRow(self::VIN);

        return [
            [new DvsaVehicle($vehicleWithModel), $vehicleWithModel->make . ', ' . $vehicleWithModel->model, self::PAGE_SUBTITLE, $tertiaryTitle],
            [new DvsaVehicle($vehicleWithoutModel), $vehicleWithModel->make, self::PAGE_SUBTITLE, $tertiaryTitle],
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
            'make' => self::MAKE_NAME,
            'model' => self::MODEL_NAME,
            'colour' => 'Grey',
            'colourSecondary' => 'Not Stated',
            'countryOfRegistration' => 'GB, UK, ENG, CYM, SCO (UK) - Great Britain',
            'fuelType' => 'Petrol',
            'vehicleClass' => '4',
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
}
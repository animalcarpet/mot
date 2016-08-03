<?php

namespace DvsaMotTestTest\Form;

use DvsaMotTest\NewVehicle\Form\CreateVehicleStepOneForm;

class CreateVehicleStepOneFormTest extends \PHPUnit_Framework_TestCase
{
    /** @var  CreateVehicleStepOneForm */
    private $sut;

    public function setUp()
    {
        $this->sut = new CreateVehicleStepOneForm(['vehicleData' => $this->getVehicleData()]);
    }

    private function getVehicleData()
    {
        return [
            'make' => null,
            'colour' => null,
            'secondaryColour' => null,
            'fuelType' => null,
            'countryOfRegistration' => null,
            'transmissionType' => null,
            'vehicleClass' => null,
            'model' => null,
            'emptyVrmReasons' => null,
            'emptyVinReasons' => null,
        ];
    }

    /**
     * @dataProvider dataProviderTestIfRegistrationIsBeingCorrected
     */
    public function testIfRegistrationIsBeingCorrected($inputVrm, $fixedVrm)
    {
        $this->sut->setData([
            'vehicleForm' => [
                'registrationNumber' => $inputVrm
            ]
        ]);

        $this->assertEquals($fixedVrm, $this->sut->getRegistrationNumber()->getValue());
    }

    public function dataProviderTestIfRegistrationIsBeingCorrected()
    {
        return [
            ['600 baa', '600BAA'],
            ["123\tabc", '123ABC']
        ];
    }
}
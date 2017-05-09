<?php

namespace VehicleTest\UpdateVehicleProperty\Form;

use DvsaCommon\Enum\FuelTypeCode;
use DvsaCommon\Model\FuelTypeAndCylinderCapacity;
use Vehicle\UpdateVehicleProperty\Form\InputFilter\UpdateEngineInputFilter;
use Vehicle\UpdateVehicleProperty\Form\UpdateEngineForm;
use Zend\Form\Element\Select;
use Zend\Form\Element\Text;

class UpdateEngineFormTest extends \PHPUnit_Framework_TestCase
{
    /** @var UpdateEngineForm */
    private $updateEngineForm;
    private $fuelTypes;

    public function setUp()
    {
        foreach (FuelTypeCode::getAll() as $fuelType) {
            $this->fuelTypes[$fuelType] = 'asdasd';
        }

        $updateEngineInputFilter = new UpdateEngineInputFilter($this->fuelTypes);
        $this->updateEngineForm = new UpdateEngineForm($this->fuelTypes);

        $this->updateEngineForm
            ->setEngineCapacityValidator($updateEngineInputFilter->getEngineCapacityValidator())
            ->setInputFilter($updateEngineInputFilter->getInputFilter());
    }

    public function testForm()
    {
        $capacity = $this->updateEngineForm->getEngineCapacityElement();
        $type = $this->updateEngineForm->getEngineTypeElement();

        $this->assertInstanceOf(Text::class, $capacity);
        $this->assertInstanceOf(Select::class, $type);

        $this->assertEquals('1-4', $capacity->getAttribute('inputModifier'));
        $this->assertTrue($capacity->getAttribute('group'));
        $this->assertNotEmpty($type->getAttribute('data-target'));
        $this->assertEquals($capacity->getAttribute('maxLength'), 5);
        $this->assertEquals(
            FuelTypeAndCylinderCapacity::getAllFuelTypeCodesWithCompulsoryCylinderCapacityAsString(),
            $type->getAttribute('data-target-value')
        );
        $this->assertNotEmpty($capacity->getAttribute('help'));
    }

    /**
     * @dataProvider dataProviderTestFormValidation
     */
    public function testFormValidation($data, $valid)
    {
        $this->updateEngineForm->setData($data);
        $this->assertEquals($valid, $this->updateEngineForm->isValid());
    }

    private function getInvalidInputs()
    {
        return [
                [UpdateEngineForm::FIELD_FUEL_TYPE => null, UpdateEngineForm::FIELD_CAPACITY => null],
                [UpdateEngineForm::FIELD_FUEL_TYPE => FuelTypeCode::DIESEL, UpdateEngineForm::FIELD_CAPACITY => null],
                [UpdateEngineForm::FIELD_FUEL_TYPE => FuelTypeCode::DIESEL, UpdateEngineForm::FIELD_CAPACITY => -1],
                [UpdateEngineForm::FIELD_FUEL_TYPE => FuelTypeCode::DIESEL, UpdateEngineForm::FIELD_CAPACITY => ''],
                [UpdateEngineForm::FIELD_FUEL_TYPE => FuelTypeCode::DIESEL, UpdateEngineForm::FIELD_CAPACITY => '00'],
                [UpdateEngineForm::FIELD_FUEL_TYPE => FuelTypeCode::DIESEL, UpdateEngineForm::FIELD_CAPACITY => ' 0000 '],
                [UpdateEngineForm::FIELD_FUEL_TYPE => FuelTypeCode::DIESEL, UpdateEngineForm::FIELD_CAPACITY => '      '],
                [UpdateEngineForm::FIELD_FUEL_TYPE => FuelTypeCode::DIESEL, UpdateEngineForm::FIELD_CAPACITY => '      0000    '],
                [UpdateEngineForm::FIELD_FUEL_TYPE => FuelTypeCode::DIESEL, UpdateEngineForm::FIELD_CAPACITY => '11.2'],
                [UpdateEngineForm::FIELD_FUEL_TYPE => FuelTypeCode::DIESEL, UpdateEngineForm::FIELD_CAPACITY => '1400.2'],
                [UpdateEngineForm::FIELD_FUEL_TYPE => FuelTypeCode::DIESEL, UpdateEngineForm::FIELD_CAPACITY => '22,2'],
                [UpdateEngineForm::FIELD_FUEL_TYPE => FuelTypeCode::DIESEL, UpdateEngineForm::FIELD_CAPACITY => 'asda'],
                [UpdateEngineForm::FIELD_FUEL_TYPE => FuelTypeCode::DIESEL, UpdateEngineForm::FIELD_CAPACITY => '1e2'],
                [UpdateEngineForm::FIELD_FUEL_TYPE => FuelTypeCode::DIESEL, UpdateEngineForm::FIELD_CAPACITY => '123123'],
                [UpdateEngineForm::FIELD_FUEL_TYPE => FuelTypeCode::DIESEL, UpdateEngineForm::FIELD_CAPACITY => '0x22'],
                [UpdateEngineForm::FIELD_FUEL_TYPE => FuelTypeCode::DIESEL, UpdateEngineForm::FIELD_CAPACITY => '1e2'],
                [UpdateEngineForm::FIELD_FUEL_TYPE => FuelTypeCode::DIESEL, UpdateEngineForm::FIELD_CAPACITY => '100000'],
                [UpdateEngineForm::FIELD_FUEL_TYPE => FuelTypeCode::DIESEL, UpdateEngineForm::FIELD_CAPACITY => '100001'],
                [UpdateEngineForm::FIELD_FUEL_TYPE => FuelTypeCode::DIESEL, UpdateEngineForm::FIELD_CAPACITY => '999999'],
                [UpdateEngineForm::FIELD_FUEL_TYPE => FuelTypeCode::DIESEL, UpdateEngineForm::FIELD_CAPACITY => '-922'],
                [UpdateEngineForm::FIELD_FUEL_TYPE => FuelTypeCode::DIESEL, UpdateEngineForm::FIELD_CAPACITY => '102 cc'],
                [UpdateEngineForm::FIELD_FUEL_TYPE => FuelTypeCode::STEAM, UpdateEngineForm::FIELD_CAPACITY => '1'],
                [UpdateEngineForm::FIELD_FUEL_TYPE => FuelTypeCode::STEAM, UpdateEngineForm::FIELD_CAPACITY => '1800'],
                [UpdateEngineForm::FIELD_FUEL_TYPE => 99, UpdateEngineForm::FIELD_CAPACITY => '1800'],
                [UpdateEngineForm::FIELD_FUEL_TYPE => '', UpdateEngineForm::FIELD_CAPACITY => '1800'],
        ];
    }

    private function getValidInputs()
    {
        return [
                [UpdateEngineForm::FIELD_FUEL_TYPE => FuelTypeCode::FUEL_CELLS, UpdateEngineForm::FIELD_CAPACITY => ''],
                [UpdateEngineForm::FIELD_FUEL_TYPE => FuelTypeCode::STEAM, UpdateEngineForm::FIELD_CAPACITY => ''],
                [UpdateEngineForm::FIELD_FUEL_TYPE => FuelTypeCode::ELECTRIC, UpdateEngineForm::FIELD_CAPACITY => ''],
                [UpdateEngineForm::FIELD_FUEL_TYPE => FuelTypeCode::PETROL, UpdateEngineForm::FIELD_CAPACITY => '0'],
                [UpdateEngineForm::FIELD_FUEL_TYPE => FuelTypeCode::DIESEL, UpdateEngineForm::FIELD_CAPACITY => '9999'],
                [UpdateEngineForm::FIELD_FUEL_TYPE => FuelTypeCode::DIESEL, UpdateEngineForm::FIELD_CAPACITY => '10000'],
                [UpdateEngineForm::FIELD_FUEL_TYPE => FuelTypeCode::DIESEL, UpdateEngineForm::FIELD_CAPACITY => '99999'],
                [UpdateEngineForm::FIELD_FUEL_TYPE => FuelTypeCode::CNG, UpdateEngineForm::FIELD_CAPACITY => '  0009999   '],
                [UpdateEngineForm::FIELD_FUEL_TYPE => FuelTypeCode::LPG, UpdateEngineForm::FIELD_CAPACITY => '1800'],
                [UpdateEngineForm::FIELD_FUEL_TYPE => FuelTypeCode::ELECTRIC_DIESEL, UpdateEngineForm::FIELD_CAPACITY => '1'],
                [UpdateEngineForm::FIELD_FUEL_TYPE => FuelTypeCode::DIESEL, UpdateEngineForm::FIELD_CAPACITY => '1999'],
                [UpdateEngineForm::FIELD_FUEL_TYPE => FuelTypeCode::LPG, UpdateEngineForm::FIELD_CAPACITY => ' 1800 '],
                [UpdateEngineForm::FIELD_FUEL_TYPE => FuelTypeCode::LPG, UpdateEngineForm::FIELD_CAPACITY => ' 1800'],
                [UpdateEngineForm::FIELD_FUEL_TYPE => FuelTypeCode::LPG, UpdateEngineForm::FIELD_CAPACITY => '01800 '],
        ];
    }

    public function dataProviderTestFormValidation()
    {
        $out = [];
        foreach ($this->getInvalidInputs() as $invalidInput) {
            $out [] = [
                $invalidInput,
                false,
            ];
        }

        foreach ($this->getValidInputs() as $validInput) {
            $out[] = [
                $validInput,
                true,
            ];
        }

        return $out;
    }
}

<?php

namespace VehicleTest\CreateVehicle\Form;

use DvsaCommon\Enum\FuelTypeCode;
use Vehicle\CreateVehicle\Form\EngineForm;

class EngineFormTest extends \PHPUnit_Framework_TestCase
{
    const ERROR_FUEL_TYPE_REQUIRED = 'Select a fuel type';
    const ERROR_CAPACITY_REQUIRED = 'Enter a value';
    const ERROR_CAPACITY_MUST_BE_NUMERIC = 'Can only contain numbers';
    const ERROR_CAPACITY_MUST_BE_SHORTER_THAN_SIX_DIGITS = 'Must be shorter than 6 digits';

    public function setUp()
    {
        parent::setUp();
    }

    public function test_formShouldNotShowError_when_validDataEntered()
    {
        $form = $this->buildForm($this->withFuelTypes(), null);
        $form->setData($this->setDataValues(FuelTypeCode::PETROL, '1400'));

        $this->assertTrue($form->isValid());
        $this->assertCount(0, $form->getMessages());
    }

    /**
     * @dataProvider validDataDataProvider
     */
    public function test_formShouldNotShowError_when_validDataEnteredAllFuelTypes($fuelType, $cylinderCapacity)
    {
        $form = $this->buildForm($this->withFuelTypes(), null);

        $form->setData($this->setDataValues($fuelType, $cylinderCapacity));

        $this->assertTrue($form->isValid());
        $this->assertCount(0, $form->getMessages());
    }

    public function test_formShouldShowError_when_fuelTypeIsNotSelected()
    {
        $form = $this->buildForm($this->withFuelTypes(), null);
        $form->setData($this->setDataValues('', ''));

        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->getMessages());
        $this->assertSame(self::ERROR_FUEL_TYPE_REQUIRED, $form->getMessages()[EngineForm::FIELD_FUEL_TYPE][0]);
    }

    public function test_formShouldShowError_when_cylinderCapacityIsEmpty()
    {
        $form = $this->buildForm($this->withFuelTypes(), null);
        $form->setData($this->setDataValues(FuelTypeCode::PETROL, ''));

        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->getMessages());
        $this->assertSame(self::ERROR_CAPACITY_REQUIRED, $form->getMessages()[EngineForm::FIELD_CAPACITY][0]);
    }

    public function test_formShouldShowError_when_cylinderCapacityValueIsLongerThanFiveDigits()
    {
        $form = $this->buildForm($this->withFuelTypes(), null);
        $form->setData($this->setDataValues(FuelTypeCode::PETROL, '123456'));

        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->getMessages());
        $this->assertSame(self::ERROR_CAPACITY_MUST_BE_SHORTER_THAN_SIX_DIGITS, $form->getMessages()[EngineForm::FIELD_CAPACITY][0]);
    }

    /**
     * @dataProvider nonNumericInputsDataProvider
     */
    public function test_formShouldShowError_when_cylinderCapacityContainsNonNumericCharacters($cylinderCapacity)
    {
        $form = $this->buildForm($this->withFuelTypes(), null);

        $form->setData($this->setDataValues(FuelTypeCode::PETROL, $cylinderCapacity));

        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->getMessages());
        $this->assertSame(self::ERROR_CAPACITY_MUST_BE_NUMERIC, $form->getMessages()[EngineForm::FIELD_CAPACITY][0]);
    }

    public function nonNumericInputsDataProvider()
    {
        return [
            ['A'],
            ['a'],
            ['1400 cc'],
            ['aaaB'],
            ['£'],
            ['$'],
            ['-200'],
            ['1.2'],
        ];
    }

    public function validDataDataProvider()
    {
        return [
            [FuelTypeCode::PETROL, '1400'],
            [FuelTypeCode::DIESEL, '1400'],
            [FuelTypeCode::ELECTRIC, ''],
            [FuelTypeCode::CNG, '1400'],
            [FuelTypeCode::ELECTRIC_DIESEL, '1400'],
            [FuelTypeCode::FUEL_CELLS, ''],
            [FuelTypeCode::GAS, '1400'],
            [FuelTypeCode::GAS_BI_FUEL, '1400'],
            [FuelTypeCode::GAS_DIESEL, '1400'],
            [FuelTypeCode::HYBRID_ELECTRIC_CLEAN, '1400'],
            [FuelTypeCode::LNG, '1400'],
            [FuelTypeCode::LPG, '1400'],
            [FuelTypeCode::STEAM, ''],
            [FuelTypeCode::OTHER, '1400'],
        ];
    }

    private function setDataValues($fuelType, $capacity)
    {
        return [
            EngineForm::FIELD_FUEL_TYPE => $fuelType,
            EngineForm::FIELD_CAPACITY => $capacity,
        ];
    }

    private function buildForm($fuelTypes, $engineData)
    {
        return new EngineForm(
            $fuelTypes,
            $engineData
        );
    }

    private function withFuelTypes()
    {
        return [
            FuelTypeCode::PETROL => 'Petrol',
            FuelTypeCode::DIESEL => 'Diesel',
            FuelTypeCode::ELECTRIC => 'Electric',
            FuelTypeCode::CNG => 'CNG',
            FuelTypeCode::ELECTRIC_DIESEL => 'Electric Diesel',
            FuelTypeCode::FUEL_CELLS => 'Fuel Cells',
            FuelTypeCode::GAS => 'Gas',
            FuelTypeCode::GAS_BI_FUEL => 'Gas Bi-Fuel',
            FuelTypeCode::GAS_DIESEL => 'Gas Diesel',
            FuelTypeCode::HYBRID_ELECTRIC_CLEAN => 'Hybrid Electric (Clean)',
            FuelTypeCode::LNG => 'LNG',
            FuelTypeCode::LPG => 'LPG',
            FuelTypeCode::STEAM => 'Steam',
            FuelTypeCode::OTHER => 'Other',
        ];
    }
}

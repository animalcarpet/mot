<?php

namespace Vehicle\CreateVehicle\Form;

class RegistrationAndVinFormTest extends \PHPUnit_Framework_TestCase
{
    const ERROR_EMPTY_REGISTRATION = 'Enter the registration or select ‘I can’t provide the registration mark’';
    const ERROR_EMPTY_VIN = 'Enter the VIN or select ‘I can’t provide the VIN’';
    const ERROR_MUST_CONTAIN_ONE_ALPHANUMERIC = 'Must contain at least one number or letter';
    const ERROR_NON_ALLOWED_VALUES = 'Can only contain numbers, letters, spaces, hyphens and full stops';
    const ERROR_REGISTRATION_GREATER_THAN_THIRTEEN_CHARACTERS = 'Must be shorter than 14 characters';
    const ERROR_NOTHING_ENTERED = 'You must enter the registration mark or the VIN';
    const ERROR_CANNOT_SELECT_AND_ENTER_REGISTRATION = 'Either enter the registration or select ‘I can’t provide the registration mark’';
    const ERROR_CANNOT_SELECT_AND_ENTER_VIN = 'Either enter the VIN or select ‘I can’t provide the VIN’';
    const ERROR_VIN_GREATER_THAN_20_CHARACTERS = 'Must be shorter than 21 characters';

    public function setUp()
    {
        parent::setUp();
    }

    public function testIsValid_validData_shouldNotDisplayErrors()
    {
        $form = $this->buildForm();
        $form->setData($this->setDataValues('TES T123', false, 'TES TVIN 1234', false));
        $this->assertTrue($form->isValid());
        $this->assertCount(0, $form->getMessages());
    }

    public function testIsInValid_emptyRegistrationFieldAndVIN_shouldDisplayErrors()
    {
        $form = $this->buildForm();
        $form->setData($this->setDataValues('', false, '', false));
        $this->assertFalse($form->isValid());
        $this->assertCount(2, $form->getMessages());
        $this->assertSame(self::ERROR_EMPTY_REGISTRATION, $form->getMessages()['reg-input'][0]);
        $this->assertSame(self::ERROR_EMPTY_VIN, $form->getMessages()['vin-input'][0]);
    }

    public function testRegistrationFieldIsInValid_mustContainAtLeastOneLetterOrNumber_shouldDisplayErrors()
    {
        $form = $this->buildForm();
        $form->setData($this->setDataValues('---', false, 'TES TVIN 1234', false));
        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->getMessages());
        $this->assertSame(self::ERROR_MUST_CONTAIN_ONE_ALPHANUMERIC, $form->getMessages()['reg-input'][0]);
    }

    public function testRegistrationFieldIsInValid_mustOnlyContainNumbersLettersHyphensFullStops_shouldDisplayErrors()
    {
        $form = $this->buildForm();
        $form->setData($this->setDataValues('---§§§11', false, 'TES TVIN 1234', false));
        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->getMessages());
        $this->assertSame(self::ERROR_NON_ALLOWED_VALUES, $form->getMessages()['reg-input'][0]);
    }

    public function testRegistrationFieldIsInValid_greaterThan13Characters_shouldDisplayErrors()
    {
        $form = $this->buildForm();
        $form->setData($this->setDataValues('thirteen123456', false, 'TES TVIN 1234', false));
        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->getMessages());
        $this->assertSame(self::ERROR_REGISTRATION_GREATER_THAN_THIRTEEN_CHARACTERS, $form->getMessages()['reg-input'][0]);
    }

    public function testIsInValid_emptyRegistrationAndVINSelectedBothCheckboxes_shouldDisplayErrors()
    {
        $form = $this->buildForm('', true, '', true);
        $form->setData($this->setDataValues('', true, '', true));
        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->getErrorMessages());
        $this->assertSame(self::ERROR_NOTHING_ENTERED, $form->getErrorMessages()[0]);
    }

    public function testVinFieldIsInValid_emptyVIN_shouldDisplayErrors()
    {
        $form = $this->buildForm();
        $form->setData($this->setDataValues('TES T123', false, '', false));
        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->getMessages());
        $this->assertSame(self::ERROR_EMPTY_VIN, $form->getMessages()['vin-input'][0]);
    }

    public function testIsValid_emptyVINSelectedCheckbox_shouldNotDisplayErrors()
    {
        $form = $this->buildForm('TES T123', false, '', true);
        $form->setData($this->setDataValues('TES T123', false, '', true));
        $this->assertTrue($form->isValid());
        $this->assertCount(0, $form->getMessages());
    }

    public function testIsValid_emptyRegistrationFieldSelectedCheckbox_shouldNotDisplayErrors()
    {
        $form = $this->buildForm('', true, 'TES T123', false);
        $form->setData($this->setDataValues('', true, 'TES T123', false));
        $this->assertTrue($form->isValid());
        $this->assertCount(0, $form->getMessages());
    }

    public function testRegistrationFieldIsInValid_registrationFieldFilledAndCheckboxSelected_shouldDisplayErrors()
    {
        $form = $this->buildForm('TES T123', true, 'TES T123', false);
        $form->setData($this->setDataValues('TES T123', true, 'TES T123', false));
        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->getMessages());
        $this->assertSame(self::ERROR_CANNOT_SELECT_AND_ENTER_REGISTRATION, $form->getMessages()['reg-input'][0]);
    }

    public function testVinFieldIsInValid_greaterThan20Characters_shouldDisplayErrors()
    {
        $form = $this->buildForm();
        $form->setData($this->setDataValues('TES T123', false, 'TES TVIN 123456789 TT', false));
        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->getMessages());
        $this->assertSame(self::ERROR_VIN_GREATER_THAN_20_CHARACTERS, $form->getMessages()['vin-input'][0]);
    }

    public function testVinFieldIsInValid_vinFieldFilledAndCheckboxSelected_shouldDisplayErrors()
    {
        $form = $this->buildForm('TES T123', false, 'TES T123', true);
        $form->setData($this->setDataValues('TES T123', false, 'TES T123', true));
        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->getMessages());
        $this->assertSame(self::ERROR_CANNOT_SELECT_AND_ENTER_VIN, $form->getMessages()['vin-input'][0]);
    }

    private function setDataValues(
        $registrationFieldValue,
        $registrationCheckboxValue,
        $vinFieldValue,
        $vinCheckboxValue
    )
    {
        return [
            'reg-input' => $registrationFieldValue,
            'vin-input' => $vinFieldValue,
            'leavingRegBlank' => $registrationCheckboxValue,
            'leavingVINBlank' => $vinCheckboxValue,
        ];
    }

    private function buildForm(
        $registrationFieldValue = null,
        $registrationCheckboxValue = false,
        $vinFieldValue = null,
        $vinCheckboxValue = false
    )
    {
        return new RegistrationAndVinForm(
            $registrationFieldValue,
            $registrationCheckboxValue,
            $vinFieldValue,
            $vinCheckboxValue
        );
    }
}
<?php

namespace Vehicle\CreateVehicle\Form;

class ClassFormTest extends \PHPUnit_Framework_TestCase
{
    const VALID_VEHICLE_CLASS = '5';
    const INVALID_VEHICLE_CLASS = '';

    public function setUp()
    {
        parent::setUp();
    }

    public function testIsValid_classSelected_noValidationMessageDisplayed()
    {
        $form = $this->buildForm(self::VALID_VEHICLE_CLASS, $this->mockAllowedClasses());
        $form->setData($this->setDataValues(self::VALID_VEHICLE_CLASS));
        $this->assertTrue($form->isValid());
        $this->assertCount(0, $form->getMessages());
    }

    public function testIsValid_noClassSelected_validationMessageDisplayed()
    {
        $form = $this->buildForm(self::INVALID_VEHICLE_CLASS, $this->mockAllowedClasses());
        $form->setData($this->setDataValues(self::INVALID_VEHICLE_CLASS));
        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->getErrorMessages());
        $this->assertSame(ClassForm::SELECT_CLASS_ERROR, $form->getErrorMessages()[0]);
    }

    public function testIfValueSentToForm_ThenValueIsPresetToGivenValue()
    {
        $form = $this->buildForm(self::VALID_VEHICLE_CLASS, $this->mockAllowedClasses());
        $form->setData($this->setDataValues(self::VALID_VEHICLE_CLASS));
        $this->assertTrue($form->isValid());
        $this->assertSame(self::VALID_VEHICLE_CLASS, $form->getClassRadioGroup()->getValue());
    }

    private function setDataValues($class)
    {
        return ['class' => $class];
    }

    private function mockAllowedClasses()
    {
        return ['forPerson' => [
                0 => '1',
                1 => '2',
                2 => '3',
                3 => '4',
                4 => '5',
                5 => '7',
            ],
            'forVts' => [
                    0 => '1',
                    1 => '2',
                    2 => '3',
                    3 => '4',
                    4 => '5',
                    5 => '7',
                ],
        ];
    }

    private function buildForm($classFieldValue, $allowedClasses)
    {
        return new ClassForm($classFieldValue, $allowedClasses);
    }
}

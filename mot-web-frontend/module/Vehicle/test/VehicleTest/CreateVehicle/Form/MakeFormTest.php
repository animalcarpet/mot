<?php

namespace VehicleTest\CreateVehicle\Form;

use Vehicle\CreateVehicle\Form\MakeForm;

class MakeFormTest extends \PHPUnit_Framework_TestCase
{
    public function testWhenUserSelectsPleaseSelect_validationMessageIsShown()
    {
        $form = new MakeForm($this->getMockMakes());

        $modelData = [
            MakeForm::MODEL => 'Please select',
            MakeForm::OTHER => '',
        ];

        $form->setData($modelData);
        $this->assertFalse($form->isValid());
        $this->assertNotEmpty($form->getVehicleMake()->getMessages());
    }

    public function testWhenUserSelectsOtherButEmpty_validationMessageIsShown()
    {
        $form = new MakeForm($this->getMockMakes());

        $modelData = [
            MakeForm::MODEL => 'Other',
            MakeForm::OTHER => '',
        ];

        $form->setData($modelData);
        $this->assertFalse($form->isValid());
        $this->assertNotEmpty($form->getOther()->getMessages());
    }

    public function testWhenUserEntersValidOtherData_validationPasses()
    {
        $form = new MakeForm($this->getMockMakes(), 'Other');

        $modelData = [
            MakeForm::MODEL => 'Other',
            MakeForm::OTHER => 'Zonda',
        ];

        $form->setData($modelData);
        $this->assertTrue($form->isValid());
    }

    public function testWhenUserEntersOtherOverMaxLengthOfCharacters_validationWillFail()
    {
        $form = new MakeForm($this->getMockMakes(), 'Other');

        $modelData = [
            MakeForm::MODEL => 'Other',
            MakeForm::OTHER => 'TprWULx4hPzeKwHpPLyKZh48CA7EIPq68nNscASf',
        ];

        $form->setData($modelData);
        $this->assertFalse($form->isValid());
    }

    private function getMockMakes()
    {
        return [
            ['id' => '1000', 'name' => 'Audi'],
            ['id' => '1001', 'name' => 'BMW'],
        ];
    }
}

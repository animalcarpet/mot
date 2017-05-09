<?php

namespace Vehicle\CreateVehicle\Form;

class ColourFormTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testIsValid_validData_shouldNotDisplayErrors()
    {
        $form = $this->buildForm($this->mockAvailableColours(), 'e', '');
        $form->setData($this->setDataValues('e', ''));
        $this->assertTrue($form->isValid());
        $this->assertCount(0, $form->getMessages());
    }

    public function testIsValid_inValidDataAsPleaseSelectSelected_shouldDisplayErrors()
    {
        $form = $this->buildForm($this->mockAvailableColours(), 'PLEASE_SELECT', '');
        $form->setData($this->setDataValues('PLEASE_SELECT', ''));
        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->getMessages());
        $this->assertSame('Select an option', $form->getMessages()['primaryColour'][0]);
    }

    private function setDataValues(
        $primaryColour,
        $secondaryColour
    ) {
        return [
            'primaryColour' => $primaryColour,
            'secondaryColours' => $secondaryColour,
        ];
    }

    private function mockAvailableColours()
    {
        return [
            'S' => 'Beige',
            'P' => 'Black',
            'B' => 'Bronze',
            'A' => 'Brown',
            'V' => 'Cream',
            'G' => 'Gold',
            'H' => 'Green',
            'L' => 'Grey',
            'T' => 'Maroon',
            'K' => 'Purple',
            'E' => 'Orange',
            'D' => 'Pink',
            'C' => 'Red',
            'M' => 'Silver',
            'U' => 'Turquoise',
            'N' => 'White',
            'F' => 'Yellow',
            'R' => 'Multi-colour',
            'W' => 'Not Stated',
            'J' => 'Blue',
        ];
    }

    private function buildForm(
        $colours,
        $primaryColours,
        $secondaryColours
    ) {
        return new ColourForm(
            $colours,
            $primaryColours,
            $secondaryColours
        );
    }
}

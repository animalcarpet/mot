<?php
namespace VehicleTest\UpdateVehicleProperty\Form;

use DvsaCommon\Enum\ColourCode;
use Vehicle\UpdateVehicleProperty\Form\UpdateColourForm;

class UpdateColourFormTest extends \PHPUnit_Framework_TestCase
{
    /** @var  UpdateColourForm | \PHPUnit_Framework_MockObject_MockObject */
    private $form;

    public function setUp()
    {
        $colours = [
            ColourCode::SILVER => "Silver",
            ColourCode::BLACK => "Black",
            ColourCode::NOT_STATED => "Not stated",
        ];

        $this->form = new UpdateColourForm($colours);
    }

    /**
     * @dataProvider dataProviderTestFormValidation
     */
    public function testFormValidation($colour, $secondaryColour, $result)
    {
        $formData = [
            UpdateColourForm::FIELD_COLOUR => $colour,
            UpdateColourForm::FIELD_SECONDARY_COLOUR => $secondaryColour,
        ];

        $this->form->setData($formData);
        $this->assertEquals($result, $this->form->isValid());
    }


    public function dataProviderTestFormValidation()
    {
        return [
            //both colours sent
            [ColourCode::BLACK, ColourCode::BLACK, true],
            //one colour sent
            [ColourCode::SILVER, ColourCode::NOT_STATED, true],
            [ColourCode::SILVER, null, true],
            //provided values are not in colour list
            ["notCode", "thisIsNotColourCode", false],
            //primary colour not sent
            [null, ColourCode::SILVER, false],
            [ColourCode::NOT_STATED, ColourCode::SILVER, false],
            //no colours sent
            [null, null, false],
        ];
    }
}
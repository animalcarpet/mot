<?php

namespace SiteTest\UpdateVtsProperty\Form;

use Site\UpdateVtsProperty\Process\Form\ClassesPropertyForm;

class ClassesPropertyFormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider validData
     */
    public function testFormForValidData(array $data)
    {
        $form = new ClassesPropertyForm();
        $form->setData($data);

        $this->assertTrue($form->isValid());
        $this->assertCount(0, $form->getMessages());
    }

    public function validData()
    {
        return [
            [[ClassesPropertyForm::FIELD_CLASSES => ['1', '2', '3', '4', '5', '7']]],
            [[ClassesPropertyForm::FIELD_CLASSES => ['1', '7']]],
            [[ClassesPropertyForm::FIELD_CLASSES => []]],
            [[]],
        ];
    }
}

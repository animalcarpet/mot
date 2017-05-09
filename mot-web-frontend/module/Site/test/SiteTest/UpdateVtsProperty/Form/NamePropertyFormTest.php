<?php

namespace SiteTest\UpdateVtsProperty\Form;

use Site\UpdateVtsProperty\Process\Form\NamePropertyForm;

class NamePropertyFormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider validData
     */
    public function testFormForValidData(array $data)
    {
        $form = new NamePropertyForm();
        $form->setData($data);

        $this->assertTrue($form->isValid());
        $this->assertCount(0, $form->getMessages());
    }

    public function validData()
    {
        return [
            [[NamePropertyForm::FIELD_NAME => 'site name']],
            [[NamePropertyForm::FIELD_NAME => 'site name 2 !']],
            [[NamePropertyForm::FIELD_NAME => $this->createName(100)]],
        ];
    }

    /**
     * @dataProvider invalidData
     */
    public function testFormReturnsErrorMsgForInvalidData(array $data, $expectedMsg)
    {
        $form = new NamePropertyForm();
        $form->setData($data);

        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->getMessages());

        $messages = $form->getNameElement()->getMessages();
        $this->assertCount(1, $messages);
        $this->assertEquals($expectedMsg, array_shift($messages));
    }

    public function invalidData()
    {
        return [
            [[NamePropertyForm::FIELD_NAME => ''], NamePropertyForm::NAME_EMPTY_MSG],
            [[NamePropertyForm::FIELD_NAME => ' '], NamePropertyForm::NAME_EMPTY_MSG],
            [[NamePropertyForm::FIELD_NAME => $this->createName(100, ' ')], NamePropertyForm::NAME_EMPTY_MSG],
            [[NamePropertyForm::FIELD_NAME => $this->createName(101)], str_replace('%max%', NamePropertyForm::FIELD_NAME_MAX_LENGTH, NamePropertyForm::NAME_TOO_LONG_MSG)],
        ];
    }

    private function createName($length, $char = 'X')
    {
        $name = '';
        while ($length) {
            $name .= $char;
            --$length;
        }

        return $name;
    }
}

<?php

namespace Organisation\UpdateAeProperty\Form;

use Organisation\UpdateAeProperty\Process\Form\RegisteredPhonePropertyForm;

class PhonePropertyFormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider validData
     */
    public function testFormForValidData(array $data)
    {
        $form = new RegisteredPhonePropertyForm();
        $form->setData($data);

        $this->assertTrue($form->isValid());
        $this->assertCount(0, $form->getMessages());
    }

    public function validData()
    {
        return [
            [[RegisteredPhonePropertyForm::FIELD_PHONE => '1']],
            [[RegisteredPhonePropertyForm::FIELD_PHONE => '22-678-345-342']],
            [[RegisteredPhonePropertyForm::FIELD_PHONE => '22 678 345 342']],
            [[RegisteredPhonePropertyForm::FIELD_PHONE => $this->createPhone(RegisteredPhonePropertyForm::FIELD_PHONE_MAX_LENGTH)]],
        ];
    }

    /**
     * @dataProvider invalidData
     */
    public function testFormReturnsErrorMsgForInvalidData(array $data, $expectedMsg)
    {
        $form = new RegisteredPhonePropertyForm();
        $form->setData($data);

        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->getMessages());

        $messages = $form->getPhoneElement()->getMessages();
        $this->assertCount(1, $messages);
        $this->assertEquals($expectedMsg, array_shift($messages));
    }

    public function invalidData()
    {
        return [
            [[RegisteredPhonePropertyForm::FIELD_PHONE => ''], RegisteredPhonePropertyForm::PHONE_EMPTY_MSG],
            [[RegisteredPhonePropertyForm::FIELD_PHONE => ' '], RegisteredPhonePropertyForm::PHONE_EMPTY_MSG],
            [[RegisteredPhonePropertyForm::FIELD_PHONE => $this->createPhone(RegisteredPhonePropertyForm::FIELD_PHONE_MAX_LENGTH, ' ')], RegisteredPhonePropertyForm::PHONE_EMPTY_MSG],
            [
                [RegisteredPhonePropertyForm::FIELD_PHONE => $this->createPhone(RegisteredPhonePropertyForm::FIELD_PHONE_MAX_LENGTH + 1)],
                str_replace('%max%', RegisteredPhonePropertyForm::FIELD_PHONE_MAX_LENGTH, RegisteredPhonePropertyForm::PHONE_TOO_LONG_MSG),
            ],
        ];
    }

    private function createPhone($length, $char = '1')
    {
        $name = '';
        while ($length) {
            $name .= $char;
            --$length;
        }

        return $name;
    }
}

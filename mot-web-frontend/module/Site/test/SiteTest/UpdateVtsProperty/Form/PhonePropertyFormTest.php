<?php

namespace SiteTest\UpdateVtsProperty\Form;

use Site\UpdateVtsProperty\Process\Form\PhonePropertyForm;

class PhonePropertyFormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider validData
     */
    public function testFormForValidData(array $data)
    {
        $form = new PhonePropertyForm();
        $form->setData($data);

        $this->assertTrue($form->isValid());
        $this->assertCount(0, $form->getMessages());
    }

    public function validData()
    {
        return [
            [[PhonePropertyForm::FIELD_PHONE => '1']],
            [[PhonePropertyForm::FIELD_PHONE => '22-678-345-342']],
            [[PhonePropertyForm::FIELD_PHONE => '22 678 345 342']],
            [[PhonePropertyForm::FIELD_PHONE => $this->createPhone(PhonePropertyForm::FIELD_PHONE_MAX_LENGTH)]],
        ];
    }

    /**
     * @dataProvider invalidData
     */
    public function testFormReturnsErrorMsgForInvalidData(array $data, $expectedMsg)
    {
        $form = new PhonePropertyForm();
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
            [[PhonePropertyForm::FIELD_PHONE => ''], PhonePropertyForm::PHONE_EMPTY_MSG],
            [[PhonePropertyForm::FIELD_PHONE => ' '], PhonePropertyForm::PHONE_EMPTY_MSG],
            [[PhonePropertyForm::FIELD_PHONE => $this->createPhone(PhonePropertyForm::FIELD_PHONE_MAX_LENGTH, ' ')], PhonePropertyForm::PHONE_EMPTY_MSG],
            [
                [PhonePropertyForm::FIELD_PHONE => $this->createPhone(PhonePropertyForm::FIELD_PHONE_MAX_LENGTH + 1)],
                str_replace('%max%', PhonePropertyForm::FIELD_PHONE_MAX_LENGTH, PhonePropertyForm::PHONE_TOO_LONG_MSG),
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

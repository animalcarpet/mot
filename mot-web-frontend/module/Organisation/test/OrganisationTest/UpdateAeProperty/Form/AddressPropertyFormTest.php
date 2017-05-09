<?php

namespace Organisation\UpdateAeProperty\Form;

use Organisation\UpdateAeProperty\Process\Form\AddressPropertyForm;

class AddressPropertyFormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider validData
     */
    public function testFormForValidData(array $data)
    {
        $form = new AddressPropertyForm();
        $form->setData($data);

        $this->assertTrue($form->isValid());
    }

    public function testFormForInvalidData_shouldSignalRequired()
    {
        $data = [
            AddressPropertyForm::FIELD_ADDRESS_LINE_1 => '',
            AddressPropertyForm::FIELD_TOWN => '',
            AddressPropertyForm::FIELD_POSTCODE => '',
        ];

        $form = new AddressPropertyForm();
        $form->setData($data);

        $this->assertFalse($form->isValid());
        $this->assertCount(3, $form->getMessages());
        $this->assertContains('you must enter the first line of the address', self::messagesForField($form, AddressPropertyForm::FIELD_ADDRESS_LINE_1));
        $this->assertContains('you must enter a town or city', self::messagesForField($form, AddressPropertyForm::FIELD_TOWN));
        $this->assertContains('you must enter a postcode', self::messagesForField($form, AddressPropertyForm::FIELD_POSTCODE));
    }

    public function testFormForToLongText_signalTooLongError()
    {
        $data = [
            AddressPropertyForm::FIELD_ADDRESS_LINE_1 => str_repeat('A', 51),
            AddressPropertyForm::FIELD_ADDRESS_LINE_2 => str_repeat('A', 51),
            AddressPropertyForm::FIELD_ADDRESS_LINE_3 => str_repeat('A', 51),
            AddressPropertyForm::FIELD_TOWN => str_repeat('A', 51),
            AddressPropertyForm::FIELD_COUNTRY => str_repeat('A', 51),
            AddressPropertyForm::FIELD_POSTCODE => str_repeat('A', 11),
        ];

        $form = new AddressPropertyForm();
        $form->setData($data);

        $this->assertFalse($form->isValid());
        $this->assertCount(6, $form->getMessages());
        $this->assertContains('must be 50 characters or less', self::messagesForField($form, AddressPropertyForm::FIELD_ADDRESS_LINE_1));
        $this->assertContains('must be 50 characters or less', self::messagesForField($form, AddressPropertyForm::FIELD_ADDRESS_LINE_2));
        $this->assertContains('must be 50 characters or less', self::messagesForField($form, AddressPropertyForm::FIELD_ADDRESS_LINE_3));
        $this->assertContains('must be 50 characters or less', self::messagesForField($form, AddressPropertyForm::FIELD_TOWN));
        $this->assertContains('must be 10 characters or less', self::messagesForField($form, AddressPropertyForm::FIELD_POSTCODE));
    }

    public function testFormForToMaxLength_shouldExpectNoErrors()
    {
        $data = [
            AddressPropertyForm::FIELD_ADDRESS_LINE_1 => str_repeat('A', 50),
            AddressPropertyForm::FIELD_ADDRESS_LINE_2 => str_repeat('A', 50),
            AddressPropertyForm::FIELD_ADDRESS_LINE_3 => str_repeat('A', 50),

            AddressPropertyForm::FIELD_COUNTRY => str_repeat('A', 50),
            AddressPropertyForm::FIELD_TOWN => str_repeat('A', 50),
            AddressPropertyForm::FIELD_POSTCODE => str_repeat('A', 10),
        ];

        $form = new AddressPropertyForm();
        $form->setData($data);

        $this->assertTrue($form->isValid());
    }

    public function validData()
    {
        return [
            [[
                AddressPropertyForm::FIELD_ADDRESS_LINE_1 => 'LINE1',
                AddressPropertyForm::FIELD_TOWN => 'my town',
                AddressPropertyForm::FIELD_POSTCODE => 'BS2FDW',
            ]],
        ];
    }

    private static function messagesForField($form, $field)
    {
        return array_values($form->getMessages($field));
    }
}

<?php

namespace Vehicle\CreateVehicle\Form;

class CountryOfRegistrationFormTest extends \PHPUnit_Framework_TestCase
{
    public function testWhenUserSelectsPleaseSelect_validationMessageIsShown()
    {
        $form = new CountryOfRegistrationForm($this->mockModels());

        $modelData = [
            CountryOfRegistrationForm::COUNTRY_OF_REGISTRATION_NAME => 'Please select',
        ];

        $form->setData($modelData);
        $this->assertFalse($form->isValid());
        $this->assertTrue($form->IsErrorOnCountryField());
        $this->assertNotEmpty($form->getCountryOfRegistration()->getMessages());
        $this->assertSame(
            CountryOfRegistrationForm::PLEASE_SELECT_FIELD_ERROR_MSG,
            $form->getCountryOfRegistration()->getMessages()[0]
        );
    }

    public function testWhenUserEntersValidCountrySelection_validationPasses()
    {
        $form = new CountryOfRegistrationForm($this->mockModels());

        $modelData = [
            CountryOfRegistrationForm::COUNTRY_OF_REGISTRATION_NAME => 'UK',
        ];

        $form->setData($modelData);
        $this->assertTrue($form->isValid());
        $this->assertFalse($form->IsErrorOnCountryField());
    }

    public function testWhenCountryHasAlreadyBeenSelected_fieldIsPrePopulated()
    {
        $expected = 'UK';
        $form = new CountryOfRegistrationForm($this->mockModels(), $expected);

        $this->assertSame($expected, $form->getCountryOfRegistration()->getValue());
    }

    private function mockModels()
    {
        return [
            ['code' => 'UK', 'name' => 'United Kingdom'],
            ['code' => 'IRELAND', 'name' => 'Republic of Ireland'],
        ];
    }
}
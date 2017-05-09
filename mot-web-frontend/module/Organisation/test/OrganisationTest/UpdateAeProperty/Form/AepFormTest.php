<?php

namespace OrganisationTest\UpdateAeProperty\Form;

use DvsaCommon\Validator\DateOfBirthValidator;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\FirstNameInput;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\MiddleNameInput;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\FamilyNameInput;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\AddressLine1Input;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\AddressLine2Input;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\AddressLine3Input;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\PostcodeInput;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\CountryInput;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\TownInput;
use Organisation\UpdateAeProperty\Process\Form\AepForm;

class AepFormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider validData
     */
    public function testFormForValidData(array $data)
    {
        $form = new AepForm();
        $form->setData($data);

        $this->assertTrue($form->isValid());
        $this->assertCount(0, $form->getMessages());
    }

    public function validData()
    {
        return [
            [
                [
                    AepForm::FIELD_DOB_DAY => '1',
                    AepForm::FIELD_DOB_MONTH => '2',
                    AepForm::FIELD_DOB_YEAR => '2010',
                    FirstNameInput::FIELD => 'John',
                    MiddleNameInput::FIELD => '',
                    FamilyNameInput::FIELD => 'Rambo',
                    AddressLine1Input::FIELD => 'address line 1',
                    AddressLine2Input::FIELD => 'address line 2',
                    AddressLine3Input::FIELD => 'address line 3',
                    PostcodeInput::FIELD => '1234567890',
                    CountryInput::FIELD => '',
                    TownInput::FIELD => 'Bristol',
                ],
            ],
        ];
    }

    /**
     * @dataProvider invalidData
     */
    public function testFormForInvalidDobData(array $data, array $expectedMessages)
    {
        $form = new AepForm();
        $form->setData($data);

        $this->assertFalse($form->isValid());
        $this->assertCount(count($expectedMessages), $form->getMessages());
        $this->assertEquals($expectedMessages, $form->getMessages());
    }

    public function invalidData()
    {
        return [
            [
                [
                    AepForm::FIELD_DOB_DAY => 'b',
                    AepForm::FIELD_DOB_MONTH => '2',
                    AepForm::FIELD_DOB_YEAR => '2010',
                    FirstNameInput::FIELD => 'John',
                    MiddleNameInput::FIELD => '',
                    FamilyNameInput::FIELD => 'Rambo',
                    AddressLine1Input::FIELD => 'address line 1',
                    AddressLine2Input::FIELD => 'address line 2',
                    AddressLine3Input::FIELD => 'address line 3',
                    PostcodeInput::FIELD => '1234567890',
                    CountryInput::FIELD => '',
                    TownInput::FIELD => 'Bristol',
                ],

                [
                    AepForm::FIELD_DOB_DAY => [
                        DateOfBirthValidator::IS_INVALID_FORMAT => DateOfBirthValidator::ERR_MSG_IS_INVALID_FORMAT,
                    ],
                ],
            ],

            [
                [
                    AepForm::FIELD_DOB_DAY => '01',
                    AepForm::FIELD_DOB_MONTH => '',
                    AepForm::FIELD_DOB_YEAR => '2010',
                    FirstNameInput::FIELD => 'John',
                    MiddleNameInput::FIELD => '',
                    FamilyNameInput::FIELD => 'Rambo',
                    AddressLine1Input::FIELD => 'address line 1',
                    AddressLine2Input::FIELD => 'address line 2',
                    AddressLine3Input::FIELD => 'address line 3',
                    PostcodeInput::FIELD => '1234567890',
                    CountryInput::FIELD => '',
                    TownInput::FIELD => 'Bristol',
                ],
                [
                    AepForm::FIELD_DOB_DAY => [
                        DateOfBirthValidator::IS_INVALID_FORMAT => DateOfBirthValidator::ERR_MSG_IS_INVALID_FORMAT,
                    ],
                ],
            ],

            [
                [
                    AepForm::FIELD_DOB_DAY => '01',
                    AepForm::FIELD_DOB_MONTH => '02',
                    AepForm::FIELD_DOB_YEAR => '',
                    FirstNameInput::FIELD => 'John',
                    MiddleNameInput::FIELD => '',
                    FamilyNameInput::FIELD => 'Rambo',
                    AddressLine1Input::FIELD => 'address line 1',
                    AddressLine2Input::FIELD => 'address line 2',
                    AddressLine3Input::FIELD => 'address line 3',
                    PostcodeInput::FIELD => '1234567890',
                    CountryInput::FIELD => '',
                    TownInput::FIELD => 'Bristol',
                ],
                [
                    AepForm::FIELD_DOB_DAY => [
                        DateOfBirthValidator::IS_INVALID_FORMAT => DateOfBirthValidator::ERR_MSG_IS_INVALID_FORMAT,
                    ],
                ],
            ],

            [
                [
                    AepForm::FIELD_DOB_DAY => '',
                    AepForm::FIELD_DOB_MONTH => '',
                    AepForm::FIELD_DOB_YEAR => '',
                    FirstNameInput::FIELD => 'John',
                    MiddleNameInput::FIELD => '',
                    FamilyNameInput::FIELD => 'Rambo',
                    AddressLine1Input::FIELD => 'address line 1',
                    AddressLine2Input::FIELD => 'address line 2',
                    AddressLine3Input::FIELD => 'address line 3',
                    PostcodeInput::FIELD => '1234567890',
                    CountryInput::FIELD => '',
                    TownInput::FIELD => 'Bristol',
                ],
                [
                    AepForm::FIELD_DOB_DAY => [
                        DateOfBirthValidator::IS_EMPTY => DateOfBirthValidator::ERR_MSG_IS_EMPTY,
                    ],
                ],
            ],

            [
                [
                    AepForm::FIELD_DOB_DAY => '1',
                    AepForm::FIELD_DOB_MONTH => '',
                    AepForm::FIELD_DOB_YEAR => '',
                    FirstNameInput::FIELD => 'John',
                    MiddleNameInput::FIELD => '',
                    FamilyNameInput::FIELD => 'Rambo',
                    AddressLine1Input::FIELD => 'address line 1',
                    AddressLine2Input::FIELD => 'address line 2',
                    AddressLine3Input::FIELD => 'address line 3',
                    PostcodeInput::FIELD => '1234567890',
                    CountryInput::FIELD => '',
                    TownInput::FIELD => 'Bristol',
                ],
                [
                    AepForm::FIELD_DOB_DAY => [
                        DateOfBirthValidator::IS_INVALID_FORMAT => DateOfBirthValidator::ERR_MSG_IS_INVALID_FORMAT,
                    ],
                ],
            ],
        ];
    }
}

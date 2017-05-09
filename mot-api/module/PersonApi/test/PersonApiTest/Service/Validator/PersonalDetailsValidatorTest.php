<?php

namespace PersonApiTest\Service\Validator;

use PersonApi\Service\Validator\PersonalDetailsValidator;
use PHPUnit_Framework_TestCase;

/**
 * Class PersonalDetailsValidatorTest.
 */
class PersonalDetailsValidatorTest extends PHPUnit_Framework_TestCase
{
    public function testValidateCorrectDataDoesNotThrowException()
    {
        $data = $this->getCorrectData();

        $this->callValidator($data);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\RequiredFieldException
     */
    public function testValidateMissingRequiredFieldsThrowsException()
    {
        $data = [];

        $this->callValidator($data);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function testValidateIncorrectDateOfBirthThrowsException()
    {
        $data = $this->getCorrectData();
        $data['dateOfBirth'] = '2000-03-1x';

        $this->callValidator($data);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function testValidateIncorrectDateOfBirthInTheFutureThrowsException()
    {
        $data = $this->getCorrectData();
        $data['dateOfBirth'] = '2100-03-10';

        $this->callValidator($data);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function testValidateEmailConfirmationDoesntMatch()
    {
        $data = $this->getCorrectData();
        $data['emailConfirmation'] = $data['email'].'doesnotmatch';

        $this->callValidator($data);
    }

    /**
     * @param $data
     */
    private function callValidator($data)
    {
        $validator = new PersonalDetailsValidator();
        $validator->validate($data);
    }

    /**
     * @return array
     */
    private function getCorrectData()
    {
        return [
            'title' => 'Mr',
            'firstName' => 'John',
            'middleName' => 'Adam',
            'surname' => 'Smith',
            'gender' => 'Male',
            'drivingLicenceNumber' => '1232142314',
            'drivingLicenceRegion' => 'oth',
            'addressLine1' => 'Ulica',
            'addressLine2' => 'Sezamkowa',
            'addressLine3' => '34',
            'town' => 'Berlin',
            'postcode' => '23-232',
            'email' => 'persondetailsvalidatortest@dvsa.com',
            'emailConfirmation' => 'persondetailsvalidatortest@dvsa.com',
            'phoneNumber' => '+32324-324-324-234',
            'dateOfBirth' => '1980-05-05',
        ];
    }
}

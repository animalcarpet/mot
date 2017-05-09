<?php

namespace UserApiTest\Application\Service\Validator;

use DateInterval;
use DateTime;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Validator\EmailAddressValidator;
use UserApi\Application\Service\Validator\AccountValidator;

/**
 * unit tests for AccountValidator.
 */
class AccountValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function testValidateCorrectDataNoException()
    {
        $data = $this->getCorrectData();

        $this->callValidator($data);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function testInvalidDateThrowsException()
    {
        $data = $this->getCorrectData();
        $data['dateOfBirth'] = '1980-02-31';

        $this->callValidator($data);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function testValidateMissingRequiredFieldsThrowsException()
    {
        $data = [];

        $this->callValidator($data);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function testValidateDifferentEmailsThrowsException()
    {
        $data = $this->getCorrectData();
        $data = array_merge(
            $data,
            [
                'email' => 'email',
                'emailConfirmation' => 'differentEmail',
            ]
        );

        $this->callValidator($data);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function testValidateDifferentPasswordsThrowsException()
    {
        $data = $this->getCorrectData();
        $data = array_merge(
            $data,
            [
                'password' => 'AlaMa1Kota',
                'passwordConfirmation' => 'AlaMa3Koty',
            ]
        );

        $this->callValidator($data);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function testValidateTooShortPasswordThrowsException()
    {
        $data = $this->getCorrectData();
        $data = array_merge(
            $data,
            [
                'password' => 'AlaMa1',
                'passwordConfirmation' => 'AlaMa1',
            ]
        );

        $this->callValidator($data);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function testValidateNoLowercaseLettersThrowsException()
    {
        $data = $this->getCorrectData();
        $data = array_merge(
            $data,
            [
                'password' => 'ALAMA1KOTA',
                'passwordConfirmation' => 'ALAMA1KOTA',
            ]
        );

        $this->callValidator($data);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function testValidateNoUppercaseLettersThrowsException()
    {
        $data = $this->getCorrectData();
        $data = array_merge(
            $data,
            [
                'password' => 'alama1kota',
                'passwordConfirmation' => 'alama1kota',
            ]
        );

        $this->callValidator($data);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function testValidateNoDigitThrowsException()
    {
        $data = $this->getCorrectData();
        $data = array_merge(
            $data,
            [
                'password' => 'AlaMaJednegoKota',
                'passwordConfirmation' => 'AlaMaJednegoKota',
            ]
        );

        $this->callValidator($data);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function testValidateUnder16ThrowsException()
    {
        $data = $this->getCorrectData();
        $dateTimeHolder = new DateTimeHolder();
        $sixteenYears = new DateInterval('P16Y');
        $oneDay = new DateInterval('P1D');
        $date = (new DateTime($dateTimeHolder->getCurrent()->format('Y-m-d')))->sub($sixteenYears)->add($oneDay);
        $data = array_merge(
            $data,
            [
                'dateOfBirth' => $date->format('Y-m-d'),
            ]
        );

        $this->callValidator($data);
    }

    public function testValidateUnder16DoesNotThrowExceptionForValidDate()
    {
        $data = $this->getCorrectData();
        $dateTimeHolder = new DateTimeHolder();
        $sixteenYears = new DateInterval('P16Y');
        $date = (new DateTime($dateTimeHolder->getCurrent()->format('Y-m-d')))->sub($sixteenYears);
        $data = array_merge(
            $data,
            [
                'dateOfBirth' => $date->format('Y-m-d'),
            ]
        );

        $this->callValidator($data);
    }

    /**
     * @param $data
     */
    private function callValidator($data)
    {
        $validator = new AccountValidator();
        $validator->validate($data);
    }

    /**
     * @return array
     */
    private function getCorrectData()
    {
        return [
            'title' => 'title',
            'firstName' => 'firstName',
            'surname' => 'surname',
            'dateOfBirth' => '1980-03-15',
            'gender' => 'gender',
            'addressLine1' => 'addressLine1',
            'town' => 'town',
            'postcode' => 'postcode',
            'phoneNumber' => 'phoneNumber',
            'email' => 'accountvalidatortest@'.EmailAddressValidator::TEST_DOMAIN,
            'emailConfirmation' => 'accountvalidatortest@'.EmailAddressValidator::TEST_DOMAIN,
            'password' => 'AlaMa1Kota',
            'passwordConfirmation' => 'AlaMa1Kota',
            'drivingLicenceRegion' => 'other',
            'drivingLicenceNumber' => '2323123',
        ];
    }
}

<?php

namespace Dvsa\Mot\Frontend\RegistrationModuleTest\Service;

use Dvsa\Mot\Frontend\RegistrationModule\Service\RegisterUserService;
use Dvsa\Mot\Frontend\RegistrationModule\Step\ContactDetailsStep;
use Dvsa\Mot\Frontend\RegistrationModule\Step\DetailsStep;
use Dvsa\Mot\Frontend\RegistrationModule\Step\EmailStep;
use Dvsa\Mot\Frontend\RegistrationModule\Step\PasswordStep;
use Dvsa\Mot\Frontend\RegistrationModule\Step\SecurityQuestionsStep;
use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;
use DvsaCommon\HttpRestJson\Exception\GeneralRestException;
use DvsaCommon\InputFilter\Registration\ContactDetailsInputFilter;
use DvsaCommon\InputFilter\Registration\DetailsInputFilter;
use DvsaCommon\InputFilter\Registration\EmailInputFilter;
use DvsaCommon\InputFilter\Registration\PasswordInputFilter;
use DvsaCommon\InputFilter\Registration\SecurityQuestionsInputFilter;
use DvsaCommon\Validator\EmailAddressValidator;
use DvsaCommonTest\TestUtils\XMock;

class RegisterUserServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dpRegisterUser
     *
     * @param $jsonClientException
     * @param $expectedFunctionReturn
     */
    public function testRegisterUser($jsonClientException, $expectedFunctionReturn)
    {
        $jsonClientMock = XMock::of(HttpRestJsonClient::class);

        if (true === $jsonClientException) {
            $jsonClientMock
                ->expects($this->once())
                ->method('post')
                ->willThrowException(XMock::of(GeneralRestException::class));
        } else {
            $jsonClientMock
                ->expects($this->once())
                ->method('post');
        }

        $obj = new RegisterUserService($jsonClientMock);
        $actual = $obj->registerUser($this->registrationData());
        $this->assertSame($expectedFunctionReturn, $actual);
    }

    public function dpRegisterUser()
    {
        return [
            [false, true], // json post is good and therefore returns nothing
            [true, false], // json post is bad an throws an exception
        ];
    }

    private function registrationData()
    {
        return [
            EmailStep::STEP_ID => [
                EmailInputFilter::FIELD_EMAIL => 'registeruserservicetest@'.EmailAddressValidator::TEST_DOMAIN,
                EmailInputFilter::FIELD_EMAIL_CONFIRM => 'registeruserservicetest@'.EmailAddressValidator::TEST_DOMAIN,
            ],
            DetailsStep::STEP_ID => [
                DetailsInputFilter::FIELD_FIRST_NAME => 'Fred',
                DetailsInputFilter::FIELD_MIDDLE_NAME => '',
                DetailsInputFilter::FIELD_LAST_NAME => 'Flintstone',
                DetailsInputFilter::FIELD_DAY => '01',
                DetailsInputFilter::FIELD_MONTH => '02',
                DetailsInputFilter::FIELD_YEAR => '1999',
                DetailsInputFilter::FIELD_DATE => [
                    DetailsInputFilter::FIELD_DAY => '01',
                    DetailsInputFilter::FIELD_MONTH => '02',
                    DetailsInputFilter::FIELD_YEAR => '1999',
                ],
            ],
            ContactDetailsStep::STEP_ID => [
                ContactDetailsInputFilter::FIELD_ADDRESS_1 => '1 Bedrock Way',
                ContactDetailsInputFilter::FIELD_ADDRESS_2 => '',
                ContactDetailsInputFilter::FIELD_ADDRESS_3 => '',
                ContactDetailsInputFilter::FIELD_TOWN_OR_CITY => 'Bedrock',
                ContactDetailsInputFilter::FIELD_POSTCODE => 'BR1 2FF',
                ContactDetailsInputFilter::FIELD_PHONE => '123123123',
            ],
            PasswordStep::STEP_ID => [
                PasswordInputFilter::FIELD_PASSWORD => 'password1',
                PasswordInputFilter::FIELD_PASSWORD_CONFIRM => 'password1',
            ],
            SecurityQuestionsStep::STEP_ID => [
                SecurityQuestionsInputFilter::FIELD_QUESTION_1 => 1,
                SecurityQuestionsInputFilter::FIELD_ANSWER_1 => 'Yes',
                SecurityQuestionsInputFilter::FIELD_QUESTION_2 => 2,
                SecurityQuestionsInputFilter::FIELD_ANSWER_2 => 'No',
            ],
        ];
    }
}

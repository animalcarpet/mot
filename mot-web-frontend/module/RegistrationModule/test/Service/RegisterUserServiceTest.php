<?php

namespace Dvsa\Mot\Frontend\RegistrationModuleTest\Service;

use Dvsa\Mot\Frontend\RegistrationModule\Service\RegisterUserService;
use Dvsa\Mot\Frontend\RegistrationModule\Step\AddressStep;
use Dvsa\Mot\Frontend\RegistrationModule\Step\DetailsStep;
use Dvsa\Mot\Frontend\RegistrationModule\Step\EmailStep;
use Dvsa\Mot\Frontend\RegistrationModule\Step\PasswordStep;
use Dvsa\Mot\Frontend\RegistrationModule\Step\SecurityQuestionOneStep;
use Dvsa\Mot\Frontend\RegistrationModule\Step\SecurityQuestionTwoStep;
use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;
use DvsaCommon\HttpRestJson\Exception\GeneralRestException;
use DvsaCommon\InputFilter\Registration\AddressInputFilter;
use DvsaCommon\InputFilter\Registration\DetailsInputFilter;
use DvsaCommon\InputFilter\Registration\EmailInputFilter;
use DvsaCommon\InputFilter\Registration\PasswordInputFilter;
use DvsaCommon\InputFilter\Registration\SecurityQuestionFirstInputFilter;
use DvsaCommon\InputFilter\Registration\SecurityQuestionSecondInputFilter;
use DvsaCommon\Validator\EmailAddressValidator;
use DvsaCommonTest\TestUtils\XMock;

class RegisterUserServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param $jsonClientException
     * @param $expectedFunctionReturn
     * @dataProvider dpRegisterUser
     */
    public function testRegisterUser($jsonClientException, $expectedFunctionReturn)
    {
        $jsonClientMock = XMock::of(HttpRestJsonClient::class);

        if (true === $jsonClientException) {
            $jsonClientMock->expects($this->once())
                ->method('post')
                ->willThrowException(XMock::of(GeneralRestException::class));
        } else {
            $jsonClientMock->expects($this->once())
                ->method('post');
        }

        $obj = new RegisterUserService($jsonClientMock);
        $actual = $obj->registerUser($this->registrationData());
        $this->assertSame($expectedFunctionReturn, $actual);
    }

    public function dpRegisterUser()
    {
        return [
            [false, true],   // json post is good and therefore returns nothing
            [true, false],    // json post is bad an throws an exception
        ];
    }

    /**
     * @dataProvider getStubAPIResponse
     */
    public function testIsEmailDuplicated($apiResponse, $expectedOutcome)
    {
        $jsonClientMock = XMock::of(HttpRestJsonClient::class);

        $jsonClientMock->expects($this->any())
                ->method('post')
                ->willReturn($apiResponse);


        $registerUserService = new RegisterUserService($jsonClientMock);

        if ($expectedOutcome instanceof \Exception) {
            $this->setExpectedException(get_class($expectedOutcome));
        }

        $this->assertEquals($expectedOutcome, $registerUserService->isEmailDuplicated(''));
    }

    public function getStubAPIResponse()
    {
        return [
            [
                ['data'=> ['isExists' => true]],
                true,
            ],
            [
                ['data'=> ['isExists' => false]],
                false,
            ],
            [
                ['data'=> []],
                new \LogicException(),
            ],
            [
                [],
                new \LogicException(),
            ],
            [
                '',
                new \LogicException(),
            ],
        ];
    }

    private function registrationData()
    {
        return [
            EmailStep::STEP_ID => [
                EmailInputFilter::FIELD_EMAIL         => 'registeruserservicetest@' . EmailAddressValidator::TEST_DOMAIN,
                EmailInputFilter::FIELD_EMAIL_CONFIRM => 'registeruserservicetest@' . EmailAddressValidator::TEST_DOMAIN,
            ],
            DetailsStep::STEP_ID => [
                DetailsInputFilter::FIELD_FIRST_NAME    => 'Fred',
                DetailsInputFilter::FIELD_MIDDLE_NAME   => '',
                DetailsInputFilter::FIELD_LAST_NAME     => 'Flintstone',
                DetailsInputFilter::FIELD_PHONE         => '123123123',
            ],
            AddressStep::STEP_ID => [
                AddressInputFilter::FIELD_ADDRESS_1    => '1 Bedrock Way',
                AddressInputFilter::FIELD_ADDRESS_2    => '',
                AddressInputFilter::FIELD_ADDRESS_3    => '',
                AddressInputFilter::FIELD_TOWN_OR_CITY => 'Bedrock',
                AddressInputFilter::FIELD_POSTCODE     => 'BR1 2FF',
            ],
            PasswordStep::STEP_ID => [
                PasswordInputFilter::FIELD_PASSWORD         => 'password1',
                PasswordInputFilter::FIELD_PASSWORD_CONFIRM => 'password1',
            ],
            SecurityQuestionOneStep::STEP_ID => [
                SecurityQuestionFirstInputFilter::FIELD_QUESTION => 1,
                SecurityQuestionFirstInputFilter::FIELD_ANSWER   => 'Yes',
            ],
            SecurityQuestionTwoStep::STEP_ID => [
                SecurityQuestionSecondInputFilter::FIELD_QUESTION => 2,
                SecurityQuestionSecondInputFilter::FIELD_ANSWER   => 'No',
            ],
        ];
    }
}

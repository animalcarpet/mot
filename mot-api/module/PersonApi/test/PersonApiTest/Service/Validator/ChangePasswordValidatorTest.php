<?php

namespace PersonApiTest\Service\Validator;

use DvsaCommonTest\TestUtils\XMock;
use PersonApi\Service\Validator\ChangePasswordValidator;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Auth\MotIdentityInterface;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommon\InputFilter\Account\ChangePasswordInputFilter;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApi\Service\Exception\RequiredFieldException;
use Dvsa\OpenAM\OpenAMClientInterface;
use Dvsa\OpenAM\Exception\OpenAMClientException;

class ChangePasswordValidatorTest extends AbstractServiceTestCase
{
    const USERNAME = 'vts-Tester-1';

    /**
     * @dataProvider dataProvider
     */
    public function testValidateThrowsExceptionWhenDataAreInvalid($data, $expectedException)
    {
        $this->setExpectedException($expectedException);

        $validator = $this->createValidator(XMock::of(OpenAMClientInterface::class));
        $validator->validate($data);
    }

    public function testValidateThrowsExceptionWhenOldPasswordIsInvalid()
    {
        $this->setExpectedException(BadRequestException::class);

        $data = [
            ChangePasswordInputFilter::FIELD_OLD_PASSWORD => 'InvalidPassword',
            ChangePasswordInputFilter::FIELD_PASSWORD => 'Password1',
            ChangePasswordInputFilter::FIELD_PASSWORD_CONFIRM => 'Password1',
        ];

        $openAMClient = XMock::of(OpenAMClientInterface::class);
        $openAMClient
            ->expects($this->any())
            ->method('validateCredentials')
            ->willThrowException(new OpenAMClientException('Invalid password!!'));

        $validator = $this->createValidator($openAMClient);
        $validator->validate($data);
    }

    public function dataProvider()
    {
        return [
            [
                [],
                RequiredFieldException::class,
            ],

            [
                [ChangePasswordInputFilter::FIELD_PASSWORD => ''],
                RequiredFieldException::class,
            ],
            [
                [
                    ChangePasswordInputFilter::FIELD_PASSWORD => 'Password1',
                    ChangePasswordInputFilter::FIELD_PASSWORD_CONFIRM => 'Password1',
                ],
                RequiredFieldException::class,
            ],
            [
                [
                    ChangePasswordInputFilter::FIELD_OLD_PASSWORD => '',
                    ChangePasswordInputFilter::FIELD_PASSWORD => 'Password1',
                    ChangePasswordInputFilter::FIELD_PASSWORD_CONFIRM => 'pASSWORD1',
                ],
                RequiredFieldException::class,
            ],
            [
                [
                    ChangePasswordInputFilter::FIELD_OLD_PASSWORD => 'OldPassword1',
                    ChangePasswordInputFilter::FIELD_PASSWORD => 'Password1',
                    ChangePasswordInputFilter::FIELD_PASSWORD_CONFIRM => '',
                ],
                RequiredFieldException::class,
            ],
            [
                [
                    ChangePasswordInputFilter::FIELD_OLD_PASSWORD => 'OldPassword1',
                    ChangePasswordInputFilter::FIELD_PASSWORD => '',
                    ChangePasswordInputFilter::FIELD_PASSWORD_CONFIRM => 'Password1',
                ],
                RequiredFieldException::class,
            ],
            [
                [
                    ChangePasswordInputFilter::FIELD_OLD_PASSWORD => 'OldPassword1',
                    ChangePasswordInputFilter::FIELD_PASSWORD => 'password',
                    ChangePasswordInputFilter::FIELD_PASSWORD_CONFIRM => 'passwofrd',
                ],
                BadRequestException::class,
            ],
        ];
    }

    private function createValidator($openAmClient)
    {
        $inputFilter = new ChangePasswordInputFilter($this->createIdentityProvider());
        $inputFilter->init();

        return new ChangePasswordValidator(
            $this->createIdentityProvider(),
            $inputFilter,
            $openAmClient,
            'mot'
        );
    }

    private function createIdentityProvider()
    {
        $identity = XMock::of(MotIdentityInterface::class);
        $identity
            ->expects($this->any())
            ->method('getUsername')
            ->willReturn(self::USERNAME);

        $identityProvider = XMock::of(MotIdentityProviderInterface::class);
        $identityProvider
            ->expects($this->any())
            ->method('getIdentity')
            ->willReturn($identity);

        return $identityProvider;
    }
}

<?php

namespace PersonApiTest\Service;

use DvsaCommonTest\TestUtils\XMock;
use PersonApi\Service\PasswordService;
use PersonApi\Service\Validator\ChangePasswordValidator;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Auth\MotIdentityInterface;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use AccountApi\Service\OpenAmIdentityService;
use DvsaCommon\InputFilter\Account\ChangePasswordInputFilter;
use DvsaCommon\Exception\UnauthorisedException;
use AccountApi\Service\Exception\OpenAmChangePasswordException;
use DvsaCommonApi\Service\Exception\BadRequestException;

class PasswordServiceTest extends AbstractServiceTestCase
{
    public function testChangePasswordThrowsExceptionWhenChangePasswordForSomeUser()
    {
        $this->setExpectedException(UnauthorisedException::class);

        $passwordService = $this->createPasswordService(1, XMock::of(OpenAmIdentityService::class));
        $passwordService->changePassword(2, []);
    }

    public function testChangePasswordThrowsExceptionWhenOldPasswordAndNewPasswordAreIdentical()
    {
        $this->setExpectedException(BadRequestException::class);

        $personId = 1;
        $oldPassword = 'Password1';
        $data = [
            ChangePasswordInputFilter::FIELD_OLD_PASSWORD => $oldPassword,
            ChangePasswordInputFilter::FIELD_PASSWORD => $oldPassword,
            ChangePasswordInputFilter::FIELD_PASSWORD_CONFIRM => $oldPassword,
        ];

        $openAmIdentityService = XMock::of(OpenAmIdentityService::class);
        $openAmIdentityService
            ->expects($this->any())
            ->method('changePassword')
            ->willReturnCallback(function ($username, $password) use ($oldPassword) {
                if ($password === $oldPassword) {
                    throw new OpenAmChangePasswordException(ChangePasswordInputFilter::MSG_PASSWORD_HISTORY);
                }
            });

        $passwordService = $this->createPasswordService($personId, $openAmIdentityService);
        $passwordService->changePassword($personId, $data);
    }

    public function testChangePassword()
    {
        $personId = 1;
        $data = [
            ChangePasswordInputFilter::FIELD_OLD_PASSWORD => 'InvalidPassword',
            ChangePasswordInputFilter::FIELD_PASSWORD => 'Password1',
            ChangePasswordInputFilter::FIELD_PASSWORD_CONFIRM => 'Password1',
        ];

        $passwordService = $this->createPasswordService($personId, XMock::of(OpenAmIdentityService::class));
        $passwordService->changePassword($personId, $data);
    }

    private function createPasswordService($personId, $openAmIdentityService)
    {
        return new PasswordService(
            XMock::of(ChangePasswordValidator::class),
            $this->createIdentityProvider($personId),
            $openAmIdentityService
        );
    }

    private function createIdentityProvider($personId = 1)
    {
        $identity = XMock::of(MotIdentityInterface::class);
        $identity
            ->expects($this->any())
            ->method('getUserId')
            ->willReturn($personId);

        $identityProvider = XMock::of(MotIdentityProviderInterface::class);
        $identityProvider
            ->expects($this->any())
            ->method('getIdentity')
            ->willReturn($identity);

        return $identityProvider;
    }
}

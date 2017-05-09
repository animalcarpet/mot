<?php

namespace DashboardTest\Service;

use Dashboard\Service\PasswordService;
use Core\Service\MotFrontendIdentityProviderInterface;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use DvsaCommon\HttpRestJson\Client;
use DvsaCommon\InputFilter\Account\ChangePasswordInputFilter;
use DvsaCommon\HttpRestJson\Exception\ValidationException;
use DvsaCommonTest\TestUtils\XMock;

class PasswordServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testChangePasswordReturnTruForValidData()
    {
        $data = [
            ChangePasswordInputFilter::FIELD_OLD_PASSWORD => 'OldPassword1',
            ChangePasswordInputFilter::FIELD_PASSWORD => 'Password1',
            ChangePasswordInputFilter::FIELD_PASSWORD_CONFIRM => 'Password1',
        ];

        $passwordService = $this->createPasswordService(XMock::of(Client::class));
        $passwordService->changePassword($data);

        $this->assertTrue($passwordService->changePassword($data));
        $this->assertEquals([], $passwordService->getErrors());
    }

    public function testChangePasswordReturnsFalseForInvalidData()
    {
        $data = [
            ChangePasswordInputFilter::FIELD_OLD_PASSWORD => '',
            ChangePasswordInputFilter::FIELD_PASSWORD => 'Password1',
            ChangePasswordInputFilter::FIELD_PASSWORD_CONFIRM => 'Password1',
        ];

        $errors = [['displayMessage' => ChangePasswordInputFilter::MSG_OLD_PASSWORD_EMPTY]];

        $client = XMock::of(Client::class);
        $client
            ->expects($this->any())
            ->method('put')
            ->willReturnCallback(function () use ($data, $errors) {
                throw new ValidationException('path', 'put', $data, 400, $errors);
            });

        $passwordService = $this->createPasswordService($client);
        $this->assertFalse($passwordService->changePassword($data));
        $this->assertEquals(['' => [ChangePasswordInputFilter::MSG_OLD_PASSWORD_EMPTY]], $passwordService->getErrors());
    }

    private function createPasswordService($client)
    {
        return new PasswordService(
            $client,
            $this->createIdentityProvider()
        );
    }

    private function createIdentityProvider()
    {
        $identity = XMock::of(Identity::class);
        $identity
            ->expects($this->any())
            ->method('getUserId')
            ->willReturn(1);

        $identity
            ->expects($this->any())
            ->method('setPasswordExpired')
            ->with(false);

        $identityProvider = XMock::of(MotFrontendIdentityProviderInterface::class);
        $identityProvider
            ->expects($this->any())
            ->method('getIdentity')
            ->willReturn($identity);

        return $identityProvider;
    }
}

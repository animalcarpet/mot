<?php

namespace DvsaAuthenticationTest\Login;

use DvsaAuthentication\Identity;
use DvsaAuthentication\Login\AuthenticationResponseMapper;
use DvsaAuthentication\Login\LoginService;
use DvsaAuthentication\Login\Response\GenericAuthenticationFailure;
use DvsaAuthentication\Login\UsernamePasswordAuthenticator;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommonTest\TestUtils\MethodSpy;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Person;
use PersonApi\Service\PasswordExpiryService;

class LoginServiceTest extends \PHPUnit_Framework_TestCase
{
    private $passwordExpiryService;

    private $mapper;

    private $authenticator;

    private $identityProvider;

    public function setUp()
    {
        $this->passwordExpiryService = XMock::of(PasswordExpiryService::class);
        $this->mapper = XMock::of(AuthenticationResponseMapper::class);
        $this->authenticator = XMock::of(UsernamePasswordAuthenticator::class);
        $this->identityProvider = XMock::of(MotIdentityProviderInterface::class);
    }

    private function createService()
    {
        return new LoginService(
            $this->authenticator,
            $this->passwordExpiryService,
            $this->mapper,
            $this->identityProvider
        );
    }

    public function testLogin()
    {
        $response = new GenericAuthenticationFailure('anyMessage');
        $this->authenticator->expects($this->once())->method('authenticate')
            ->willReturn($response);

        $authenticate = new MethodSpy($this->authenticator, 'authenticate');
        $mapToDto = new MethodSpy($this->mapper, 'mapToDto');

        $this->createService()->login('customUsername', 'customPassword');

        $this->assertEquals('customUsername', $authenticate->paramsForInvocation(0)[0]);
        $this->assertEquals('customPassword', $authenticate->paramsForInvocation(0)[1]);

        $this->assertEquals($response, $mapToDto->paramsForInvocation(0)[0]);
        $this->assertEquals('customUsername', $mapToDto->paramsForInvocation(0)[1]);
    }

    public function testConfirmPasswordPassesCurrentUsernameToAuthenticator()
    {
        $expectedUsername = 'myusername';
        $expectedPassword = 'mypassword';
        $expectedResult = true;

        $this->identityProvider
            ->expects($this->once())
            ->method('getIdentity')
            ->willReturn(new Identity((new Person())->setUsername($expectedUsername)));

        $this->authenticator
            ->expects($this->once())
            ->method('validateCredentials')
            ->with($expectedUsername, $expectedPassword)
            ->willReturn($expectedResult);

        $result = $this->createService()->confirmPassword($expectedPassword);

        $this->assertEquals($expectedResult, $result);
    }
}

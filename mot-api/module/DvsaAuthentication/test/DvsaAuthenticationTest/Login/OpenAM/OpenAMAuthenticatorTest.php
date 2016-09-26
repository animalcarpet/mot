<?php

namespace DvsaAuthenticationTest\Login\OpenAM;

use Dvsa\OpenAM\Exception\InvalidPasswordException;
use Dvsa\OpenAM\Exception\OpenAMClientException;
use Dvsa\OpenAM\Exception\TooManyAuthenticationAttemptsException;
use Dvsa\OpenAM\Exception\UserInactiveException;
use Dvsa\OpenAM\Exception\UserLockedException;
use Dvsa\OpenAM\Model\OpenAMLoginDetails;
use Dvsa\OpenAM\OpenAMClient;
use Dvsa\OpenAM\Options\OpenAMClientOptions;
use DvsaAuthentication\Identity;
use DvsaAuthentication\Identity\OpenAM\OpenAMIdentityByTokenResolver;
use DvsaAuthentication\Login\OpenAM\OpenAMAuthenticator;
use DvsaAuthentication\Login\Response\AccountLockedAuthenticationFailure;
use DvsaAuthentication\Login\Response\AuthenticationSuccess;
use DvsaAuthentication\Login\Response\GenericAuthenticationFailure;
use DvsaAuthentication\Login\Response\InvalidCredentialsAuthenticationFailure;
use DvsaAuthentication\Login\Response\LockoutWarningAuthenticationFailure;
use DvsaAuthentication\Login\Response\UnresolvableIdentityAuthenticationFailure;
use DvsaCommon\Enum\PersonAuthType;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\AuthenticationMethod;
use DvsaEntities\Entity\Person;
use Zend\Log\LoggerInterface;

class OpenAMAuthenticatorTest extends \PHPUnit_Framework_TestCase
{
    private $openAMClient;
    /** @var  OpenAMClientOptions */
    private $openAMOptions;

    private $identityByTokenResolver;
    private $logger;


    public function setUp()
    {
        $this->openAMClient = XMock::of(OpenAMClient::class);
        $this->openAMOptions = new OpenAMClientOptions();
        $this->identityByTokenResolver = XMock::of(OpenAMIdentityByTokenResolver::class);
        $this->logger = XMock::of(LoggerInterface::class);
    }


    public function dataProvider_usernamePasswordEmptyCombinations()
    {

        return [[null, 'abc'], ['', 'abc'], [null, null], ['abc', '']];
    }

    /**
     * @dataProvider dataProvider_usernamePasswordEmptyCombinations
     */
    public function testAuthenticate_givenUsernameOrPasswordEmpty_shouldNotCallOpenAM($username, $password)
    {
        $this->openAMClient->expects($this->never())->method('authenticate');

        $this->create()->authenticate($username, $password);
    }

    /**
     * @dataProvider dataProvider_usernamePasswordEmptyCombinations
     */
    public function testAuthenticate_givenUsernameOrPasswordEmpty_shouldReturn($username, $password)
    {
        $result = $this->create()->authenticate($username, $password);

        $this->assertInstanceOf(InvalidCredentialsAuthenticationFailure::class, $result);
    }


    public function dataProvider_openAMExceptionsToAuthenticationFailureMapping()
    {

        return [
            [InvalidPasswordException::class, InvalidCredentialsAuthenticationFailure::class],
            [TooManyAuthenticationAttemptsException::class, LockoutWarningAuthenticationFailure::class],
            [UserLockedException::class, AccountLockedAuthenticationFailure::class],
            [UserInactiveException::class, AccountLockedAuthenticationFailure::class],
            [OpenAMClientException::class, GenericAuthenticationFailure::class]

        ];
    }

    /**
     * @dataProvider dataProvider_openAMExceptionsToAuthenticationFailureMapping
     */
    public function testAuthenticate_givenAuthenticationException_shouldTranslateToAppropriateFailure(
        $openAMExceptionClass,
        $failureClass
    ) {
        $username = 'abc';
        $password = 'def';
        $exceptionMock = XMock::of($openAMExceptionClass);
        $this->openAMClient->expects($this->once())->method('authenticate')
            ->with(new OpenAMLoginDetails($username, $password, $this->openAMOptions->getRealm()))
            ->willThrowException($exceptionMock);

        $failure = $this->create()->authenticate($username, $password);

        $this->assertInstanceOf($failureClass, $failure);
    }


    public function testAuthenticate_givenTooManyAttempts_shouldAttachAttemptsLeftInExtra() {
        $username = 'abc';
        $password = 'def';
        $this->openAMOptions->setLoginFailureLockoutCount(6);
        $this->openAMOptions->setWarnUserAfterNFailures(2);
        $this->openAMClient->expects($this->once())->method('authenticate')
            ->willThrowException(XMock::of(TooManyAuthenticationAttemptsException::class));

        $failure = $this->create()->authenticate($username, $password);


        $this->assertEquals(4, ArrayUtils::get($failure->getExtra(), 'attemptsLeft'));
    }


    public function testAuthenticate_givenIdentityResolutionFailed_shouldThrowUnresolvableIdentityException()
    {
        $username = 'abc';
        $password = 'def';
        $token = 'token';
        $this->openAMClient->expects($this->once())->method('authenticate')->willReturn($token);
        $this->identityByTokenResolver->expects($this->once())->method('resolve')->with($token)->willReturn(null);

        $result = $this->create()->authenticate($username, $password);

        $this->assertInstanceOf(UnresolvableIdentityAuthenticationFailure::class, $result);
    }

    public function testAuthenticate_successfulAuthentication()
    {
        $username = 'abc';
        $password = 'def';
        $token = 'token';
        $identity = new Identity((new Person()));
        $this->openAMClient->expects($this->once())->method('authenticate')->willReturn($token);
        $this->identityByTokenResolver->expects($this->once())->method('resolve')->with($token)->willReturn($identity);

        $result = $this->create()->authenticate($username, $password);

        $this->assertInstanceOf(AuthenticationSuccess::class, $result);
        $this->assertEquals($identity, $result->getIdentity());
    }

    /**
     * @dataProvider dataProvider_usernamePasswordEmptyCombinations
     */
    public function testValidateCredentials_givenUsernameOrPasswordEmpty_shouldBeInvalid($username, $password)
    {
        $this->assertFalse($this->create()->validateCredentials($username, $password));
    }

    public function testValidateCredentials_givenOpenAMException_shouldBeInvalid()
    {
        $this->openAMClient
            ->expects($this->once())
            ->method('validateCredentials')
            ->willThrowException(new OpenAMClientException('bang'));

        $this->assertFalse($this->create()->validateCredentials('username', 'password'));
    }

    public function testValidateCredentials_givenValidCredentials_shouldBeValid()
    {
        $this->openAMClient
            ->expects($this->once())
            ->method('validateCredentials')
            ->willReturn(true);

        $this->assertTrue($this->create()->validateCredentials('username', 'password'));
    }

    public function create()
    {
        return new OpenAMAuthenticator(
            $this->openAMClient,
            $this->openAMOptions,
            $this->identityByTokenResolver,
            $this->logger
        );
    }
}

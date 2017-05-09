<?php

namespace AccountApiTest\Factory\Service;

use AccountApi\Factory\Service\TokenServiceFactory;
use AccountApi\Service\OpenAmIdentityService;
use AccountApi\Service\TokenService;
use Doctrine\ORM\EntityManager;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommonTest\TestUtils\XMock;
use MailerApi\Service\MailerService;
use Zend\Log\LoggerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaAuthorisation\Service\AuthorisationService;
use Zend\Authentication\AuthenticationService;

/**
 * Class TokenServiceFactoryTest.
 */
class TokenServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /* @var TokenServiceFactory $serviceFactory */
    protected $serviceFactory;

    protected $serviceLocatorMock;
    protected $entityManager;
    protected $logger;
    protected $config;
    protected $mailerService;
    protected $openAmIdentityService;
    protected $obfuscator;
    protected $authorisationService;
    protected $authenticationService;

    public function setUp()
    {
        $this->serviceFactory = new TokenServiceFactory();
        $this->serviceLocatorMock = XMock::of(ServiceLocatorInterface::class, ['get']);
        $this->entityManager = XMock::of(EntityManager::class);
        $this->logger = XMock::of(LoggerInterface::class);
        $this->mailerService = XMock::of(MailerService::class);
        $this->openAmIdentityService = XMock::of(OpenAmIdentityService::class);
        $this->obfuscator = XMock::of(ParamObfuscator::class);
        $this->authenticationService = XMock::of(AuthenticationService::class);
        $this->authorisationService = XMock::of(AuthorisationService::class);

        $this->config = [
            'password_reset' => [
                'secret' => '',
                'hash_method' => '',
                'expireTime' => '',
            ],
            'mailer' => [],
            'helpdesk' => [],
        ];
    }

    public function testEventServiceGetList()
    {
        $this->serviceLocatorMock->expects($this->at(0))
            ->method('get')
            ->willReturn($this->config);
        $this->serviceLocatorMock->expects($this->at(1))
            ->method('get')
            ->willReturn($this->entityManager);
        $this->serviceLocatorMock->expects($this->at(2))
            ->method('get')
            ->willReturn($this->logger);
        $this->serviceLocatorMock->expects($this->at(3))
            ->method('get')
            ->willReturn($this->mailerService);
        $this->serviceLocatorMock->expects($this->at(4))
            ->method('get')
            ->willReturn($this->openAmIdentityService);
        $this->serviceLocatorMock->expects($this->at(5))
            ->method('get')
            ->willReturn($this->obfuscator);
        $this->serviceLocatorMock->expects($this->at(6))
            ->method('get')
            ->willReturn($this->authenticationService);
        $this->serviceLocatorMock->expects($this->at(7))
            ->method('get')
            ->willReturn($this->authorisationService);

        $this->assertInstanceOf(
            TokenService::class,
            $this->serviceFactory->createService($this->serviceLocatorMock)
        );
    }
}

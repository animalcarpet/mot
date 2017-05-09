<?php

namespace DvsaAuthorisationTest\Factory;

use DvsaAuthorisation\Factory\AuthorisationServiceFactory;
use DvsaAuthorisation\Service\AuthorisationService;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Repository\RbacRepository;
use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\ServiceManager;

/**
 * Class AuthorisationServiceFactoryTest.
 */
class AuthorisationServiceFactoryTest extends AbstractServiceTestCase
{
    /* @var AuthorisationServiceFactory $authorisationServiceFactory */
    private $authorisationServiceFactory;

    private $serviceLocator;
    private $rbacRepositoryMock;
    private $authenticationServiceMock;

    public function setUp()
    {
        $this->authorisationServiceFactory = new AuthorisationServiceFactory();
        $this->rbacRepositoryMock = XMock::of(RbacRepository::class);
        $this->authenticationServiceMock = XMock::of(AuthenticationService::class);

        $this->serviceLocator = new ServiceManager();
        $this->serviceLocator->setService('DvsaAuthenticationService', $this->authenticationServiceMock);
        $this->serviceLocator->setService(RbacRepository::class, $this->rbacRepositoryMock);
    }

    public function testAuthorisationServiceFactoryReturnsInstance()
    {
        $this->assertInstanceOf(
            AuthorisationService::class,
            $this->authorisationServiceFactory->createService($this->serviceLocator)
        );
    }
}

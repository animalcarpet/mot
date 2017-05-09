<?php

namespace DvsaAuthorisationTest\Factory;

use DvsaAuthorisation\Factory\RoleProviderServiceFactory;
use DvsaAuthorisation\Service\RoleProviderService;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Repository\RbacRepository;
use Zend\ServiceManager\ServiceManager;

/**
 * Class RoleProviderServiceFactoryTest.
 */
class RoleProviderServiceFactoryTest extends AbstractServiceTestCase
{
    /* @var RoleProviderServiceFactory $roleProviderServiceFactory */
    private $roleProviderServiceFactory;

    private $serviceLocator;
    private $rbacRepositoryMock;

    public function setUp()
    {
        $this->roleProviderServiceFactory = new RoleProviderServiceFactory();
        $this->rbacRepositoryMock = XMock::of(RbacRepository::class);
        $this->serviceLocator = new ServiceManager();
        $this->serviceLocator->setService(RbacRepository::class, $this->rbacRepositoryMock);
    }

    public function testRoleProviderServiceFactoryReturnsInstance()
    {
        $this->assertInstanceOf(
            RoleProviderService::class,
            $this->roleProviderServiceFactory->createService($this->serviceLocator)
        );
    }
}

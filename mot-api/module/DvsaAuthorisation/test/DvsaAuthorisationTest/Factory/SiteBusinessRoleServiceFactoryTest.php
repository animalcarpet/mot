<?php

namespace DvsaAuthorisationTest\Factory;

use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Factory\SiteBusinessRoleServiceFactory;
use DvsaAuthorisation\Service\SiteBusinessRoleService;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use Zend\ServiceManager\ServiceManager;

/**
 * Class SiteBusinessRoleServiceFactoryTest.
 */
class SiteBusinessRoleServiceFactoryTest extends AbstractServiceTestCase
{
    /* @var SiteBusinessRoleServiceFactory $siteBusinessRoleServiceFactory */
    private $siteBusinessRoleServiceFactory;

    private $serviceLocator;
    private $entityManagerMock;

    public function setUp()
    {
        $this->siteBusinessRoleServiceFactory = new SiteBusinessRoleServiceFactory();
        $this->entityManagerMock = XMock::of(EntityManager::class);
        $this->serviceLocator = new ServiceManager();
        $this->serviceLocator->setService(EntityManager::class, $this->entityManagerMock);
    }

    public function testRoleProviderServiceFactoryReturnsInstance()
    {
        $this->assertInstanceOf(
            SiteBusinessRoleService::class,
            $this->siteBusinessRoleServiceFactory->createService($this->serviceLocator)
        );
    }
}

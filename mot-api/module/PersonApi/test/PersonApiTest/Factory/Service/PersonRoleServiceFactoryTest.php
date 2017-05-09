<?php

namespace PersonApiTest\Factory\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Repository\RbacRepository;
use DvsaMotApi\Helper\RoleEventHelper;
use DvsaMotApi\Helper\RoleNotificationHelper;
use PersonApi\Factory\Service\PersonRoleServiceFactory;
//use PersonApi\Factory\PersonRoleServiceFactory;
use PersonApi\Service\PersonRoleService;
use Zend\ServiceManager\ServiceManager;

class PersonRoleServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateService()
    {
        $factory = new PersonRoleServiceFactory();

        $sm = new ServiceManager();

        $sm->setService(EntityManager::class, $this->stubEntityManager());
        $sm->setService(RbacRepository::class,  XMock::of(RbacRepository::class));
        $sm->setService('DvsaAuthorisationService', XMock::of(AuthorisationServiceInterface::class));
        $sm->setService(RoleEventHelper::class, XMock::of(RoleEventHelper::class));
        $sm->setService(RoleNotificationHelper::class, XMock::of(RoleNotificationHelper::class));

        $this->assertInstanceOf(
            PersonRoleService::class,
            $factory->createService($sm)
        );
    }

    private function stubEntityManager()
    {
        $mockEntityManager = XMock::of(EntityManager::class);

        $mockEntityManager->expects($this->any())
            ->method('getRepository')
            ->willReturnCallback(
                function ($entityName) {
                    $repositoryName = str_replace('Entity', 'Repository', $entityName).'Repository';

                    if (class_exists($repositoryName)) {
                        $mokRepo = XMock::of($repositoryName);
                    } else {
                        $mokRepo = XMock::of(EntityRepository::class);
                    }

                    return $mokRepo;
                }
            );

        return $mockEntityManager;
    }
}

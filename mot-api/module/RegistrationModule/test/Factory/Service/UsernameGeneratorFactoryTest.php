<?php

use Doctrine\ORM\EntityManager;
use Dvsa\Mot\Api\RegistrationModule\Factory\Service\UsernameGeneratorFactory;
use Dvsa\Mot\Api\RegistrationModule\Service\UsernameGenerator;
use DvsaCommonTest\TestUtils\XMock;
use Zend\ServiceManager\ServiceManager;

/**
 * Class UsernameGeneratorFactoryTest.
 */
class UsernameGeneratorFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateService()
    {
        $factory = new UsernameGeneratorFactory();

        $sm = new ServiceManager();

        $sm->setService(EntityManager::class, $this->stubEntityManager());

        $this->assertInstanceOf(
            UsernameGenerator::class,
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

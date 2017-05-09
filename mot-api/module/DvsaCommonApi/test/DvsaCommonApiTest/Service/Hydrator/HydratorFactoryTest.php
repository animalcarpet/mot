<?php

namespace DvsaCommonApiTest\Service\Hydrator;

use Doctrine\ORM\EntityManager;
use DvsaCommonApi\Service\Hydrator\HydratorFactory;
use PHPUnit_Framework_TestCase;

/**
 * Class HydratorFactoryTest.
 */
class HydratorFactoryTest extends PHPUnit_Framework_TestCase
{
    const TEST_HYDRATOR = "Sample\SampleHydrator";

    public function testCanCreateObject()
    {
        $hydratorFactory = new HydratorFactory();
        $mockServiceLocator = $this->getServiceLocatorMock();
        $this->assertTrue(
            $hydratorFactory->canCreateServiceWithName($mockServiceLocator, self::TEST_HYDRATOR, self::TEST_HYDRATOR)
        );
        $this->assertFalse(
            $hydratorFactory->canCreateServiceWithName($mockServiceLocator, 'notImportant', 'completeFake')
        );
        $this->assertFalse(
            $hydratorFactory->canCreateServiceWithName($mockServiceLocator, 'nothing', 'nonNamespaceHydrator')
        );
    }

    public function testCreateObject()
    {
        $hydratorFactory = new HydratorFactory();
        $mockServiceLocator = $this->getServiceLocatorMock();
        $this->assertInstanceOf(
            \DoctrineModule\Stdlib\Hydrator\DoctrineObject::class,
            $hydratorFactory->createServiceWithName($mockServiceLocator, self::TEST_HYDRATOR, self::TEST_HYDRATOR)
        );
    }

    private function getServiceLocatorMock()
    {
        $metaDataMock = $this->getMock(\Doctrine\Common\Persistence\Mapping\ClassMetadata::class);
        $metaDataMock->expects($this->any())
            ->method('getAssociationNames')
            ->will($this->returnValue([]));
        $entityManagerMock = \DvsaCommonTest\TestUtils\XMock::of(EntityManager::class);
        $entityManagerMock->expects($this->any())
            ->method('getClassMetadata')
            ->will($this->returnValue($metaDataMock));
        $serviceManagerMock = $this->getMock(\Zend\ServiceManager\ServiceManager::class);
        $serviceManagerMock->expects($this->any())
            ->method('get')
            ->with(EntityManager::class)
            ->will($this->returnValue($entityManagerMock));

        return $serviceManagerMock;
    }
}

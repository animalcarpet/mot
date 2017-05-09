<?php

namespace DvsaCommonApi\Service\Hydrator;

use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class HydratorFactory.
 */
class HydratorFactory implements AbstractFactoryInterface
{
    const HYDRATOR_SUFFIX = 'Hydrator';

    public function canCreateServiceWithName(ServiceLocatorInterface $locator, $name, $requestedName)
    {
        return $this->endsWith($requestedName, self::HYDRATOR_SUFFIX) && strpos($requestedName, '\\');
    }

    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return new DoctrineHydrator(
            $serviceLocator->get(EntityManager::class),
            substr($requestedName, 0, -8)
        );
    }

    private function endsWith($haystack, $needle)
    {
        return $needle === '' || substr($haystack, -strlen($needle)) === $needle;
    }
}

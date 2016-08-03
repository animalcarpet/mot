<?php

namespace DvsaCommon\Factory\AutoWire;

use DvsaCommon\Utility\ArrayUtils;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AutoWireFactory implements AbstractFactoryInterface
{
    /**
     * Determine if we can create a service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return is_subclass_of($requestedName, AutoWireableInterface::class);
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return mixed
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        if ($serviceLocator instanceof ControllerManager) {
            $serviceLocator = $serviceLocator->getServiceLocator();
        }

        $reflection = new \ReflectionClass($requestedName);

        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return $reflection->newInstance();
        } else {
            $constructorParameters = ArrayUtils::map($constructor->getParameters(),
                function (\ReflectionParameter $parameter) use ($serviceLocator, $requestedName) {
                    return $serviceLocator->get($parameter->getClass()->getName());
                });

            return $reflection->newInstanceArgs($constructorParameters);
        }
    }
}

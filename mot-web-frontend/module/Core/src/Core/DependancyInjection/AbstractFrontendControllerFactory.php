<?php

namespace Core\DependancyInjection;

use DvsaCommon\DtoSerialization\DtoReflectiveDeserializer;
use DvsaCommon\DtoSerialization\DtoReflectiveSerializer;
use DvsaCommon\HttpRestJson\AbstractApiResource;
use DvsaCommon\HttpRestJson\Client;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

abstract class AbstractFrontendControllerFactory implements FactoryInterface
{
    /** @var ServiceLocatorInterface */
    private $serviceLocator;

    /** @var ControllerManager */
    private $controllerManager;

    public function createService(ServiceLocatorInterface $controllerManager)
    {
        $this->controllerManager = $controllerManager;

        $this->serviceLocator = $this->controllerManager->getServiceLocator();

        return $this->createController($this->controllerManager);
    }

    abstract public function createController(ServiceLocatorInterface $serviceLocator);

    /**
     * Helps creating api resource objects.
     *
     * @param $class
     *
     * @return AbstractApiResource
     */
    protected function getApiResource($class)
    {
        if (is_string($class) && is_subclass_of($class, AbstractApiResource::class)) {
            $httpClient = $this->serviceLocator->get(Client::class);
            $deserializer = $this->serviceLocator->get(DtoReflectiveDeserializer::class);
            $serializer = $this->serviceLocator->get(DtoReflectiveSerializer::class);

            return new $class($httpClient, $deserializer, $serializer);
        } else {
            throw new \InvalidArgumentException('First argument of method '.self::class.'::'.'getApiResource is expected to be a name of a class extending '.AbstractApiResource::class);
        }
    }
}

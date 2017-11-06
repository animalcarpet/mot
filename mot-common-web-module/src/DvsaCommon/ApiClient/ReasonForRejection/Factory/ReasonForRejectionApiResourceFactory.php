<?php
namespace DvsaCommon\ApiClient\ReasonForRejection\Factory;

use DvsaCommon\ApiClient\ReasonForRejection\ReasonForRejectionApiResource;
use DvsaCommon\HttpRestJson\Client;
use DvsaCommon\DtoSerialization\DtoReflectiveDeserializer;
use DvsaCommon\DtoSerialization\DtoReflectiveSerializer;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ReasonForRejectionApiResourceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return ReasonForRejectionApiResource
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var Client $client */
        $client = $serviceLocator->get(Client::class);

        return new ReasonForRejectionApiResource($client, new DtoReflectiveDeserializer(), new DtoReflectiveSerializer());
    }
}

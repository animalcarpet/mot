<?php

namespace TestSupport\Factory;

use Doctrine\ORM\EntityManager;
use DvsaCommon\HttpRestJson\Client;
use TestSupport\Helper\TestSupportAccessTokenManager;
use TestSupport\Service\CronUserService;
use TestSupport\Service\GdsSurveyService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class GdsSurveyServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $serviceLocator->get(EntityManager::class);
        $service = new GdsSurveyService(
            $entityManager,
            $serviceLocator->get(TestSupportAccessTokenManager::class),
            $serviceLocator->get(CronUserService::class),
            $serviceLocator->get(Client::class)
        );

        return $service;
    }
}

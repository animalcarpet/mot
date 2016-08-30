<?php

namespace DvsaMotApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommonApi\Authorisation\Assertion\ApiPerformMotTestAssertion;
use DvsaMotApi\Service\MotTestReasonForRejectionService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class MotTestReasonForRejectionServiceFactory.
 */
class MotTestReasonForRejectionServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return MotTestReasonForRejectionService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new MotTestReasonForRejectionService(
            $serviceLocator->get(EntityManager::class),
            $serviceLocator->get('DvsaAuthorisationService'),
            $serviceLocator->get('MotTestValidator'),
            $serviceLocator->get('TestItemSelectorService'),
            $serviceLocator->get(ApiPerformMotTestAssertion::class),
            $serviceLocator->get('Feature\FeatureToggles')
        );
    }
}

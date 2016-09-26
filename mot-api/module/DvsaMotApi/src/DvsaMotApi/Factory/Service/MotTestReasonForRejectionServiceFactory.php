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
        $entityManager = $serviceLocator->get(EntityManager::class);
        $authService = $serviceLocator->get('DvsaAuthorisationService');
        $motTestValidator = $serviceLocator->get('MotTestValidator');
        $motTestItemSelectorService = $serviceLocator->get('TestItemSelectorService');
        $performMotTestAssertion = $serviceLocator->get(ApiPerformMotTestAssertion::class);
        $featureToggles = $serviceLocator->get('Feature\FeatureToggles');

        return new MotTestReasonForRejectionService($entityManager, $authService, $motTestValidator,
            $motTestItemSelectorService, $performMotTestAssertion, $featureToggles);
    }
}
